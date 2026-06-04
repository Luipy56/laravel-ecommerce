<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class AdminHelpIssueRequestService
{
    public function storageRoot(): string
    {
        return rtrim((string) config('admin_help.storage_path'), DIRECTORY_SEPARATOR);
    }

    public function ensureDirectories(): void
    {
        foreach (['pending', 'processing', 'processed', 'failed', 'drafts'] as $dir) {
            $path = $this->dirPath($dir);
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0755, true);
            }
        }
    }

    public function processingJsonPath(string $id): string
    {
        return $this->filePath('processing', $id);
    }

    public function draftPath(string $id): string
    {
        $safeId = preg_replace('/[^a-zA-Z0-9\-]/', '', $id) ?? $id;

        return $this->dirPath('drafts').DIRECTORY_SEPARATOR.$safeId.'.md';
    }

    public function processorLockPath(): string
    {
        return $this->storageRoot().DIRECTORY_SEPARATOR.'.processor.lock';
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    public function writeProcessedMeta(string $id, array $meta): void
    {
        $this->ensureDirectories();
        $safeId = preg_replace('/[^a-zA-Z0-9\-]/', '', $id) ?? $id;
        $path = $this->dirPath('processed').DIRECTORY_SEPARATOR.$safeId.'.meta.json';
        File::put($path, json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }

    public function archiveDraft(string $id): void
    {
        $draft = $this->draftPath($id);
        if (! File::exists($draft)) {
            return;
        }

        $safeId = preg_replace('/[^a-zA-Z0-9\-]/', '', $id) ?? $id;
        $dest = $this->dirPath('processed').DIRECTORY_SEPARATOR.$safeId.'.draft.md';
        File::move($draft, $dest);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function storePending(array $payload): string
    {
        $this->ensureDirectories();

        $id = (string) ($payload['id'] ?? '');
        if ($id === '') {
            throw new InvalidArgumentException('Payload id is required.');
        }

        $path = $this->filePath('pending', $id);
        $written = File::put($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        if ($written === false) {
            throw new RuntimeException('Failed to store admin help request.');
        }

        return $id;
    }

    /**
     * @return array<int, string> Request ids in pending/
     */
    public function listPendingIds(): array
    {
        $this->ensureDirectories();

        return $this->listIdsInDir('pending');
    }

    /**
     * @return array<int, string> Request ids in processing/
     */
    public function listProcessingIds(): array
    {
        $this->ensureDirectories();

        return $this->listIdsInDir('processing');
    }

    public function claim(string $id): bool
    {
        return $this->moveBetween('pending', 'processing', $id);
    }

    public function releaseToPending(string $id): bool
    {
        return $this->moveBetween('processing', 'pending', $id);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    public function markProcessed(string $id, array $extra = []): bool
    {
        $payload = $this->readPayload('processing', $id);
        if ($payload === null) {
            return false;
        }

        $payload['status'] = 'processed';
        $payload['processedAt'] = now()->utc()->toIso8601String();
        foreach ($extra as $key => $value) {
            $payload[$key] = $value;
        }

        $dest = $this->filePath('processed', $id);
        File::put($dest, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        File::delete($this->filePath('processing', $id));

        return true;
    }

    public function moveToFailed(string $id, string $reason, string $fromDir = 'processing'): bool
    {
        $payload = $this->readPayload($fromDir, $id);
        if ($payload === null) {
            return false;
        }

        $payload['status'] = 'failed';
        $payload['failedAt'] = now()->utc()->toIso8601String();
        $payload['failureReason'] = $reason;

        $dest = $this->filePath('failed', $id);
        File::put($dest, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        File::delete($this->filePath($fromDir, $id));

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function readPayload(string $dir, string $id): ?array
    {
        $path = $this->filePath($dir, $id);
        if (! File::exists($path)) {
            return null;
        }

        $decoded = json_decode(File::get($path), true);
        if (! is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    /**
     * Recover processing files older than the configured threshold back to pending.
     */
    public function recoverStaleProcessing(): int
    {
        $this->ensureDirectories();
        $maxAgeMinutes = (int) config('admin_help.processing_stale_minutes', 30);
        $cutoff = now()->subMinutes($maxAgeMinutes)->getTimestamp();
        $recovered = 0;

        foreach ($this->listProcessingIds() as $id) {
            $path = $this->filePath('processing', $id);
            if (File::lastModified($path) < $cutoff && $this->releaseToPending($id)) {
                $recovered++;
            }
        }

        return $recovered;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function validatePayload(array $payload): ?array
    {
        $comment = $payload['comment'] ?? null;
        if (! is_string($comment) || trim($comment) === '') {
            return null;
        }

        $maxComment = (int) config('admin_help.comment_max_length', 4000);
        if (mb_strlen($comment) > $maxComment) {
            return null;
        }

        $title = $payload['title'] ?? null;
        if ($title !== null && ! is_string($title)) {
            return null;
        }
        if (is_string($title) && mb_strlen($title) > (int) config('admin_help.title_max_length', 200)) {
            return null;
        }

        return $payload;
    }

    private function moveBetween(string $fromDir, string $toDir, string $id): bool
    {
        $from = $this->filePath($fromDir, $id);
        $to = $this->filePath($toDir, $id);

        if (! File::exists($from)) {
            return false;
        }

        if (File::exists($to)) {
            return false;
        }

        return File::move($from, $to);
    }

    /**
     * @return array<int, string>
     */
    private function listIdsInDir(string $dir): array
    {
        $path = $this->dirPath($dir);
        if (! File::isDirectory($path)) {
            return [];
        }

        $ids = [];
        foreach (File::files($path) as $file) {
            if ($file->getExtension() === 'json') {
                $ids[] = $file->getFilenameWithoutExtension();
            }
        }

        sort($ids);

        return $ids;
    }

    private function dirPath(string $dir): string
    {
        return $this->storageRoot().DIRECTORY_SEPARATOR.$dir;
    }

    private function filePath(string $dir, string $id): string
    {
        $safeId = preg_replace('/[^a-zA-Z0-9\-]/', '', $id) ?? $id;

        return $this->dirPath($dir).DIRECTORY_SEPARATOR.$safeId.'.json';
    }
}
