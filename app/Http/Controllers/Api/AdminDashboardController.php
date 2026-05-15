<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
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

    /**
     * Resolve dashboard chart anchor year from request (optional).
     * Invalid or missing values use the current calendar year.
     */
    private function resolveChartCurrentYear(Request $request): int
    {
        $y = $request->query('year');
        if ($y === null || $y === '') {
            return (int) date('Y');
        }
        $yi = (int) $y;

        return ($yi >= 2000 && $yi <= 2100) ? $yi : (int) date('Y');
    }

    /** Optional month 1–12 from query; null means all months. */
    private function resolveChartMonthFilter(Request $request): ?int
    {
        $m = $request->query('month');
        if ($m === null || $m === '') {
            return null;
        }
        $mi = (int) $m;

        return ($mi >= 1 && $mi <= 12) ? $mi : null;
    }

    /** Distinct postal codes from order shipping addresses (for dashboard filter). */
    public function postalCodes(): JsonResponse
    {
        $codes = OrderAddress::query()
            ->where('type', OrderAddress::TYPE_SHIPPING)
            ->whereNotNull('postal_code')
            ->where('postal_code', '!=', '')
            ->distinct()
            ->orderBy('postal_code')
            ->pluck('postal_code');

        return response()->json(['success' => true, 'data' => $codes->values()->all()]);
    }

    /** Sales by month: anchor year Y vs Y−1 (Jan–Dec, or a single month if `month` is set). Optional `postal_code`, `year`, `month`. */
    public function salesByPeriod(Request $request): JsonResponse
    {
        $currentYear = $this->resolveChartCurrentYear($request);
        $previousYear = $currentYear - 1;
        $monthFilter = $this->resolveChartMonthFilter($request);
        $postalCode = $request->get('postal_code');
        $postalCode = is_string($postalCode) ? trim($postalCode) : '';

        $query = Order::query()
            ->where('kind', Order::KIND_ORDER)
            ->whereNotNull('order_date')
            ->where(function ($q) use ($currentYear, $previousYear) {
                $q->whereYear('order_date', $currentYear)
                    ->orWhereYear('order_date', $previousYear);
            });

        if ($monthFilter !== null) {
            $query->whereMonth('order_date', $monthFilter);
        }

        $this->applyPostalCodeFilter($query, $postalCode);

        $orders = $query->with('lines')->get();

        $byYearMonth = $orders->groupBy(fn ($o) => $o->order_date->format('Y-m'));

        $monthsRange = $monthFilter !== null ? [$monthFilter] : range(1, 12);

        $result = [];
        foreach ($monthsRange as $m) {
            $monthKey = sprintf('%02d', $m);
            $currKey = "{$currentYear}-{$monthKey}";
            $prevKey = "{$previousYear}-{$monthKey}";
            $currOrders = $byYearMonth->get($currKey, collect());
            $prevOrders = $byYearMonth->get($prevKey, collect());
            $totalCurr = $currOrders->sum(fn ($o) => $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price));
            $totalPrev = $prevOrders->sum(fn ($o) => $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price));
            $result[] = [
                'month' => $monthKey,
                'total_current' => round($totalCurr, 2),
                'count_current' => $currOrders->count(),
                'total_previous' => round($totalPrev, 2),
                'count_previous' => $prevOrders->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result,
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
        ]);
    }

    /** Top 10 products by quantity sold. Optional `postal_code`; `year` / `month` narrow to the same period as the sales chart when either is sent. */
    public function topProducts(Request $request): JsonResponse
    {
        $postalCode = $request->get('postal_code');
        $postalCode = is_string($postalCode) ? trim($postalCode) : '';

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

        if ($postalCode !== '') {
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
        $data = $lines->map(fn ($l) => [
            'product_id' => $l->product_id,
            'name' => $products->get($l->product_id)?->name ?? '',
            'quantity' => (int) $l->qty,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /** Products with lowest stock. */
    public function lowStock(): JsonResponse
    {
        $data = Product::query()
            ->where('is_active', true)
            ->with('translations')
            ->orderBy('stock')
            ->limit(10)
            ->get(['id', 'stock', 'code'])
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'stock' => $p->stock, 'code' => $p->code]);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
