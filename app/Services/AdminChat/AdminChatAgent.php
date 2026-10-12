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
     * @return array{
     *     reply: string,
     *     provider: string,
     *     tools: list<string>,
     *     downloads: list<array<string, mixed>>,
     *     fallback?: bool,
     *     fallback_reason?: string
     * }
     */
    public function handle(string $message, string $provider, array $history = []): array
    {
        $provider = $this->normalizeProvider($provider);
        $suggested = $this->heuristic->suggest($message);

        // Hard policy (like Maestro DENY) — never ask the LLM to violate this.
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
        $requestedLlm = in_array($provider, ['ollama', 'cursor'], true);

        // Maestro-style: with an LLM provider, the pack + model choose tools.
        // Heuristic is only a hint / fallback — never a script that skips the brain.
        if ($requestedLlm) {
            $loop = $this->llmLoop($message, $provider, $history, $suggested, $used, $downloads, $lastTool);
            if ($loop !== null) {
                return $loop;
            }
            $reason = $this->llm->consumeLastFailure() ?? 'llm_sin_respuesta_utilizable';
            $fallback = $this->heuristicHandle($suggested, $provider.'+fallback', $used, $downloads);

            return $this->withFallbackNotice($fallback, $provider, $reason);
        }

        return $this->heuristicHandle($suggested, $provider, $used, $downloads);
    }

    /**
     * Script-generated notice (not LLM). Visible in chat when falling back.
     *
     * @param  array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}  $result
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>, fallback: bool, fallback_reason: string}
     */
    private function withFallbackNotice(array $result, string $requestedProvider, string $reason): array
    {
        $banner = '[Fallback · script] El modelo «'.$requestedProvider.'» no entregó una respuesta usable '
            .'(motivo: '.$reason.'). Continúo con tools/heurística — no es el LLM.';

        $result['reply'] = $this->sanitizeWriting($banner."\n\n".$result['reply']);
        $result['fallback'] = true;
        $result['fallback_reason'] = $reason;

        return $result;
    }

    /** Strip em/en dashes; UI + WRITING.md forbid them. */
    private function sanitizeWriting(string $text): string
    {
        return str_replace(["\u{2014}", "\u{2013}", '—', '–'], [' - ', '-', ' - ', '-'], $text);
    }

    /**
     * @param  array{name?: string, args?: array<string, mixed>, reply?: string, deny?: string}|null  $suggested
     * @param  list<string>  $used
     * @param  list<array<string, mixed>>  $downloads
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}
     */
    private function heuristicHandle(?array $suggested, string $provider, array $used, array $downloads): array
    {
        if (is_array($suggested) && isset($suggested['reply'])) {
            $code = (string) $suggested['reply'];
            if ($code === 'demo') {
                return $this->demoReply($provider);
            }

            return [
                'reply' => $this->sanitizeWriting($this->cannedReply($code)),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        if (is_array($suggested) && isset($suggested['name'])) {
            $result = $this->tools->run((string) $suggested['name'], $suggested['args'] ?? []);
            $used[] = (string) $suggested['name'];
            if (isset($result['download']) && is_array($result['download'])) {
                $downloads[] = $result['download'];
            }

            return [
                'reply' => $this->sanitizeWriting($this->formatToolReply((string) $suggested['name'], $result, $suggested['args'] ?? [])),
                'provider' => $provider,
                'tools' => $used,
                'downloads' => $downloads,
            ];
        }

        // Maestro never dead-ends: explain the catalog from the pack.
        return [
            'reply' => $this->sanitizeWriting($this->cannedReply('catalog')),
            'provider' => $provider,
            'tools' => $used,
            'downloads' => $downloads,
        ];
    }

    /**
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}
     */
    private function demoReply(string $provider): array
    {
        $result = $this->tools->run('explorer_aggregate', [
            'table' => 'clients',
            'metric' => 'count',
            'group_by' => 'is_active',
        ]);
        $body = $this->formatToolReply('explorer_aggregate', $result, [
            'table' => 'clients',
            'group_by' => 'is_active',
        ]);

        return [
            'reply' => $this->sanitizeWriting(
                $this->cannedReply('identity')."\n\n**Demo rápida** (conteo de clientes):\n\n".$body
            ),
            'provider' => $provider,
            'tools' => ['explorer_aggregate'],
            'downloads' => [],
        ];
    }

    /**
     * @param  list<array{role: string, content: string}>  $history
     * @param  array{name?: string, args?: array<string, mixed>, reply?: string}|null  $hint
     * @param  list<string>  $used
     * @param  list<array<string, mixed>>  $downloads
     * @return array{reply: string, provider: string, tools: list<string>, downloads: list<array<string, mixed>>}|null
     */
    private function llmLoop(string $message, string $provider, array $history, ?array $hint, array &$used, array &$downloads, mixed &$lastTool): ?array
    {
        $system = implode("\n", [
            'You are Sierra — Maestro\'s daughter for Serralleria Solidària admin.',
            'Behave like Maestro: ALWAYS answer. Never say you do not understand without explaining your catalog.',
            'Meta (who you are, how you work, what context catalog you have) → reply from the pack below (no tool).',
            'Data (clients, products, orders, money, top sellers) → pick a tool from TOOLS.md, then answer.',
            'Multi-intent: greet briefly as Sierra AND fetch the data.',
            'Never invent rows, IDs, prices, stock, or money.',
            'v1 read-only (DENY.md).',
            'Output ONE JSON object only:',
            '{"action":"tool","name":"...","args":{}} OR {"action":"reply","text":"..."}',
            'After TOOL_RESULT → action=reply in the user language.',
            'Top sellers → stats_get which=top_products. Order money → order_search.',
            'Write Markdown in reply text (lists/tables). Never use em dash (—) or en dash (–). Follow WRITING.md.',
            '',
            $this->pack->load(),
        ]);

        if (is_array($hint) && isset($hint['name'])) {
            $system .= "\n\n# HEURISTIC_HINT (optional, not mandatory)\n"
                .json_encode(['name' => $hint['name'], 'args' => $hint['args'] ?? []], JSON_UNESCAPED_UNICODE);
        }

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
                break;
            }
            if (($action['action'] ?? '') === 'reply') {
                $text = trim((string) ($action['text'] ?? ''));
                if ($text === '') {
                    break;
                }

                return [
                    'reply' => $this->sanitizeWriting($text),
                    'provider' => $provider,
                    'tools' => $used,
                    'downloads' => $downloads,
                ];
            }
            $name = (string) ($action['name'] ?? '');
            if ($name === '') {
                break;
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
            $messages[] = ['role' => 'user', 'content' => 'TOOL_RESULT '.json_encode($result, JSON_UNESCAPED_UNICODE)
                ."\nNow reply with action=reply and a short human answer in the user's language. If they also asked your name, say you are Sierra in one short clause."];
        }

        // If the model called a tool but never produced a final reply, still surface the data.
        if (is_array($lastTool) && $lastName !== '') {
            return [
                'reply' => $this->sanitizeWriting($this->formatToolReply($lastName, $lastTool, $lastArgs)),
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
            $rows = [];
            foreach (array_slice($data, 0, 20) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $g = $row['group_value'] ?? '?';
                $v = (int) ($row['aggregate_value'] ?? 0);
                $sum += $v;
                if ($groupBy === 'is_active') {
                    $label = ((string) $g === '1' || $g === 1 || $g === true) ? 'activos' : 'inactivos';
                    $rows[] = [$label, (string) $v];
                } else {
                    $rows[] = [(string) $g, (string) $v];
                }
            }
            if ($rows === []) {
                return 'No hay grupos para '.$table.'.';
            }

            return '**Total '.$table.': '.$sum."**\n\n".$this->mdTable(['Grupo', 'Cantidad'], $rows);
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
            $rows = [];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rows[] = [
                    (string) ($row['code'] ?? ''),
                    (string) ($row['name'] ?? ''),
                    isset($row['id']) ? (string) $row['id'] : '',
                    isset($row['stock']) ? (string) $row['stock'] : '',
                    isset($row['price']) ? $row['price'].' €' : '',
                ];
            }

            return 'He encontrado **'.$total.'** '.$label.($shown < $total ? ' (muestro '.$shown.')' : '').":\n\n"
                .$this->mdTable(['Código', 'Nombre', 'Id', 'Stock', 'Precio'], $rows);
        }

        if ($tool === 'order_get' || $tool === 'order_search') {
            if ($total === 0 || $data === []) {
                return 'No he encontrado ese pedido.';
            }
            $moneySum = 0.0;
            $rows = [];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $due = isset($row['amount_due']) ? (float) $row['amount_due'] : (isset($row['lines_subtotal']) ? (float) $row['lines_subtotal'] : null);
                if ($due !== null) {
                    $moneySum += $due;
                }
                $rows[] = [
                    '#'.($row['id'] ?? '?'),
                    (string) ($row['status'] ?? ''),
                    (string) ($row['order_date'] ?? ''),
                    $due !== null ? $due.' €' : '',
                    (string) ($row['client_email'] ?? ''),
                ];
            }
            $head = $tool === 'order_search'
                ? 'Últimos pedidos (total **'.$total.'**, muestro '.min(10, count($data)).'):'
                : 'Pedido:';
            $out = $head."\n\n".$this->mdTable(['Id', 'Estado', 'Fecha', 'Importe', 'Cliente'], $rows);
            if ($tool === 'order_search' && $moneySum > 0) {
                $out .= "\n\nSuma (mostrados): **".round($moneySum, 2).' €**';
            }

            return $out;
        }

        if ($tool === 'stats_get') {
            $which = (string) ($args['which'] ?? '');
            if ($which === 'top_products') {
                if ($data === []) {
                    return 'No hay datos de productos vendidos aún.';
                }
                $rows = [];
                foreach (array_slice($data, 0, 10) as $i => $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $rows[] = [
                        (string) ($i + 1),
                        (string) ($row['code'] ?? ''),
                        (string) ($row['name'] ?? ''),
                        (string) ($row['qty_sold'] ?? 0),
                        isset($row['revenue']) ? $row['revenue'].' €' : '',
                    ];
                }

                return "**Productos más vendidos** (por cantidad):\n\n"
                    .$this->mdTable(['#', 'Código', 'Nombre', 'Qty', 'Ingresos'], $rows);
            }
            if ($which === 'low_stock') {
                $rows = [];
                foreach (array_slice($data, 0, 10) as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $rows[] = [(string) ($row['code'] ?? ''), (string) ($row['stock'] ?? 0)];
                }

                return "**Stock bajo:**\n\n".$this->mdTable(['Código', 'Stock'], $rows);
            }
        }

        if ($tool === 'client_search' || $tool === 'client_get') {
            if ($total === 0 || $data === []) {
                return 'No he encontrado clientes con ese criterio.';
            }
            $rows = [];
            foreach (array_slice($data, 0, 10) as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $rows[] = [
                    '#'.($row['id'] ?? '?'),
                    (string) ($row['login_email'] ?? ''),
                    (string) ($row['identification'] ?? ''),
                ];
            }

            return 'Clientes (**'.$total."**):\n\n".$this->mdTable(['Id', 'Email', 'Identificación'], $rows);
        }

        $lines = [];
        foreach (array_slice($data, 0, 10) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $bits = [];
            foreach (['id', 'code', 'name', 'kind', 'status', 'login_email', 'stock', 'price', 'group_value', 'aggregate_value', 'amount_due', 'lines_subtotal'] as $k) {
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

    /**
     * @param  list<string>  $headers
     * @param  list<list<string>>  $rows
     */
    private function mdTable(array $headers, array $rows): string
    {
        $esc = static fn (string $c): string => str_replace('|', '\\|', $c);
        $line = '| '.implode(' | ', array_map($esc, $headers)).' |';
        $sep = '| '.implode(' | ', array_fill(0, count($headers), '---')).' |';
        $body = [];
        foreach ($rows as $row) {
            $cells = [];
            foreach ($headers as $i => $_) {
                $cells[] = $esc((string) ($row[$i] ?? ''));
            }
            $body[] = '| '.implode(' | ', $cells).' |';
        }

        return $line."\n".$sep."\n".implode("\n", $body);
    }

    private function cannedReply(string $code): string
    {
        return match ($code) {
            'identity', 'catalog' => 'Soy **Sierra**, hija de Maestro, asistente de administración de Serralleria Solidària.'."\n\n"
                .'Funciono como Maestro: tengo un **catálogo de contexto** en `docs/admin-chat/` '
                .'(CONTEXT, ALLOW, DENY, TOOLS, SCHEMA-BRIEF, EXAMPLES, WRITING). '
                .'Cuando preguntas por datos, uso tools (no invento filas).'."\n\n"
                .'**Catálogo de acciones (v1, solo lectura):**'."\n"
                .'- Productos / packs / categorías: `catalog_search` / `catalog_get`'."\n"
                .'- Clientes: `client_search` / `client_get` / conteos'."\n"
                .'- Pedidos, importes, facturas HTML: `order_search` / `order_get` / `order_export_doc`'."\n"
                .'- Más vendidos / stock bajo: `stats_get`'."\n"
                .'- Export CSV: `explorer_export_csv`'."\n\n"
                .'Ejemplos: «¿cuántos clientes hay?», «productos más vendidos», «últimas 10 facturas», «busca evoK1».',
            'need_product_query' => 'Dime un código o nombre de producto (ej. «existe evoK1» o «busca bombín»).',
            default => 'Soy **Sierra**. Puedo explicar mi catálogo o consultar datos del admin. '
                .'Prueba: «cómo funcionas?», «productos más vendidos», «¿cuántos clientes hay?».',
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
