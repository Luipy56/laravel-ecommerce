<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AdminDataExplorerService
{
    public function allowedTables(): array
    {
        return array_keys(config('admin_data_explorer.tables', []));
    }

    /**
     * @return array{tables: array<int, array<string, mixed>>, limits: array<string, int>}
     */
    public function schema(): array
    {
        $tables = [];
        foreach (config('admin_data_explorer.tables', []) as $name => $meta) {
            if (! Schema::hasTable($name)) {
                continue;
            }
            $columns = $this->resolvedColumns($name);
            if ($columns === []) {
                continue;
            }
            $searchColumns = array_values(array_intersect($meta['search_columns'] ?? [], $columns));
            $dateColumns = array_values(array_intersect($meta['date_columns'] ?? [], $columns));

            $tables[] = [
                'name' => $name,
                'label_key' => $meta['label_key'] ?? 'admin.data_explorer.tables.generic',
                'columns' => $columns,
                'search_columns' => $searchColumns,
                'date_columns' => $dateColumns,
            ];
        }

        return [
            'tables' => $tables,
            'limits' => [
                'max_per_page' => (int) config('admin_data_explorer.max_per_page', 100),
                'max_export_rows' => (int) config('admin_data_explorer.max_export_rows', 5000),
                'max_aggregate_groups' => (int) config('admin_data_explorer.max_aggregate_groups', 200),
                'query_timeout_seconds' => (int) config('admin_data_explorer.query_timeout_seconds', 25),
            ],
        ];
    }

    /**
     * @param  array<int, array{column: string, op: string, value: mixed}>  $filters
     */
    public function buildFilteredQuery(string $table, ?string $q, array $filters, ?string $dateColumn, ?string $dateFrom, ?string $dateTo): Builder
    {
        $this->assertTableAllowed($table);

        $columns = $this->resolvedColumns($table);

        $query = DB::table($table)->select($columns);

        $searchColumns = $this->searchColumnsForTable($table);
        $q = $q !== null ? trim($q) : '';
        if ($q !== '' && $searchColumns !== []) {
            $pattern = '%'.$this->escapeLikePattern($q).'%';
            $query->where(function (Builder $w) use ($searchColumns, $pattern) {
                foreach ($searchColumns as $col) {
                    $w->orWhere($col, 'LIKE', $pattern);
                }
            });
        }

        foreach ($filters as $filter) {
            $column = $filter['column'] ?? '';
            $op = strtolower((string) ($filter['op'] ?? '='));
            $value = $filter['value'] ?? null;

            if (! is_string($column) || ! in_array($column, $columns, true)) {
                throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_column'));
            }
            $allowedOps = ['=', '!=', '<', '>', '<=', '>=', 'like'];
            if (! in_array($op, $allowedOps, true)) {
                throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_operator'));
            }

            if ($op === 'like') {
                $likeVal = '%'.$this->escapeLikePattern((string) $value).'%';
                $query->where($column, 'LIKE', $likeVal);
            } else {
                $query->where($column, $op, $value);
            }
        }

        if ($dateColumn !== null && $dateColumn !== '') {
            if (! in_array($dateColumn, $this->dateColumnsForTable($table), true)) {
                throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_date_column'));
            }
            if ($dateFrom !== null && $dateFrom !== '') {
                $query->whereDate($dateColumn, '>=', $dateFrom);
            }
            if ($dateTo !== null && $dateTo !== '') {
                $query->whereDate($dateColumn, '<=', $dateTo);
            }
        }

        return $query;
    }

    /**
     * @param  array<int, array{column: string, op: string, value: mixed}>  $filters
     */
    public function paginateQuery(
        Builder $query,
        string $table,
        ?string $sortColumn,
        string $sortDirection,
        int $page,
        int $perPage,
    ): LengthAwarePaginator {
        $columns = $this->resolvedColumns($table);
        $sortColumn = $sortColumn !== null && $sortColumn !== '' && in_array($sortColumn, $columns, true)
            ? $sortColumn
            : (in_array('id', $columns, true) ? 'id' : $columns[0]);
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param  array<int, array{column: string, op: string, value: mixed}>  $filters
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function exportRows(
        string $table,
        ?string $q,
        array $filters,
        ?string $dateColumn,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortColumn,
        string $sortDirection,
        int $maxRows,
    ) {
        $query = $this->buildFilteredQuery($table, $q, $filters, $dateColumn, $dateFrom, $dateTo);

        $columns = $this->resolvedColumns($table);
        $sortColumn = $sortColumn !== null && $sortColumn !== '' && in_array($sortColumn, $columns, true)
            ? $sortColumn
            : (in_array('id', $columns, true) ? 'id' : $columns[0]);
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortColumn, $sortDirection)->limit($maxRows);

        return $query->get();
    }

    /**
     * @param  array<int, array{column: string, op: string, value: mixed}>  $filters
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function aggregate(
        string $table,
        string $metric,
        ?string $valueColumn,
        string $groupByColumn,
        ?string $q,
        array $filters,
        ?string $dateColumn,
        ?string $dateFrom,
        ?string $dateTo,
        int $maxGroups,
    ) {
        $this->assertTableAllowed($table);
        $columns = $this->resolvedColumns($table);

        if (! in_array($groupByColumn, $columns, true)) {
            throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_group_by'));
        }

        $grammar = DB::connection()->getQueryGrammar();
        $wrappedGroup = $grammar->wrap($groupByColumn);

        $inner = $this->buildFilteredQuery($table, $q, $filters, $dateColumn, $dateFrom, $dateTo);

        $metric = strtolower($metric);
        if ($metric === 'count') {
            return DB::query()->fromSub($inner, 'explorer_inner')
                ->selectRaw($wrappedGroup.' as group_value, COUNT(*) as aggregate_value')
                ->groupBy($groupByColumn)
                ->orderByDesc('aggregate_value')
                ->limit($maxGroups)
                ->get();
        }

        if ($valueColumn === null || $valueColumn === '' || ! in_array($valueColumn, $columns, true)) {
            throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_value_column'));
        }
        if ($valueColumn === $groupByColumn) {
            throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_value_column'));
        }

        $wrappedValue = $grammar->wrap($valueColumn);

        if ($metric === 'sum') {
            $aggExpr = 'SUM('.$wrappedValue.')';
        } elseif ($metric === 'avg') {
            $aggExpr = 'AVG('.$wrappedValue.')';
        } else {
            throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_metric'));
        }

        return DB::query()->fromSub($inner, 'explorer_inner')
            ->selectRaw($wrappedGroup.' as group_value, '.$aggExpr.' as aggregate_value')
            ->groupBy($groupByColumn)
            ->orderByDesc('aggregate_value')
            ->limit($maxGroups)
            ->get();
    }

    public function configureSessionTimeout(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $seconds = max(1, (int) config('admin_data_explorer.query_timeout_seconds', 25));

        if ($driver === 'pgsql') {
            DB::statement("SET LOCAL statement_timeout = '{$seconds}s'");

            return;
        }

        if ($driver === 'mariadb') {
            $this->trySetSessionStatement('SET SESSION max_statement_time = '.(int) $seconds);

            return;
        }

        if ($driver !== 'mysql') {
            return;
        }

        $versionRow = DB::selectOne('SELECT VERSION() as v');
        $sql = $this->mysqlFamilyTimeoutStatement((string) ($versionRow->v ?? ''), $seconds);
        if ($sql !== null) {
            $this->trySetSessionStatement($sql);
        }
    }

    /**
     * Resolve a MySQL-protocol SET SESSION statement for statement timeouts.
     * MariaDB uses max_statement_time (seconds); MySQL 8.0.3+ uses max_execution_time (milliseconds).
     * Older MySQL releases have no equivalent session guard — returns null (caller skips SET).
     */
    protected function mysqlFamilyTimeoutStatement(string $versionString, int $seconds): ?string
    {
        $seconds = max(1, $seconds);
        $lower = strtolower($versionString);

        if (str_contains($lower, 'mariadb')) {
            return 'SET SESSION max_statement_time = '.(int) $seconds;
        }

        if (! preg_match('/^(\d+)\.(\d+)\.(\d+)/', $versionString, $m)) {
            return null;
        }

        $major = (int) $m[1];
        $minor = (int) $m[2];
        $patch = (int) $m[3];

        $mysql803Plus = $major > 8
            || ($major === 8 && $minor > 0)
            || ($major === 8 && $minor === 0 && $patch >= 3);

        if ($mysql803Plus) {
            return 'SET SESSION max_execution_time = '.($seconds * 1000);
        }

        return null;
    }

    protected function trySetSessionStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (QueryException $e) {
            if ($this->isUnsupportedSessionTimeoutVariableError($e)) {
                return;
            }

            throw $e;
        }
    }

    protected function isUnsupportedSessionTimeoutVariableError(QueryException $e): bool
    {
        $msg = strtolower($e->getMessage());

        return str_contains($msg, 'unknown system variable')
            || str_contains($msg, 'unsupported')
            || str_contains($msg, '1193'); // ER_BAD_SYSTEM_VARIABLE in MySQL
    }

    /**
     * @return list<string>
     */
    public function resolvedColumns(string $table): array
    {
        $configured = config("admin_data_explorer.tables.$table.columns");
        if (! is_array($configured)) {
            return [];
        }
        if (! Schema::hasTable($table)) {
            return [];
        }
        $existing = Schema::getColumnListing($table);

        return array_values(array_intersect($configured, $existing));
    }

    /**
     * @return list<string>
     */
    protected function searchColumnsForTable(string $table): array
    {
        $meta = config("admin_data_explorer.tables.$table");
        $configured = is_array($meta) ? ($meta['search_columns'] ?? []) : [];
        $resolved = $this->resolvedColumns($table);

        return array_values(array_intersect($configured, $resolved));
    }

    /**
     * @return list<string>
     */
    protected function dateColumnsForTable(string $table): array
    {
        $meta = config("admin_data_explorer.tables.$table");
        $configured = is_array($meta) ? ($meta['date_columns'] ?? []) : [];
        $resolved = $this->resolvedColumns($table);

        return array_values(array_intersect($configured, $resolved));
    }

    protected function assertTableAllowed(string $table): void
    {
        if (! in_array($table, $this->allowedTables(), true) || ! Schema::hasTable($table)) {
            throw new InvalidArgumentException(__('admin.data_explorer.errors.invalid_table'));
        }
    }

    protected function escapeLikePattern(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
