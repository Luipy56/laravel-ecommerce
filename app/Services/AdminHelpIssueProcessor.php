<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminHelpIssueProcessor
{
    public function __construct(
        private readonly AdminHelpIssueRequestService $requests,
    ) {}

    public function processPending(int $limit = 10, bool $dryRun = false): int
    {
        $this->requests->ensureDirectories();
        $this->requests->recoverStaleProcessing();

        $lockHandle = fopen($this->requests->processorLockPath(), 'c+');
        if ($lockHandle === false) {
            Log::warning('admin_help: could not open processor lock');

            return 0;
        }

        if (! flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);

            return 0;
        }

        try {
            $processed = 0;
            foreach ($this->requests->listPendingIds() as $id) {
                if ($processed >= $limit) {
                    break;
                }
                if ($this->processOne($id, $dryRun)) {
                    $processed++;
                }
            }

            return $processed;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }

    public function processOne(string $id, bool $dryRun = false): bool
    {
        if (! $this->requests->claim($id)) {
            return false;
        }

        $payload = $this->requests->readPayload('processing', $id);
        if ($payload === null) {
            Log::warning('admin_help: missing payload after claim', ['id' => $id]);

            return false;
        }

        $validated = $this->requests->validatePayload($payload);
        if ($validated === null) {
            $this->requests->moveToFailed($id, 'invalid_payload');

            return false;
        }

        if ($dryRun) {
            $this->requests->releaseToPending($id);

            return true;
        }

        try {
            $issue = $this->generateIssueContent($id, $validated);
            if ($issue === null) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: cursor-agent did not produce a valid draft', ['id' => $id]);

                return false;
            }

            $issueLabel = (string) ($validated['label'] ?? config('admin_help.fallback_label'));

            if (! $this->ensureGitHubLabel($issueLabel)) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: failed to ensure GitHub label', ['id' => $id, 'label' => $issueLabel]);

                return false;
            }

            $issueNumber = $this->createGitHubIssue($issue['title'], $issue['body'], $issueLabel);
            if ($issueNumber === null) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: GitHub issue creation failed', ['id' => $id]);

                return false;
            }

            $repo = (string) config('admin_help.github_repo');
            $draftPath = $this->requests->draftPath($id);

            $this->requests->markProcessed($id, [
                'githubIssueNumber' => $issueNumber,
                'githubRepo' => $repo,
            ]);

            $this->requests->writeProcessedMeta($id, [
                'issue' => $issueNumber,
                'issueUrl' => "https://github.com/{$repo}/issues/{$issueNumber}",
                'queueId' => $id,
                'draftPath' => $draftPath,
                'processedAt' => now()->utc()->toIso8601String(),
            ]);

            $this->requests->archiveDraft($id);

            return true;
        } catch (\Throwable $e) {
            $this->requests->releaseToPending($id);
            Log::error('admin_help: processing failed', [
                'id' => $id,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{title: string, body: string}|null
     */
    private function generateIssueContent(string $id, array $payload): ?array
    {
        $cursorAgent = (string) config('admin_help.cursor_agent_path', 'cursor-agent');

        if (! $this->commandExists($cursorAgent)) {
            Log::warning('admin_help: cursor-agent not found on PATH', ['path' => $cursorAgent]);

            return null;
        }

        $promptPath = (string) config('admin_help.prompt_path');
        if (! File::exists($promptPath)) {
            Log::error('admin_help: prompt file missing', ['path' => $promptPath]);

            return null;
        }

        $jsonPath = $this->requests->processingJsonPath($id);
        $draftPath = $this->requests->draftPath($id);

        if (File::exists($draftPath)) {
            File::delete($draftPath);
        }

        $promptTemplate = File::get($promptPath);
        $fullPrompt = $promptTemplate."\n\n---\n\nLoop message:\n"
            ."Queue JSON (read this file): {$jsonPath}\n"
            ."Output draft (write ONLY this file): {$draftPath}\n"
            ."Queue ID: {$id}\n"
            ."Do not create GitHub issues. Do not edit application source. Do your job.";

        $timeout = (int) config('admin_help.cursor_agent_timeout', 900);
        $process = new Process(
            [$cursorAgent, '--yolo', '--print', '--trust', '--workspace', base_path(), $fullPrompt],
            base_path(),
            $this->processEnvironment(),
            null,
            $timeout
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException $e) {
            Log::warning('admin_help: cursor-agent failed', [
                'exit_code' => $process->getExitCode(),
            ]);

            return null;
        }

        try {
            return AdminHelpDraftParser::parseFile($draftPath);
        } catch (\Throwable $e) {
            Log::warning('admin_help: draft parse failed', [
                'id' => $id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array<string, string>|null
     */
    private function processEnvironment(): ?array
    {
        $env = getenv();
        if (! is_array($env)) {
            return null;
        }

        $ghToken = env('GH_TOKEN');
        if (is_string($ghToken) && $ghToken !== '') {
            $env['GH_TOKEN'] = $ghToken;
        }

        return $env;
    }

    private function ensureGitHubLabel(string $label): bool
    {
        if (! $this->commandExists('gh')) {
            Log::warning('admin_help: gh not found on PATH');

            return false;
        }

        $repo = (string) config('admin_help.github_repo');
        $allowed = (array) config('admin_help.allowed_labels', []);
        $meta = $allowed[$label] ?? null;
        if (! is_array($meta)) {
            Log::warning('admin_help: unknown issue label', ['label' => $label]);

            return false;
        }

        $color = (string) ($meta['color'] ?? '5319E7');
        $description = (string) ($meta['description'] ?? 'Admin Help intake');

        if ($this->gitHubLabelExists($repo, $label)) {
            return true;
        }

        $create = new Process([
            'gh', 'label', 'create', $label,
            '--repo', $repo,
            '--color', $color,
            '--description', $description,
        ], base_path(), $this->processEnvironment(), null, 60);

        try {
            $create->mustRun();
        } catch (ProcessFailedException) {
            return $this->gitHubLabelExists($repo, $label);
        }

        return $this->gitHubLabelExists($repo, $label);
    }

    private function gitHubLabelExists(string $repo, string $label): bool
    {
        $list = new Process(
            ['gh', 'label', 'list', '--repo', $repo, '--limit', '500', '--json', 'name'],
            base_path(),
            $this->processEnvironment(),
            null,
            60
        );

        try {
            $list->mustRun();
        } catch (ProcessFailedException) {
            return false;
        }

        $names = json_decode($list->getOutput(), true);
        if (! is_array($names)) {
            return false;
        }

        foreach ($names as $row) {
            if (is_array($row) && ($row['name'] ?? null) === $label) {
                return true;
            }
        }

        return false;
    }

    private function createGitHubIssue(string $title, string $body, string $label): ?int
    {
        $repo = (string) config('admin_help.github_repo');

        $bodyFile = storage_path('app/admin-help/tmp-'.bin2hex(random_bytes(8)).'.md');
        File::ensureDirectoryExists(dirname($bodyFile));
        File::put($bodyFile, $body);

        try {
            $process = new Process(
                [
                    'gh', 'issue', 'create',
                    '--repo', $repo,
                    '--title', $title,
                    '--body-file', $bodyFile,
                    '--label', $label,
                ],
                base_path(),
                $this->processEnvironment(),
                null,
                120
            );
            $process->mustRun();

            $output = trim($process->getOutput());
            if (preg_match('#/issues/(\d+)\s*$#', $output, $matches)) {
                return (int) $matches[1];
            }

            return null;
        } catch (ProcessFailedException $e) {
            Log::warning('admin_help: gh issue create failed', [
                'stderr' => $e->getProcess()->getErrorOutput(),
            ]);

            return null;
        } finally {
            if (File::exists($bodyFile)) {
                File::delete($bodyFile);
            }
        }
    }

    private function commandExists(string $command): bool
    {
        if (str_contains($command, '/')) {
            return is_file($command) && is_executable($command);
        }

        $process = new Process(['which', $command]);
        $process->run();

        return $process->isSuccessful();
    }
}
