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
        // PHP-FPM runs as www-data; prefer CURSOR_API_KEY from app env when present.
        $apiKey = env('CURSOR_API_KEY');
        if (is_string($apiKey) && $apiKey !== '') {
            $env['CURSOR_API_KEY'] = $apiKey;
        }
        $home = config('admin_chat.cursor.home');
        if (is_string($home) && $home !== '') {
            $env['HOME'] = $home;
        }

        $process = new Process(
            [$binary, '--yolo', '--print', '--trust', '--workspace', base_path(), $prompt],
            base_path(),
            $env,
            null,
            $timeout
        );

        try {
            $process->mustRun();
        } catch (ProcessFailedException) {
            Log::warning('admin_chat: cursor-agent failed', ['exit' => $process->getExitCode()]);
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
