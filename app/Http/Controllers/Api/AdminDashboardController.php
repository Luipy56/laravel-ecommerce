<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    /** Sales by month for the last 12 months. */
    public function salesByPeriod(): JsonResponse
    {
        $byMonth = Order::query()
            ->where('kind', Order::KIND_ORDER)
            ->whereNotNull('order_date')
            ->with('lines')
            ->get()
            ->groupBy(fn ($o) => $o->order_date->format('Y-m'));
        $result = [];
        foreach ($byMonth as $month => $orders) {
            $total = 0;
            foreach ($orders as $o) {
                $total += $o->lines->sum(fn ($l) => $l->quantity * $l->unit_price);
            }
            $result[] = ['month' => $month, 'count' => $orders->count(), 'total' => round($total, 2)];
        }
        usort($result, fn ($a, $b) => strcmp($a['month'], $b['month']));
        $result = array_slice($result, -12, 12);
        return response()->json(['success' => true, 'data' => $result]);
    }

    /** Top 10 products by quantity sold. */
    public function topProducts(): JsonResponse
    {
        $lines = DB::table('order_lines')
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->where('orders.kind', Order::KIND_ORDER)
            ->whereNotNull('order_lines.product_id')
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
