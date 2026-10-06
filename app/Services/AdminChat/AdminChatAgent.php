<?php

namespace App\Services\AdminChat;

class AdminChatAgent
{
    public function __construct(
        private readonly AdminChatContextPack $pack,
        private readonly AdminChatToolService $tools,
        private readonly AdminChatHeuristic $heuristic,
        private readonly AdminChatLlmClient $llm,
    ) {}

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}
     */
    public function handle(string $message, string $provider, array $history = []): array
    {
        $provider = $this->normalizeProvider($provider);
        $suggested = $this->heuristic->suggest($message);
        if (is_array($suggested) && isset($suggested['deny'])) {
            return [
                'reply' => $this->denyText((string) $suggested['deny']),
                'provider' => $provider,
                'tools' => [],
                'downloads' => [],
            ];
        }

        $used = [];
        $downloads = [];
        $lastTool = null;

        if (in_array($provider, ['ollama', 'cursor'], true)) {
            $loop = $this->llmLoop($message, $provider, $history, $used, $downloads, $lastTool);
            if ($loop !== null) {
                return $loop;
            }
            $provider = $provider.'+heuristic';
        }

        if (is_array($suggested) && isset($suggested['name'])) {
            $result = $this->tools->run((string) $suggested['name'], $suggested['args'] ?? []);
            $used[] = (string) $suggested['name'];
            $lastTool = $result;
            if (isset($result['download']) && is_array($result['download'])) {
                $downloads[] = $result['download'];
            }

            return [
                'reply' => $this->formatToolReply($result),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        return [
            'reply' => 'No he podido interpretar la pregunta. Prueba un código de producto, un id de pedido o “cuántos clientes hay”.',
            'provider' => $provider,
            'tools' => $used,
            'downloads' => $downloads,
        ];
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  list<string>  $used
     * @param  list<array<string, mixed>>  $downloads
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}|null
     */
    private function llmLoop(string $message, string $provider, array $history, array &$used, array &$downloads, mixed &$lastTool): ?array
    {
        $system = "You are the Serra admin assistant. Follow the pack. Output JSON only.\n"
            ."{\"action\":\"tool\",\"name\":\"...\",\"args\":{}} or {\"action\":\"reply\",\"text\":\"...\"}.\n"
            ."v1 is read-only. Compact answers.\n\n".$this->pack->load();
        $messages = [
            ['role' => 'system', 'content' => $system],
        ];
        foreach (array_slice($history, -((int) config('admin_chat.max_history', 8))) as $h) {
            if (! empty($h['role']) && ! empty($h['content'])) {
                $messages[] = ['role' => (string) $h['role'], 'content' => (string) $h['content']];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $max = (int) config('admin_chat.max_tool_rounds', 3);
        for ($i = 0; $i < $max; $i++) {
            $action = $this->llm->complete($provider, $messages);
            if ($action === null) {
                return null;
            }
            if (($action['action'] ?? '') === 'reply') {
                $text = trim((string) ($action['text'] ?? ''));
                if ($text === '') {
                    return null;
                }

                return [
                    'reply' => $text,
                    'provider' => $provider,
                    'tools' => $used,
                    'downloads' => $downloads,
                ];
            }
            $name = (string) ($action['name'] ?? '');
            if ($name === '') {
                return null;
            }
            $result = $this->tools->run($name, $action['args'] ?? []);
            $used[] = $name;
            $lastTool = $result;
            if (isset($result['download']) && is_array($result['download'])) {
                $downloads[] = $result['download'];
            }
            $messages[] = ['role' => 'assistant', 'content' => json_encode($action, JSON_UNESCAPED_UNICODE)];
            $messages[] = ['role' => 'user', 'content' => 'TOOL_RESULT '.json_encode($result, JSON_UNESCAPED_UNICODE)];
        }

        if (is_array($lastTool)) {
            return [
                'reply' => $this->formatToolReply($lastTool),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function formatToolReply(array $result): string
    {
        if (! ($result['ok'] ?? false)) {
            $err = (string) ($result['error'] ?? 'error');
            if ($err === 'not_found') {
                return 'No he encontrado ese registro.';
            }
            if ($err === 'not_an_order_kind') {
                return 'Ese id no es un pedido confirmado (kind=order). No se puede emitir factura/albarán.';
            }

            return 'No he podido completar la consulta ('.$err.').';
        }
        $lines = [];
        if (! empty($result['summary'])) {
            $lines[] = (string) $result['summary'];
        }
        $data = $result['data'] ?? [];
        if (is_array($data)) {
            foreach (array_slice($data, 0, 10) as $row) {
                if (is_array($row)) {
                    $bits = [];
                    foreach (['id', 'code', 'name', 'kind', 'status', 'login_email', 'stock', 'price', 'group_value', 'aggregate_value'] as $k) {
                        if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                            $bits[] = $k.'='.$row[$k];
                        }
                    }
                    if ($bits !== []) {
                        $lines[] = implode(' · ', $bits);
                    }
                }
            }
        }
        if (isset($result['meta']['total'])) {
            $lines[] = 'total='.$result['meta']['total'];
        }
        if (isset($result['download']['url'])) {
            $lines[] = 'Descarga (HTML/CSV): '.$result['download']['url'];
            if (($result['download']['content_type'] ?? '') === 'text/html') {
                $lines[] = 'Es HTML imprimible, no un PDF binario.';
            }
        }
        $text = implode("\n", $lines);

        return $text !== '' ? $text : 'Sin filas.';
    }

    private function denyText(string $code): string
    {
        return match ($code) {
            'secrets' => 'No puedo mostrar secretos ni contraseñas.',
            default => 'En v1 solo puedo leer y exportar. Crear o editar (incluido adjuntar PNG) aún no está habilitado.',
        };
    }

    private function normalizeProvider(string $provider): string
    {
        $provider = strtolower(trim($provider));
        if (in_array($provider, ['ollama', 'cursor', 'heuristic'], true)) {
            return $provider;
        }

        return (string) config('admin_chat.default_provider', 'ollama');
    }
}
