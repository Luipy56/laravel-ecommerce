<?php

namespace App\Services\AdminChat;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminChatLlmClient
{
    private ?string $lastFailure = null;

    public function consumeLastFailure(): ?string
    {
        $reason = $this->lastFailure;
        $this->lastFailure = null;

        return $reason;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    public function complete(string $provider, array $messages): ?array
    {
        $this->lastFailure = null;
        if ($provider === 'ollama') {
            return $this->completeOllama($messages);
        }
        if ($provider === 'cursor') {
            return $this->completeCursor($messages);
        }
        $this->lastFailure = 'provider_no_soportado';

        return null;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    private function completeOllama(array $messages): ?array
    {
        $url = rtrim((string) config('admin_chat.ollama.base_url'), '/').'/api/chat';
        $model = (string) config('admin_chat.ollama.model');
        $timeout = (int) config('admin_chat.ollama.timeout', 45);

        try {
            $response = Http::timeout($timeout)->acceptJson()->post($url, [
                'model' => $model,
                'messages' => $messages,
                'stream' => false,
                'format' => 'json',
            ]);
        } catch (\Throwable $e) {
            Log::warning('admin_chat: ollama http failed', ['message' => $e->getMessage()]);
            $this->lastFailure = 'ollama_http: '.$this->safeSnippet($e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            Log::warning('admin_chat: ollama status', ['status' => $response->status()]);
            $this->lastFailure = 'ollama_http_status_'.$response->status();

            return null;
        }

        $content = data_get($response->json(), 'message.content');
        if (! is_string($content) || trim($content) === '') {
            $this->lastFailure = 'ollama_respuesta_vacia';

            return null;
        }

        $parsed = $this->parseModelOutput($content);
        if ($parsed === null) {
            $this->lastFailure = 'ollama_sin_json_utilizable';
        }

        return $parsed;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    private function completeCursor(array $messages): ?array
    {
        $binary = (string) config('admin_chat.cursor.binary');
        $timeout = (int) config('admin_chat.cursor.timeout', 90);
        $dir = storage_path('app/admin-chat');
        File::ensureDirectoryExists($dir);
        $workspace = $dir.'/workspace';
        File::ensureDirectoryExists($workspace);
        $this->syncPackIntoWorkspace($workspace);

        $out = $dir.'/reply-'.uniqid('', true).'.json';
        $userTurns = array_values(array_filter($messages, fn ($m) => ($m['role'] ?? '') !== 'system'));
        $system = '';
        foreach ($messages as $m) {
            if (($m['role'] ?? '') === 'system') {
                $system = (string) ($m['content'] ?? '');
                break;
            }
        }
        File::put($workspace.'/SIERRA_SYSTEM.md', $system);

        $conversation = '';
        foreach ($userTurns as $m) {
            $conversation .= strtoupper((string) $m['role']).":\n".($m['content'] ?? '')."\n\n";
        }

        $prompt = <<<PROMPT
You are Sierra — Maestro's daughter for Serralleria Solidària admin.

Your catalog pack is on disk under ./pack/ (CONTEXT.md, TOOLS.md, ALLOW.md, DENY.md, EXAMPLES.md, SCHEMA-BRIEF.md).
System instructions are also in ./SIERRA_SYSTEM.md.

Behave like Maestro: ALWAYS answer the operator. Meta questions (who you are, how you work, what catalog you have) → answer from the pack. Data questions → use a tool from TOOLS.md.

Output ONE JSON object only (no markdown fences):
{"action":"reply","text":"..."} OR {"action":"tool","name":"...","args":{}}

Conversation:
{$conversation}
Write that JSON object to this file: {$out}
PROMPT;

        $env = getenv();
        if (! is_array($env)) {
            $env = [];
        }
        $apiKey = env('CURSOR_API_KEY');
        if (is_string($apiKey) && $apiKey !== '') {
            $env['CURSOR_API_KEY'] = $apiKey;
        }
        $env['HOME'] = $this->writableCursorHome();

        $process = new Process(
            [$binary, '--mode', 'ask', '--print', '--output-format', 'text', '--trust', '--workspace', $workspace, $prompt],
            $workspace,
            $env,
            null,
            $timeout
        );

        $stdout = '';
        $exitCode = 0;
        try {
            $process->mustRun();
            $stdout = $process->getOutput();
        } catch (ProcessFailedException) {
            $exitCode = (int) $process->getExitCode();
            $stderr = $this->safeSnippet($process->getErrorOutput());
            Log::warning('admin_chat: cursor-agent failed', [
                'exit' => $exitCode,
                'stderr' => mb_substr($process->getErrorOutput(), 0, 500),
            ]);
            $stdout = $process->getOutput();
            $this->lastFailure = 'cursor_exit_'.$exitCode.($stderr !== '' ? ': '.$stderr : '');
        }

        if (File::isFile($out)) {
            $parsed = $this->parseModelOutput(File::get($out));
            File::delete($out);
            if ($parsed !== null) {
                $this->lastFailure = null;

                return $parsed;
            }
        }

        $parsed = $this->parseModelOutput($stdout);
        if ($parsed === null && $this->lastFailure === null) {
            $this->lastFailure = $stdout === ''
                ? 'cursor_sin_salida'
                : 'cursor_sin_json_utilizable';
        }
        if ($parsed !== null) {
            $this->lastFailure = null;
        }

        return $parsed;
    }

    private function syncPackIntoWorkspace(string $workspace): void
    {
        $packDir = $workspace.'/pack';
        File::ensureDirectoryExists($packDir);
        $src = (string) config('admin_chat.pack_path');
        foreach (['CONTEXT.md', 'DENY.md', 'ALLOW.md', 'SCHEMA-BRIEF.md', 'TOOLS.md', 'EXAMPLES.md'] as $file) {
            $from = $src.DIRECTORY_SEPARATOR.$file;
            if (File::isFile($from)) {
                File::copy($from, $packDir.'/'.$file);
            }
        }
    }

    /**
     * Writable scratch HOME for cursor-agent + symlink to the live Maestro auth bind-mount.
     * Never copy auth.json — the mount is the same host inode (token updates are instant).
     */
    private function writableCursorHome(): string
    {
        $configured = config('admin_chat.cursor.home');
        $home = is_string($configured) && $configured !== ''
            ? $configured
            : storage_path('app/admin-chat/cursor-home');

        File::ensureDirectoryExists($home.'/.config/cursor');
        File::ensureDirectoryExists($home.'/.cursor');

        $authLive = (string) config('admin_chat.cursor.auth', '/run/cursor-auth/auth.json');
        $authLink = $home.'/.config/cursor/auth.json';
        if ($authLive !== '' && is_file($authLive)) {
            if (is_link($authLink) || file_exists($authLink)) {
                @unlink($authLink);
            }
            @symlink($authLive, $authLink);
        }

        @chmod($home, 0775);
        @chmod($home.'/.config', 0775);
        @chmod($home.'/.config/cursor', 0775);
        @chmod($home.'/.cursor', 0775);

        return $home;
    }

    /**
     * Accept strict JSON actions, or free-text as a reply (Maestro never swallows prose).
     *
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    private function parseModelOutput(string $raw): ?array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return null;
        }

        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded) && ! empty($decoded['action'])) {
                $action = (string) $decoded['action'];
                if ($action === 'tool') {
                    return [
                        'action' => 'tool',
                        'name' => (string) ($decoded['name'] ?? ''),
                        'args' => is_array($decoded['args'] ?? null) ? $decoded['args'] : [],
                    ];
                }
                if ($action === 'reply') {
                    $text = trim((string) ($decoded['text'] ?? ''));
                    if ($text !== '') {
                        return ['action' => 'reply', 'text' => $text];
                    }
                }
            }
        }

        $text = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $raw) ?? $raw;
        $text = trim($text);
        if ($text === '' || str_starts_with($text, '{')) {
            return null;
        }

        return ['action' => 'reply', 'text' => $text];
    }

    private function safeSnippet(string $raw): string
    {
        $raw = preg_replace('/\b[A-Za-z0-9_-]{20,}\b/', '…', $raw) ?? $raw;
        $raw = preg_replace('/\s+/', ' ', $raw) ?? $raw;
        $raw = trim($raw);

        return mb_substr($raw, 0, 180);
    }
}
