<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchasedProductsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $clientId = $request->user()->id;

        $query = OrderLine::query()
            ->select('order_lines.*')
            ->join('orders', 'orders.id', '=', 'order_lines.order_id')
            ->where('orders.client_id', $clientId)
            ->where('orders.kind', Order::KIND_ORDER)
            ->where(function ($q) {
                $q->whereNotNull('order_lines.product_id')
                    ->orWhereNotNull('order_lines.pack_id');
            });

        if (! empty($validated['date_from'])) {
            $query->whereDate('orders.order_date', '>=', $validated['date_from']);
        }
        if (! empty($validated['date_to'])) {
            $query->whereDate('orders.order_date', '<=', $validated['date_to']);
        }

        $query->orderByDesc('orders.order_date')
            ->orderByDesc('order_lines.id');

        $paginator = $query->with([
            'order',
            'product.images',
            'pack.images',
        ])->paginate(15);

        $data = $paginator->getCollection()->map(fn (OrderLine $line) => $this->formatLine($line))->values();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formatLine(OrderLine $line): array
    {
        $order = $line->order;
        $orderId = $order?->id;
        $orderDate = $order?->order_date?->toIso8601String();

        $base = [
            'line_id' => $line->id,
            'quantity' => (int) $line->quantity,
            'unit_price' => (float) $line->unit_price,
            'line_total' => (float) $line->line_total,
            'order_id' => $orderId,
            'order_date' => $orderDate,
        ];

        if ($line->product_id !== null && $line->product) {
            $img = $line->product->images->first();

            return array_merge($base, [
                'kind' => 'product',
                'product_id' => $line->product_id,
                'pack_id' => null,
                'name' => $line->product->name,
                'image_url' => $img?->url,
            ]);
        }

        if ($line->pack_id !== null && $line->pack) {
            $img = $line->pack->images->first();

            return array_merge($base, [
                'kind' => 'pack',
                'product_id' => null,
                'pack_id' => $line->pack_id,
                'name' => $line->pack->name,
                'image_url' => $img?->url,
            ]);
        }

        return array_merge($base, [
            'kind' => $line->product_id !== null ? 'product' : 'pack',
            'product_id' => $line->product_id,
            'pack_id' => $line->pack_id,
            'name' => '',
            'image_url' => null,
        ]);
    }
}
