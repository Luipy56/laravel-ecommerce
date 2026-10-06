<?php

namespace App\Services\AdminChat;

class AdminChatAgent
{
    public const NAME = 'Sierra';

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

        if (is_array($suggested) && isset($suggested['reply'])) {
            return [
                'reply' => $this->cannedReply((string) $suggested['reply']),
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
            $provider = $provider.'+fallback';
        }

        if (is_array($suggested) && isset($suggested['name'])) {
            $result = $this->tools->run((string) $suggested['name'], $suggested['args'] ?? []);
            $used[] = (string) $suggested['name'];
            if (isset($result['download']) && is_array($result['download'])) {
                $downloads[] = $result['download'];
            }

            return [
                'reply' => $this->formatToolReply((string) $suggested['name'], $result, $suggested['args'] ?? []),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        return [
            'reply' => $this->cannedReply('unclear'),
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
        $system = 'You are Sierra, the admin assistant for Serralleria Solidària.'."\n"
            ."Always identify as Sierra when asked your name.\n"
            ."Follow the pack. Output JSON only.\n"
            ."{\"action\":\"tool\",\"name\":\"...\",\"args\":{}} or {\"action\":\"reply\",\"text\":\"...\"}.\n"
            ."For greetings or identity questions, use action=reply (no tools).\n"
            ."v1 is read-only. Answer in the user's language. Compact answers.\n\n"
            .$this->pack->load();
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
        $lastName = '';
        $lastArgs = [];
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
            $args = $action['args'] ?? [];
            $result = $this->tools->run($name, is_array($args) ? $args : []);
            $used[] = $name;
            $lastTool = $result;
            $lastName = $name;
            $lastArgs = is_array($args) ? $args : [];
            if (isset($result['download']) && is_array($result['download'])) {
                $downloads[] = $result['download'];
            }
            $messages[] = ['role' => 'assistant', 'content' => json_encode($action, JSON_UNESCAPED_UNICODE)];
            $messages[] = ['role' => 'user', 'content' => 'TOOL_RESULT '.json_encode($result, JSON_UNESCAPED_UNICODE)."\nNow reply with action=reply and a short human answer in Spanish (or the user language)."];
        }

        if (is_array($lastTool) && $lastName !== '') {
            return [
                'reply' => $this->formatToolReply($lastName, $lastTool, $lastArgs),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $result
     * @param  array<string, mixed>  $args
     */
    private function formatToolReply(string $tool, array $result, array $args = []): string
    {
        if (! ($result['ok'] ?? false)) {
            $err = (string) ($result['error'] ?? 'error');

            return match ($err) {
                'not_found' => 'No he encontrado ese registro.',
                'not_an_order_kind' => 'Ese id no es un pedido confirmado (kind=order). No se puede emitir factura/albarán.',
                default => 'No he podido completar la consulta ('.$err.').',
            };
        }

        $data = is_array($result['data'] ?? null) ? $result['data'] : [];
        $total = (int) ($result['meta']['total'] ?? count($data));
        $shown = (int) ($result['meta']['returned'] ?? count($data));

        if ($tool === 'explorer_aggregate') {
            $table = (string) ($args['table'] ?? 'tabla');
            $groupBy = (string) ($args['group_by'] ?? '');
            $sum = 0;
            $lines = [];
            foreach (array_slice($data, 0, 20) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $g = $row['group_value'] ?? '?';
                $v = (int) ($row['aggregate_value'] ?? 0);
                $sum += $v;
                if ($groupBy === 'is_active') {
                    $label = ((string) $g === '1' || $g === 1 || $g === true) ? 'activos' : 'inactivos';
                    $lines[] = '· '.$label.': '.$v;
                } else {
                    $lines[] = '· '.$g.': '.$v;
                }
            }
            if ($lines === []) {
                return 'No hay grupos para '.$table.'.';
            }

            return 'Total '.$table.': '.$sum."\n".implode("\n", $lines);
        }

        if ($tool === 'catalog_search') {
            $kind = (string) ($args['kind'] ?? 'product');
            $label = match ($kind) {
                'pack' => 'packs',
                'category' => 'categorías',
                default => 'productos',
            };
            if ($total === 0) {
                $q = trim((string) ($args['q'] ?? ''));

                return $q !== ''
                    ? 'No he encontrado '.$label.' que coincidan con «'.$q.'».'
                    : 'No he encontrado '.$label.'.';
            }
            $lines = ['He encontrado '.$total.' '.$label.( $shown < $total ? ' (muestro '.$shown.')' : '').':'];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $bits = [];
                if (! empty($row['code'])) {
                    $bits[] = (string) $row['code'];
                }
                if (! empty($row['name'])) {
                    $bits[] = (string) $row['name'];
                }
                if (isset($row['id'])) {
                    $bits[] = 'id '.$row['id'];
                }
                if (isset($row['stock'])) {
                    $bits[] = 'stock '.$row['stock'];
                }
                if (isset($row['price'])) {
                    $bits[] = $row['price'].' €';
                }
                if ($bits !== []) {
                    $lines[] = '· '.implode(' · ', $bits);
                }
            }

            return implode("\n", $lines);
        }

        if ($tool === 'order_get' || $tool === 'order_search') {
            if ($total === 0 || $data === []) {
                return 'No he encontrado ese pedido.';
            }
            $lines = [];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lines[] = 'Pedido #'.($row['id'] ?? '?')
                    .' · kind='.($row['kind'] ?? '?')
                    .' · status='.($row['status'] ?? '?')
                    .(! empty($row['client_email']) ? ' · '.$row['client_email'] : '');
            }

            return implode("\n", $lines);
        }

        if ($tool === 'client_search' || $tool === 'client_get') {
            if ($total === 0 || $data === []) {
                return 'No he encontrado clientes con ese criterio.';
            }
            $lines = ['Clientes ('.$total.'):'];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $lines[] = '· #'.($row['id'] ?? '?').' '.($row['login_email'] ?? '').' · '.($row['identification'] ?? '');
            }

            return implode("\n", $lines);
        }

        $lines = [];
        foreach (array_slice($data, 0, 10) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $bits = [];
            foreach (['id', 'code', 'name', 'kind', 'status', 'login_email', 'stock', 'price', 'group_value', 'aggregate_value'] as $k) {
                if (array_key_exists($k, $row) && $row[$k] !== null && $row[$k] !== '') {
                    $bits[] = $k.'='.$row[$k];
                }
            }
            if ($bits !== []) {
                $lines[] = '· '.implode(' · ', $bits);
            }
        }
        if (isset($result['download']['url'])) {
            $lines[] = 'Descarga: '.$result['download']['url'];
            if (($result['download']['content_type'] ?? '') === 'text/html') {
                $lines[] = '(HTML imprimible, no PDF binario.)';
            }
        }
        if ($lines === []) {
            return $total === 0 ? 'Sin resultados.' : 'Consulta OK ('.$total.' filas).';
        }

        return implode("\n", $lines);
    }

    private function cannedReply(string $code): string
    {
        return match ($code) {
            'identity' => 'Soy **Sierra**, la asistente de administración de Serralleria Solidària. '
                .'Puedo consultar el catálogo (productos, packs, categorías), clientes, pedidos y pagos; '
                .'contar registros; exportar CSV; y abrir factura/albarán HTML. '
                .'En esta versión solo leo y exporto: no creo ni edito datos. '
                .'Prueba: «¿cuántos clientes hay?», «busca producto evoK1», «factura del pedido 12».',
            'need_product_query' => 'Dime un código o nombre de producto (ej. «existe evoK1» o «busca bombín»).',
            default => 'No estoy segura de lo que pides. Puedo buscar productos/packs, contar clientes o pedidos, '
                .'ver un pedido por id, o exportar CSV/factura. Pregúntame con un dato concreto.',
        };
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
