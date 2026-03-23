<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
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

    /** Sales by month: current year and previous year (12 months Jan–Dec each). Optional filter by shipping postal_code. */
    public function salesByPeriod(Request $request): JsonResponse
    {
        $currentYear = (int) date('Y');
        $previousYear = $currentYear - 1;
        $postalCode = $request->get('postal_code');
        $postalCode = is_string($postalCode) ? trim($postalCode) : '';

        $query = Order::query()
            ->where('kind', Order::KIND_ORDER)
            ->whereNotNull('order_date')
            ->where(function ($q) use ($currentYear, $previousYear) {
                $q->whereYear('order_date', $currentYear)
                    ->orWhereYear('order_date', $previousYear);
            });

        if ($postalCode !== '') {
            $query->whereHas('addresses', function ($q) use ($postalCode) {
                $q->where('type', OrderAddress::TYPE_SHIPPING)->where('postal_code', $postalCode);
            });
        }

        $orders = $query->with('lines')->get();

        $byYearMonth = $orders->groupBy(fn ($o) => $o->order_date->format('Y-m'));

        $result = [];
        for ($m = 1; $m <= 12; $m++) {
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

    /** Top 10 products by quantity sold. Optional filter by shipping postal_code. */
    public function topProducts(Request $request): JsonResponse
    {
        $postalCode = $request->get('postal_code');
        $postalCode = is_string($postalCode) ? trim($postalCode) : '';

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
            'name' => $products->get($l->product_id)?->name ?? '-',
            'quantity' => (int) $l->qty,
        ]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /** Products with lowest stock. */
    public function lowStock(): JsonResponse
    {
        $data = Product::query()->where('is_active', true)->orderBy('stock')->limit(10)->get(['id', 'name', 'stock', 'code']);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
