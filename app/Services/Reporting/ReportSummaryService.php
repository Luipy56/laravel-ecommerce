<?php

namespace App\Services\Reporting;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Combined dashboard-style metrics for GET /reports/summary (admin vs client scope).
 */
class ReportSummaryService
{
    /** @param  Builder<Order>  $query */
    private function applyPostalCodeFilter(Builder $query, string $postalCode): void
    {
        if ($postalCode === '') {
            return;
        }
        $query->whereHas('addresses', function ($q) use ($postalCode) {
            $q->where('type', OrderAddress::TYPE_SHIPPING)->where('postal_code', $postalCode);
        });
    }

    private function resolveChartCurrentYear(Request $request): int
    {
        $y = $request->query('year');
        if ($y === null || $y === '') {
            return (int) date('Y');
        }
        $yi = (int) $y;

        return ($yi >= 2000 && $yi <= 2100) ? $yi : (int) date('Y');
    }

    private function resolveChartMonthFilter(Request $request): ?int
    {
        $m = $request->query('month');
        if ($m === null || $m === '') {
            return null;
        }
        $mi = (int) $m;

        return ($mi >= 1 && $mi <= 12) ? $mi : null;
    }

    /**
     * @return array{sales_by_period: array<int, array<string, mixed>>, current_year: int, previous_year: int, summary: array<string, float|int>}
     */
    public function salesBlock(Request $request, ?int $clientId, bool $allowPostalFilter): array
    {
        $currentYear = $this->resolveChartCurrentYear($request);
        $previousYear = $currentYear - 1;
        $monthFilter = $this->resolveChartMonthFilter($request);
        $postalCode = $allowPostalFilter ? trim((string) $request->get('postal_code', '')) : '';

        $query = Order::query()
            ->where('kind', Order::KIND_ORDER)
            ->whereNotNull('order_date')
            ->where(function ($q) use ($currentYear, $previousYear) {
                $q->whereYear('order_date', $currentYear)
                    ->orWhereYear('order_date', $previousYear);
            });

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }

        if ($monthFilter !== null) {
            $query->whereMonth('order_date', $monthFilter);
        }

        if ($allowPostalFilter) {
            $this->applyPostalCodeFilter($query, $postalCode);
        }

        $orders = $query->with('lines')->get();

        $byYearMonth = $orders->groupBy(fn ($o) => $o->order_date->format('Y-m'));

        $monthsRange = $monthFilter !== null ? [$monthFilter] : range(1, 12);

        $result = [];
        $sumCurr = 0.0;
        $countCurrOrders = 0;
        foreach ($monthsRange as $m) {
            $monthKey = sprintf('%02d', $m);
            $currKey = "{$currentYear}-{$monthKey}";
            $prevKey = "{$previousYear}-{$monthKey}";
            $currOrders = $byYearMonth->get($currKey, collect());
            $prevOrders = $byYearMonth->get($prevKey, collect());
            $totalCurr = $currOrders->sum(fn ($o) => $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price));
            $totalPrev = $prevOrders->sum(fn ($o) => $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price));
            $sumCurr += $totalCurr;
            $countCurrOrders += $currOrders->count();
            $result[] = [
                'month' => $monthKey,
                'total_current' => round($totalCurr, 2),
                'count_current' => $currOrders->count(),
                'total_previous' => round($totalPrev, 2),
                'count_previous' => $prevOrders->count(),
            ];
        }

        return [
            'sales_by_period' => $result,
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
            'summary' => [
                'total_sales_current_year' => round($sumCurr, 2),
                'order_count_current_year' => $countCurrOrders,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function topProductsBlock(Request $request, ?int $clientId, bool $allowPostalFilter): array
    {
        $postalCode = $allowPostalFilter ? trim((string) $request->get('postal_code', '')) : '';

        $yearRaw = $request->query('year');
        $monthRaw = $request->query('month');
        $applyChartPeriod = ($yearRaw !== null && $yearRaw !== '') || ($monthRaw !== null && $monthRaw !== '');

        $currentYear = $this->resolveChartCurrentYear($request);
        $previousYear = $currentYear - 1;
        $monthFilter = $this->resolveChartMonthFilter($request);

        $linesQuery = DB::table('order_lines')
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->where('orders.kind', Order::KIND_ORDER)
            ->whereNotNull('order_lines.product_id');

        if ($clientId !== null) {
            $linesQuery->where('orders.client_id', $clientId);
        }

        if ($allowPostalFilter && $postalCode !== '') {
            $linesQuery->join('order_addresses', function ($join) {
                $join->on('order_addresses.order_id', '=', 'orders.id')
                    ->where('order_addresses.type', '=', OrderAddress::TYPE_SHIPPING);
            })->where('order_addresses.postal_code', '=', $postalCode);
        }

        if ($applyChartPeriod) {
            $linesQuery->where(function ($q) use ($currentYear, $previousYear) {
                $q->whereYear('orders.order_date', $currentYear)
                    ->orWhereYear('orders.order_date', $previousYear);
            });
            if ($monthFilter !== null) {
                $linesQuery->whereMonth('orders.order_date', $monthFilter);
            }
        }

        $lines = $linesQuery
            ->selectRaw('order_lines.product_id, SUM(order_lines.quantity) as qty')
            ->groupBy('order_lines.product_id')
            ->orderByDesc('qty')
            ->limit(10)
            ->get();
        $productIds = $lines->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        return $lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'name' => $products->get($l->product_id)?->name ?? '',
            'quantity' => (int) $l->qty,
        ])->values()->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function lowStockBlock(): array
    {
        return Product::query()
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'stock', 'code'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock, 'code' => $p->code])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForAdmin(Request $request): array
    {
        $sales = $this->salesBlock($request, null, true);

        return [
            'scope' => 'shop',
            'sales_by_period' => $sales['sales_by_period'],
            'current_year' => $sales['current_year'],
            'previous_year' => $sales['previous_year'],
            'summary' => $sales['summary'],
            'top_products' => $this->topProductsBlock($request, null, true),
            'low_stock' => $this->lowStockBlock(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildForClient(Request $request, Client $client): array
    {
        $sales = $this->salesBlock($request, $client->id, false);

        return [
            'scope' => 'client',
            'sales_by_period' => $sales['sales_by_period'],
            'current_year' => $sales['current_year'],
            'previous_year' => $sales['previous_year'],
            'summary' => $sales['summary'],
            'top_products' => $this->topProductsBlock($request, $client->id, false),
            'low_stock' => [],
        ];
    }
}
