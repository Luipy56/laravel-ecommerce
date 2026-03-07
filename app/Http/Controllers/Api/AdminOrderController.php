<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Admin CRUD for orders (and carts). List with filters, show full detail, update status/dates.
 * No store (orders created via checkout); no destroy.
 */
class AdminOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::query()
            ->with(['client:id,login_email', 'lines'])
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $term = trim($request->string('search'));
            if (is_numeric($term)) {
                $query->where('id', (int) $term);
            } else {
                $term = '%' . $term . '%';
                $query->whereHas('client', fn ($q) => $q->where('login_email', 'like', $term));
            }
        }
        if ($request->filled('kind')) {
            $kind = (string) $request->input('kind');
            if (in_array($kind, [Order::KIND_CART, Order::KIND_ORDER, Order::KIND_LIKE], true)) {
                $query->where('kind', $kind);
            }
        }
        if ($request->filled('status')) {
            $query->where('kind', Order::KIND_ORDER)->where('status', (string) $request->input('status'));
        }

        $orders = $query->get();

        $data = $orders->map(function (Order $o) {
            $total = $o->lines->sum(fn ($l) => (float) $l->line_total);
            return [
                'id' => $o->id,
                'kind' => $o->kind,
                'status' => $o->status,
                'client_id' => $o->client_id,
                'client_login_email' => $o->relationLoaded('client') && $o->client ? $o->client->login_email : null,
                'order_date' => $o->order_date?->toIso8601String(),
                'shipping_date' => $o->shipping_date?->toIso8601String(),
                'total' => round($total, 2),
                'lines_count' => $o->lines->count(),
                'created_at' => $o->created_at?->toIso8601String(),
                'updated_at' => $o->updated_at?->toIso8601String(),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'client:id,login_email,identification',
            'lines.product:id,name,code',
            'lines.product.images',
            'lines.pack:id,name',
            'lines.pack.images',
            'addresses',
            'payments',
        ]);

        $lines = $order->lines->map(function (OrderLine $l) {
            $imageUrl = null;
            if ($l->product && $l->product->relationLoaded('images') && $l->product->images->isNotEmpty()) {
                $imageUrl = $l->product->images->first()->url;
            } elseif ($l->pack && $l->pack->relationLoaded('images') && $l->pack->images->isNotEmpty()) {
                $imageUrl = $l->pack->images->first()->url;
            }
            return [
                'id' => $l->id,
                'product_id' => $l->product_id,
                'pack_id' => $l->pack_id,
                'product' => $l->product ? ['id' => $l->product->id, 'name' => $l->product->name, 'code' => $l->product->code] : null,
                'pack' => $l->pack ? ['id' => $l->pack->id, 'name' => $l->pack->name] : null,
                'image_url' => $imageUrl,
                'quantity' => $l->quantity,
                'unit_price' => (float) $l->unit_price,
                'offer' => (float) ($l->offer ?? 0),
                'is_installation_requested' => (bool) $l->is_installation_requested,
                'installation_price' => $l->installation_price !== null ? (float) $l->installation_price : null,
                'extra_keys_qty' => (int) ($l->extra_keys_qty ?? 0),
                'extra_key_unit_price' => $l->extra_key_unit_price !== null ? (float) $l->extra_key_unit_price : null,
                'line_total' => (float) $l->line_total,
            ];
        })->values()->all();

        $total = $order->lines->sum(fn ($l) => (float) $l->line_total);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'kind' => $order->kind,
                'status' => $order->status,
                'client' => $order->client ? [
                    'id' => $order->client->id,
                    'login_email' => $order->client->login_email,
                    'identification' => $order->client->identification,
                ] : null,
                'order_date' => $order->order_date?->toIso8601String(),
                'shipping_date' => $order->shipping_date?->toIso8601String(),
                'shipping_price' => $order->shipping_price !== null ? (float) $order->shipping_price : null,
                'lines' => $lines,
                'addresses' => $order->addresses->map(fn ($a) => [
                    'id' => $a->id,
                    'type' => $a->type,
                    'street' => $a->street,
                    'city' => $a->city,
                    'province' => $a->province,
                    'postal_code' => $a->postal_code,
                    'note' => $a->note,
                ])->values()->all(),
                'payments' => $order->payments->map(fn ($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'payment_method' => $p->payment_method,
                    'gateway_reference' => $p->gateway_reference,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                ])->values()->all(),
                'total' => round($total, 2),
                'created_at' => $order->created_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $rules = [
            'shipping_date' => ['nullable', 'date'],
            'shipping_price' => ['nullable', 'numeric', 'min:0'],
        ];
        if ($order->kind === Order::KIND_ORDER) {
            $rules['status'] = ['required', 'string', 'in:' . implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_IN_TRANSIT,
                Order::STATUS_SENT,
                Order::STATUS_INSTALLATION_PENDING,
                Order::STATUS_INSTALLATION_CONFIRMED,
            ])];
        }
        $validated = $request->validate($rules);

        if ($order->kind === Order::KIND_ORDER && isset($validated['status'])) {
            $order->status = $validated['status'];
        }
        if (array_key_exists('shipping_date', $validated)) {
            $order->shipping_date = $validated['shipping_date'] ? \Carbon\Carbon::parse($validated['shipping_date']) : null;
        }
        if (array_key_exists('shipping_price', $validated)) {
            $order->shipping_price = $validated['shipping_price'] !== null && $validated['shipping_price'] !== '' ? $validated['shipping_price'] : null;
        }
        $order->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'kind' => $order->kind,
                'status' => $order->status,
                'order_date' => $order->order_date?->toIso8601String(),
                'shipping_date' => $order->shipping_date?->toIso8601String(),
                'shipping_price' => $order->shipping_price !== null ? (float) $order->shipping_price : null,
            ],
        ]);
    }
}
