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
            $issue = $this->generateIssueContent($validated);
            if ($issue === null) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: cursor-agent did not return valid issue content', ['id' => $id]);

                return false;
            }

            if (! $this->ensureGitHubLabel()) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: failed to ensure GitHub label', ['id' => $id]);

                return false;
            }

            $issueNumber = $this->createGitHubIssue($issue['title'], $issue['body']);
            if ($issueNumber === null) {
                $this->requests->releaseToPending($id);
                Log::warning('admin_help: GitHub issue creation failed', ['id' => $id]);

                return false;
            }

            $this->requests->markProcessed($id, [
                'githubIssueNumber' => $issueNumber,
                'githubRepo' => config('admin_help.github_repo'),
            ]);

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
    private function generateIssueContent(array $payload): ?array
    {
        if (! $this->commandExists('cursor-agent')) {
            Log::warning('admin_help: cursor-agent not found on PATH');

            return null;
        }

        $promptPath = (string) config('admin_help.prompt_path');
        if (! File::exists($promptPath)) {
            Log::error('admin_help: prompt file missing');

            return null;
        }

        $promptTemplate = File::get($promptPath);
        $payloadJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        $fullPrompt = $promptTemplate."\n\n---\n\nAdmin help request payload (JSON):\n".$payloadJson;

        $timeout = (int) config('admin_help.cursor_agent_timeout', 300);
        $process = new Process(
            ['cursor-agent', '--print', '--trust', '--workspace', base_path(), $fullPrompt],
            base_path(),
            null,
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

        return $this->parseAgentOutput(trim($process->getOutput()));
    }

    /**
     * @return array{title: string, body: string}|null
     */
    private function parseAgentOutput(string $output): ?array
    {
        if ($output === '') {
            return null;
        }

        $json = $output;
        if (preg_match('/\{[\s\S]*\}/', $output, $matches)) {
            $json = $matches[0];
        }

        $decoded = json_decode($json, true);
        if (! is_array($decoded)) {
            return null;
        }

        $title = isset($decoded['title']) && is_string($decoded['title']) ? trim($decoded['title']) : '';
        $body = isset($decoded['body']) && is_string($decoded['body']) ? trim($decoded['body']) : '';

        if ($title === '' || $body === '') {
            return null;
        }

        if (mb_strlen($title) > 256) {
            $title = mb_substr($title, 0, 256);
        }

        return ['title' => $title, 'body' => $body];
    }

    private function ensureGitHubLabel(): bool
    {
        if (! $this->commandExists('gh')) {
            Log::warning('admin_help: gh not found on PATH');

            return false;
        }

        $repo = (string) config('admin_help.github_repo');
        $label = (string) config('admin_help.validation_label');
        $color = (string) config('admin_help.validation_label_color');
        $description = (string) config('admin_help.validation_label_description');

        if ($this->gitHubLabelExists($repo, $label)) {
            return true;
        }

        $create = new Process([
            'gh', 'label', 'create', $label,
            '--repo', $repo,
            '--color', $color,
            '--description', $description,
        ], base_path(), null, null, 60);

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
            null,
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

    private function createGitHubIssue(string $title, string $body): ?int
    {
        $repo = (string) config('admin_help.github_repo');
        $label = (string) config('admin_help.validation_label');

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
                null,
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
        $process = new Process(['which', $command]);
        $process->run();

        return $process->isSuccessful();
    }
}
