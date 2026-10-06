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

        return is_string($content) ? $this->parseJsonAction($content) : null;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    private function completeCursor(array $messages): ?array
    {
        $binary = (string) config('admin_chat.cursor.binary');
        $timeout = (int) config('admin_chat.cursor.timeout', 60);
        $dir = storage_path('app/admin-chat');
        File::ensureDirectoryExists($dir);
        $out = $dir.'/reply-'.uniqid('', true).'.json';
        $prompt = "You are Sierra, the Serralleria Solidària admin assistant. Reply with ONE JSON object only, no markdown.\n"
            ."Schema: {\"action\":\"tool\",\"name\":\"tool_name\",\"args\":{}} OR {\"action\":\"reply\",\"text\":\"...\"}.\n"
            ."For greetings/identity use action=reply. Read-only. Use tools from the system message when needed.\n\n"
            .json_encode($messages, JSON_UNESCAPED_UNICODE)
            ."\nWrite ONLY that JSON object to this file: {$out}";

        $env = getenv();
        if (! is_array($env)) {
            $env = [];
        }
        $apiKey = env('CURSOR_API_KEY');
        if (is_string($apiKey) && $apiKey !== '') {
            $env['CURSOR_API_KEY'] = $apiKey;
        }
        $env['HOME'] = $this->writableCursorHome();

        // Prefer a scratch workspace: the baked app tree is root-owned and agent may mkdir.
        $workspace = storage_path('app/admin-chat/workspace');
        File::ensureDirectoryExists($workspace);

        $process = new Process(
            [$binary, '--yolo', '--print', '--trust', '--workspace', $workspace, $prompt],
            $workspace,
            $env,
            null,
            $timeout
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException) {
            Log::warning('admin_chat: cursor-agent failed', [
                'exit' => $process->getExitCode(),
                'stderr' => mb_substr($process->getErrorOutput(), 0, 500),
            ]);
            $stdout = $process->getOutput();

            return $this->parseJsonAction($stdout);
        }

        if (File::isFile($out)) {
            $parsed = $this->parseJsonAction(File::get($out));
            File::delete($out);

            return $parsed;
        }

        return $this->parseJsonAction($process->getOutput());
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
     * @return array{action: string, name?: string, args?: array<string, mixed>, text?: string}|null
     */
    private function parseJsonAction(string $raw): ?array
    {
        $raw = trim($raw);
        if (preg_match('/\{.*\}/s', $raw, $m)) {
            $raw = $m[0];
        }
        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || empty($decoded['action'])) {
            return null;
        }
        $action = (string) $decoded['action'];
        if ($action === 'tool') {
            return [
                'action' => 'tool',
                'name' => (string) ($decoded['name'] ?? ''),
                'args' => is_array($decoded['args'] ?? null) ? $decoded['args'] : [],
            ];
        }
        if ($action === 'reply') {
            return [
                'action' => 'reply',
                'text' => (string) ($decoded['text'] ?? ''),
            ];
        }

        return null;
    }
}
