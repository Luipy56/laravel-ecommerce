<?php

namespace App\Services\AdminChat;

use App\Models\Admin;
use App\Models\Client;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Pack;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductReview;
use App\Models\ReturnRequest;
use App\Models\PersonalizedSolution;
use App\Services\AdminDataExplorerService;
use Illuminate\Support\Str;
use InvalidArgumentException;

class AdminChatToolService
{
    public function __construct(
        private readonly AdminDataExplorerService $explorer,
        private readonly AdminChatDownloadStore $downloads,
    ) {}

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    public function run(string $name, array $args): array
    {
        try {
            return match ($name) {
                'explorer_schema' => $this->explorerSchema(),
                'explorer_query' => $this->explorerQuery($args),
                'explorer_aggregate' => $this->explorerAggregate($args),
                'explorer_export_csv' => $this->explorerExportCsv($args),
                'catalog_search' => $this->catalogSearch($args),
                'catalog_get' => $this->catalogGet($args),
                'order_search' => $this->orderSearch($args),
                'order_get' => $this->orderGet($args),
                'order_export_doc' => $this->orderExportDoc($args),
                'client_search' => $this->clientSearch($args),
                'client_get' => $this->clientGet($args),
                'stats_get' => $this->statsGet($args),
                'entity_get' => $this->entityGet($args),
                default => ['ok' => false, 'error' => 'unknown_tool'],
            };
        } catch (InvalidArgumentException $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'tool_failed'];
        }
    }

    /** @return array<string, mixed> */
    private function explorerSchema(): array
    {
        $schema = $this->explorer->schema();
        $tables = [];
        foreach ($schema['tables'] as $t) {
            $tables[] = [
                'name' => $t['name'],
                'columns' => $t['columns'],
            ];
        }

        return ['ok' => true, 'summary' => 'explorer schema', 'data' => $tables, 'meta' => $schema['limits']];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function explorerQuery(array $args): array
    {
        $table = (string) ($args['table'] ?? '');
        $limit = $this->chatLimit($args);
        $this->explorer->configureSessionTimeout();
        $builder = $this->explorer->buildFilteredQuery(
            $table,
            $this->nullableString($args['q'] ?? null),
            $this->filters($args),
            $this->nullableString($args['date_column'] ?? null),
            $this->nullableString($args['date_from'] ?? null),
            $this->nullableString($args['date_to'] ?? null),
        );
        $paginator = $this->explorer->paginateQuery(
            $builder,
            $table,
            $this->nullableString($args['sort_column'] ?? null),
            (string) ($args['sort_direction'] ?? 'desc'),
            (int) ($args['page'] ?? 1),
            $limit,
        );
        $rows = collect($paginator->items())->map(fn ($row) => $this->compactRow((array) $row))->values()->all();

        return [
            'ok' => true,
            'summary' => $table.' total='.$paginator->total(),
            'data' => $rows,
            'meta' => [
                'total' => $paginator->total(),
                'returned' => count($rows),
                'page' => $paginator->currentPage(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function explorerAggregate(array $args): array
    {
        $table = (string) ($args['table'] ?? '');
        $this->explorer->configureSessionTimeout();
        $rows = $this->explorer->aggregate(
            $table,
            (string) ($args['metric'] ?? 'count'),
            $this->nullableString($args['value_column'] ?? null),
            (string) ($args['group_by'] ?? ''),
            $this->nullableString($args['q'] ?? null),
            $this->filters($args),
            $this->nullableString($args['date_column'] ?? null),
            $this->nullableString($args['date_from'] ?? null),
            $this->nullableString($args['date_to'] ?? null),
            (int) config('admin_data_explorer.max_aggregate_groups', 200),
        );
        $data = $rows->map(fn ($r) => [
            'group_value' => $r->group_value,
            'aggregate_value' => $r->aggregate_value !== null ? (float) $r->aggregate_value : null,
        ])->values()->all();

        return [
            'ok' => true,
            'summary' => $table.' aggregate '.($args['metric'] ?? 'count'),
            'data' => $data,
            'meta' => ['returned' => count($data)],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function explorerExportCsv(array $args): array
    {
        $table = (string) ($args['table'] ?? '');
        $maxRows = (int) config('admin_data_explorer.max_export_rows', 5000);
        $this->explorer->configureSessionTimeout();
        $rows = $this->explorer->exportRows(
            $table,
            $this->nullableString($args['q'] ?? null),
            $this->filters($args),
            $this->nullableString($args['date_column'] ?? null),
            $this->nullableString($args['date_from'] ?? null),
            $this->nullableString($args['date_to'] ?? null),
            $this->nullableString($args['sort_column'] ?? null),
            (string) ($args['sort_direction'] ?? 'desc'),
            $maxRows,
        );
        $columns = $this->explorer->resolvedColumns($table);
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            return ['ok' => false, 'error' => 'csv_failed'];
        }
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $columns);
        foreach ($rows as $row) {
            $arr = (array) $row;
            $line = [];
            foreach ($columns as $col) {
                $line[] = $arr[$col] ?? '';
            }
            fputcsv($handle, $line);
        }
        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';
        fclose($handle);
        $filename = 'data-explorer-'.$table.'-'.now()->format('Y-m-d-His').'.csv';
        $token = $this->downloads->put([
            'filename' => $filename,
            'content_type' => 'text/csv; charset=UTF-8',
            'body' => $csv,
        ]);

        return [
            'ok' => true,
            'summary' => 'CSV '.$filename.' rows='.$rows->count(),
            'data' => [],
            'download' => [
                'token' => $token,
                'filename' => $filename,
                'content_type' => 'text/csv',
                'url' => '/api/v1/admin/chat/downloads/'.$token,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function catalogSearch(array $args): array
    {
        $kind = (string) ($args['kind'] ?? 'product');
        $q = trim((string) ($args['q'] ?? ''));
        $limit = $this->chatLimit($args);

        if ($kind === 'pack') {
            $query = Pack::query()->with('translations')->withCount('packItems');
            if ($q !== '') {
                $term = '%'.$q.'%';
                $query->whereHas('translations', fn ($t) => $t->where('name', 'like', $term)->orWhere('description', 'like', $term));
            }
            if (array_key_exists('is_active', $args)) {
                $query->where('is_active', (bool) $args['is_active']);
            }
            $total = (clone $query)->count();
            $rows = $query->orderBy('id')->limit($limit)->get()->map(fn (Pack $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => (float) $p->price,
                'is_active' => (bool) $p->is_active,
                'pack_items_count' => $p->pack_items_count ?? 0,
            ])->all();

            return $this->okList('packs', $rows, $total);
        }

        if ($kind === 'category') {
            $query = ProductCategory::query()->with('translations');
            if ($q !== '') {
                $term = '%'.$q.'%';
                $query->where(function ($w) use ($term) {
                    $w->where('code', 'like', $term)
                        ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', $term));
                });
            }
            $total = (clone $query)->count();
            $rows = $query->orderBy('id')->limit($limit)->get()->map(fn (ProductCategory $c) => [
                'id' => $c->id,
                'code' => $c->code,
                'name' => $c->name,
                'is_active' => (bool) $c->is_active,
            ])->all();

            return $this->okList('categories', $rows, $total);
        }

        $query = Product::query()->with('translations');
        if ($q !== '') {
            $term = '%'.$q.'%';
            $query->where(function ($w) use ($term) {
                $w->where('code', 'like', $term)
                    ->orWhereHas('translations', fn ($t) => $t->where('name', 'like', $term)->orWhere('search_text', 'like', $term));
            });
        }
        if (array_key_exists('is_active', $args)) {
            $query->where('is_active', (bool) $args['is_active']);
        }
        $total = (clone $query)->count();
        $rows = $query->orderBy('id')->limit($limit)->get()->map(fn (Product $p) => [
            'id' => $p->id,
            'code' => $p->code,
            'name' => $p->name,
            'price' => (float) $p->price,
            'stock' => (int) $p->stock,
            'is_active' => (bool) $p->is_active,
            'security_level' => $p->security_level,
            'category_id' => $p->category_id,
        ])->all();

        return $this->okList('products', $rows, $total);
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function catalogGet(array $args): array
    {
        $kind = (string) ($args['kind'] ?? 'product');
        $id = (int) ($args['id'] ?? 0);
        if ($id < 1) {
            return ['ok' => false, 'error' => 'missing_id'];
        }
        if ($kind === 'pack') {
            $p = Pack::query()->with(['translations', 'packItems'])->find($id);
            if (! $p) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            return [
                'ok' => true,
                'summary' => 'pack '.$p->id,
                'data' => [[
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => (float) $p->price,
                    'is_active' => (bool) $p->is_active,
                    'item_count' => $p->packItems->count(),
                    'product_ids' => $p->packItems->pluck('product_id')->filter()->values()->all(),
                ]],
            ];
        }
        if ($kind === 'category') {
            $c = ProductCategory::query()->with('translations')->find($id);
            if (! $c) {
                return ['ok' => false, 'error' => 'not_found'];
            }

            return [
                'ok' => true,
                'summary' => 'category '.$c->id,
                'data' => [[
                    'id' => $c->id,
                    'code' => $c->code,
                    'name' => $c->name,
                    'is_active' => (bool) $c->is_active,
                ]],
            ];
        }
        $p = Product::query()->with(['translations', 'category.translations'])->find($id);
        if (! $p) {
            return ['ok' => false, 'error' => 'not_found'];
        }

        return [
            'ok' => true,
            'summary' => 'product '.$p->id,
            'data' => [[
                'id' => $p->id,
                'code' => $p->code,
                'name' => $p->name,
                'price' => (float) $p->price,
                'stock' => (int) $p->stock,
                'is_active' => (bool) $p->is_active,
                'security_level' => $p->security_level,
                'category_id' => $p->category_id,
                'category_name' => $p->category?->name,
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function orderSearch(array $args): array
    {
        $query = Order::query()->with(['client:id,login_email', 'lines', 'payments']);
        $q = trim((string) ($args['q'] ?? ''));
        if ($q !== '') {
            if (is_numeric($q)) {
                $query->where('id', (int) $q);
            } else {
                $term = '%'.$q.'%';
                $query->whereHas('client', fn ($c) => $c->where('login_email', 'like', $term));
            }
        }
        if (! empty($args['status'])) {
            $query->where('kind', Order::KIND_ORDER)->where('status', (string) $args['status']);
        }
        if (! empty($args['kind'])) {
            $query->where('kind', (string) $args['kind']);
        }
        if (! empty($args['client_id'])) {
            $query->where('client_id', (int) $args['client_id']);
        }
        $limit = $this->chatLimit($args);
        $total = (clone $query)->count();
        $rows = $query->orderByDesc('id')->limit($limit)->get()->map(function (Order $o) {
            $paid = round((float) $o->payments->sum(fn ($p) => (float) $p->amount), 2);

            return [
                'id' => $o->id,
                'kind' => $o->kind,
                'status' => $o->status,
                'client_id' => $o->client_id,
                'client_email' => $o->client?->login_email,
                'order_date' => $o->order_date?->toDateString(),
                'lines_subtotal' => (float) $o->lines_subtotal,
                'amount_due' => (float) $o->grand_total,
                'payments_sum' => $paid,
            ];
        })->all();

        return $this->okList('orders', $rows, $total);
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function orderGet(array $args): array
    {
        $id = (int) ($args['id'] ?? 0);
        $order = Order::query()->with(['client:id,login_email', 'lines', 'addresses', 'payments'])->find($id);
        if (! $order) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        $lines = $order->lines->take(25)->map(fn ($l) => [
            'id' => $l->id,
            'product_id' => $l->product_id,
            'pack_id' => $l->pack_id,
            'quantity' => $l->quantity,
            'unit_price' => (float) $l->unit_price,
        ])->all();

        return [
            'ok' => true,
            'summary' => 'order '.$order->id.' '.$order->kind.' '.$order->status,
            'data' => [[
                'id' => $order->id,
                'kind' => $order->kind,
                'status' => $order->status,
                'client_id' => $order->client_id,
                'client_email' => $order->client?->login_email,
                'order_date' => $order->order_date?->toDateString(),
                'lines' => $lines,
                'addresses' => $order->addresses->map(fn ($a) => [
                    'type' => $a->type,
                    'city' => $a->city,
                    'postal_code' => $a->postal_code,
                ])->all(),
                'payments' => $order->payments->map(fn ($p) => [
                    'id' => $p->id,
                    'status' => $p->status,
                    'amount' => (float) $p->amount,
                    'method' => $p->payment_method,
                ])->all(),
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function orderExportDoc(array $args): array
    {
        $id = (int) ($args['id'] ?? 0);
        $doc = (string) ($args['doc'] ?? 'invoice');
        if (! in_array($doc, ['invoice', 'delivery_note'], true)) {
            return ['ok' => false, 'error' => 'invalid_doc'];
        }
        $order = Order::query()->with(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses', 'payments'])->find($id);
        if (! $order) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if ($order->kind !== Order::KIND_ORDER) {
            return ['ok' => false, 'error' => 'not_an_order_kind'];
        }
        $locale = (string) ($args['locale'] ?? app()->getLocale());
        if (! in_array($locale, ['ca', 'es', 'en'], true)) {
            $locale = 'es';
        }
        app()->setLocale($locale);
        $view = $doc === 'invoice' ? 'pdf.invoice' : 'pdf.delivery_note';
        $html = view($view, ['order' => $order])->render();
        $filename = $doc.'-'.$order->id.'.html';
        $token = $this->downloads->put([
            'filename' => $filename,
            'content_type' => 'text/html; charset=UTF-8',
            'body' => $html,
        ]);

        return [
            'ok' => true,
            'summary' => $doc.' HTML for order '.$order->id,
            'data' => [['id' => $order->id, 'kind' => $order->kind, 'doc' => $doc, 'format' => 'html']],
            'download' => [
                'token' => $token,
                'filename' => $filename,
                'content_type' => 'text/html',
                'url' => '/api/v1/admin/chat/downloads/'.$token,
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function clientSearch(array $args): array
    {
        $query = Client::query();
        $q = trim((string) ($args['q'] ?? ''));
        if ($q !== '') {
            $term = '%'.$q.'%';
            $query->where(function ($w) use ($term) {
                $w->where('login_email', 'like', $term)->orWhere('identification', 'like', $term);
            });
        }
        $limit = $this->chatLimit($args);
        $total = (clone $query)->count();
        $rows = $query->orderBy('id')->limit($limit)->get(['id', 'type', 'identification', 'login_email', 'is_active'])
            ->map(fn (Client $c) => [
                'id' => $c->id,
                'type' => $c->type,
                'identification' => $c->identification,
                'login_email' => $c->login_email,
                'is_active' => (bool) $c->is_active,
            ])->all();

        return $this->okList('clients', $rows, $total);
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function clientGet(array $args): array
    {
        $c = Client::query()->find((int) ($args['id'] ?? 0), ['id', 'type', 'identification', 'login_email', 'is_active']);
        if (! $c) {
            return ['ok' => false, 'error' => 'not_found'];
        }

        return [
            'ok' => true,
            'summary' => 'client '.$c->id,
            'data' => [[
                'id' => $c->id,
                'type' => $c->type,
                'identification' => $c->identification,
                'login_email' => $c->login_email,
                'is_active' => (bool) $c->is_active,
            ]],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function statsGet(array $args): array
    {
        $which = (string) ($args['which'] ?? 'low_stock');
        if ($which === 'low_stock') {
            $rows = Product::query()->orderBy('stock')->limit($this->chatLimit($args))
                ->get(['id', 'code', 'stock'])
                ->map(fn (Product $p) => [
                    'id' => $p->id,
                    'code' => $p->code,
                    'name' => $p->name,
                    'stock' => (int) $p->stock,
                ])->all();

            return $this->okList('low_stock', $rows, count($rows));
        }

        return ['ok' => false, 'error' => 'unsupported_stat'];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<string, mixed>
     */
    private function entityGet(array $args): array
    {
        $resource = (string) ($args['resource'] ?? '');
        $limit = $this->chatLimit($args);
        $q = trim((string) ($args['q'] ?? ''));
        $id = (int) ($args['id'] ?? 0);

        return match ($resource) {
            'faqs' => $this->listSimple(Faq::query()->when($id > 0, fn ($q2) => $q2->where('id', $id))->when($q !== '', fn ($q2) => $q2->where('question_es', 'like', '%'.$q.'%')->orWhere('question_ca', 'like', '%'.$q.'%'))->limit($limit)->get(['id', 'is_active', 'question_es', 'question_ca']), 'faqs'),
            'reviews' => $this->listSimple(ProductReview::query()->when($id > 0, fn ($q2) => $q2->where('id', $id))->limit($limit)->get(['id', 'product_id', 'rating', 'status']), 'reviews'),
            'return-requests' => $this->listSimple(ReturnRequest::query()->when($id > 0, fn ($q2) => $q2->where('id', $id))->limit($limit)->get(['id', 'order_id', 'status']), 'return-requests'),
            'personalized-solutions' => $this->listSimple(PersonalizedSolution::query()->when($id > 0, fn ($q2) => $q2->where('id', $id))->limit($limit)->get(['id', 'email', 'status']), 'personalized-solutions'),
            'admins' => $this->listSimple(Admin::query()->when($id > 0, fn ($q2) => $q2->where('id', $id))->when($q !== '', fn ($q2) => $q2->where('username', 'like', '%'.$q.'%'))->limit($limit)->get(['id', 'username', 'is_active', 'last_login_at']), 'admins'),
            default => ['ok' => false, 'error' => 'unknown_resource'],
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, mixed>  $rows
     * @return array<string, mixed>
     */
    private function listSimple($rows, string $label): array
    {
        $data = $rows->map(fn ($r) => $this->compactRow($r->toArray()))->values()->all();

        return $this->okList($label, $data, count($data));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    private function okList(string $label, array $rows, int $total): array
    {
        return [
            'ok' => true,
            'summary' => $label.' total='.$total.' shown='.count($rows),
            'data' => $rows,
            'meta' => ['total' => $total, 'returned' => count($rows)],
        ];
    }

    /**
     * @param  array<string, mixed>  $args
     * @return array<int, array{column: string, op: string, value: mixed}>
     */
    private function filters(array $args): array
    {
        $filters = $args['filters'] ?? [];
        if (! is_array($filters)) {
            return [];
        }
        $out = [];
        foreach ($filters as $f) {
            if (! is_array($f) || empty($f['column'])) {
                continue;
            }
            $out[] = [
                'column' => (string) $f['column'],
                'op' => (string) ($f['op'] ?? '='),
                'value' => $f['value'] ?? null,
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $args
     */
    private function chatLimit(array $args): int
    {
        $max = (int) config('admin_chat.chat_row_limit', 10);
        $n = (int) ($args['limit'] ?? $args['per_page'] ?? $max);

        return max(1, min($max, $n > 0 ? $n : $max));
    }

    private function nullableString(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $v = trim($v);

        return $v === '' ? null : $v;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function compactRow(array $row): array
    {
        $out = [];
        foreach ($row as $k => $v) {
            if (is_string($v) && strlen($v) > 240) {
                $out[$k] = Str::limit($v, 240);
            } else {
                $out[$k] = $v;
            }
        }

        return $out;
    }
}
