<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminDataExplorerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminDataExplorerController extends Controller
{
    public function __construct(
        protected AdminDataExplorerService $explorer,
    ) {}

    public function schema(): JsonResponse
    {
        $payload = $this->explorer->schema();

        return response()->json([
            'success' => true,
            'data' => $payload,
        ]);
    }

    public function query(Request $request): JsonResponse
    {
        $validated = $this->validateExplorerPayload($request);
        $filters = $validated['filters'] ?? [];

        try {
            $this->explorer->configureSessionTimeout();

            $builder = $this->explorer->buildFilteredQuery(
                $validated['table'],
                $validated['q'] ?? null,
                $filters,
                $validated['date_column'] ?? null,
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null,
            );

            $paginator = $this->explorer->paginateQuery(
                $builder,
                $validated['table'],
                $validated['sort_column'] ?? null,
                $validated['sort_direction'] ?? 'desc',
                (int) ($validated['page'] ?? 1),
                (int) ($validated['per_page'] ?? config('admin_data_explorer.max_per_page', 100)),
            );

            return response()->json([
                'success' => true,
                'data' => collect($paginator->items())->map(fn ($row) => (array) $row)->values()->all(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function export(Request $request): StreamedResponse|JsonResponse
    {
        $validated = $this->validateExplorerPayload($request, true);
        $filters = $validated['filters'] ?? [];
        $maxRows = (int) config('admin_data_explorer.max_export_rows', 5000);

        try {
            $this->explorer->configureSessionTimeout();

            $rows = $this->explorer->exportRows(
                $validated['table'],
                $validated['q'] ?? null,
                $filters,
                $validated['date_column'] ?? null,
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null,
                $validated['sort_column'] ?? null,
                $validated['sort_direction'] ?? 'desc',
                $maxRows,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        $columns = $this->explorer->resolvedColumns($validated['table']);
        $filename = 'data-explorer-'.$validated['table'].'-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($rows, $columns) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
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
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function aggregate(Request $request): JsonResponse
    {
        $tables = $this->explorer->allowedTables();

        $validated = $request->validate([
            'table' => ['required', 'string', Rule::in($tables)],
            'metric' => ['required', 'string', Rule::in(['count', 'sum', 'avg'])],
            'value_column' => 'nullable|string',
            'group_by' => ['required', 'string'],
            'q' => 'nullable|string|max:200',
            'filters' => 'nullable|array',
            'filters.*.column' => 'required_with:filters|string',
            'filters.*.op' => 'required_with:filters|string|in:=,!=,<,>,<=,>=,like',
            'filters.*.value' => 'nullable',
            'date_column' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        $filters = $validated['filters'] ?? [];
        $maxGroups = min(500, max(1, (int) config('admin_data_explorer.max_aggregate_groups', 200)));

        try {
            $this->explorer->configureSessionTimeout();

            $rows = $this->explorer->aggregate(
                $validated['table'],
                $validated['metric'],
                $validated['value_column'] ?? null,
                $validated['group_by'],
                $validated['q'] ?? null,
                $filters,
                $validated['date_column'] ?? null,
                $validated['date_from'] ?? null,
                $validated['date_to'] ?? null,
                $maxGroups,
            );

            return response()->json([
                'success' => true,
                'data' => $rows->map(fn ($r) => [
                    'group_value' => $r->group_value,
                    'aggregate_value' => $r->aggregate_value !== null ? (float) $r->aggregate_value : null,
                ])->values()->all(),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateExplorerPayload(Request $request, bool $export = false): array
    {
        $tables = $this->explorer->allowedTables();
        $maxPerPage = (int) config('admin_data_explorer.max_per_page', 100);

        $rules = [
            'table' => ['required', 'string', Rule::in($tables)],
            'q' => 'nullable|string|max:200',
            'filters' => 'nullable|array',
            'filters.*.column' => 'required_with:filters|string',
            'filters.*.op' => 'required_with:filters|string|in:=,!=,<,>,<=,>=,like',
            'filters.*.value' => 'nullable',
            'date_column' => 'nullable|string',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'sort_column' => 'nullable|string',
            'sort_direction' => 'nullable|string|in:asc,desc,ASC,DESC',
        ];

        if (! $export) {
            $rules['page'] = 'nullable|integer|min:1';
            $rules['per_page'] = 'nullable|integer|min:1|max:'.$maxPerPage;
        }

        return $request->validate($rules);
    }
}
