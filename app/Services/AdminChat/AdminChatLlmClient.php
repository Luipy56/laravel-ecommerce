<?php

namespace App\Services\AdminChat;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AdminChatLlmClient
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    public function complete(string $provider, array $messages): ?array
    {
        if ($provider === 'ollama') {
            return $this->completeOllama($messages);
        }
        if ($provider === 'cursor') {
            return $this->completeCursor($messages);
        }

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

            return null;
        }

        if (! $response->successful()) {
            Log::warning('admin_chat: ollama status', ['status' => $response->status()]);

            return null;
        }

        $content = data_get($response->json(), 'message.content');

        return is_string($content) ? $this->parseModelOutput($content) : null;
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
        try {
            $process->mustRun();
            $stdout = $process->getOutput();
        } catch (ProcessFailedException) {
            Log::warning('admin_chat: cursor-agent failed', [
                'exit' => $process->getExitCode(),
                'stderr' => mb_substr($process->getErrorOutput(), 0, 500),
            ]);
            $stdout = $process->getOutput();
        }

        if (File::isFile($out)) {
            $parsed = $this->parseModelOutput(File::get($out));
            File::delete($out);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        return $this->parseModelOutput($stdout);
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
     * cursor-agent needs a writable HOME (ro auth mount alone is not enough).
     */
    private function writableCursorHome(): string
    {
        $home = storage_path('app/admin-chat/cursor-home');
        File::ensureDirectoryExists($home.'/.config/cursor');
        File::ensureDirectoryExists($home.'/.cursor');

        $authTarget = $home.'/.config/cursor/auth.json';
        if (! File::isFile($authTarget)) {
            $seed = config('admin_chat.cursor.home');
            $seedAuth = is_string($seed) && $seed !== ''
                ? rtrim($seed, '/').'/.config/cursor/auth.json'
                : '';
            if ($seedAuth !== '' && File::isFile($seedAuth)) {
                File::copy($seedAuth, $authTarget);
            }
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

        // Strip obvious agent chatter wrappers; keep human prose.
        $text = preg_replace('/^```(?:json)?\s*|\s*```$/u', '', $raw) ?? $raw;
        $text = trim($text);
        if ($text === '' || str_starts_with($text, '{')) {
            return null;
        }

        return ['action' => 'reply', 'text' => $text];
    }
}
