<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderLine;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:card,paypal,bizum'],
            'shipping_street' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_province' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_note' => ['nullable', 'string'],
            'installation_street' => ['nullable', 'string', 'max:255'],
            'installation_city' => ['nullable', 'string', 'max:100'],
            'installation_postal_code' => ['nullable', 'string', 'max:20'],
            'installation_note' => ['nullable', 'string'],
        ]);

        $client = $request->user();
        $cart = Order::where('client_id', $client->id)->where('kind', Order::KIND_CART)->with('lines')->first();
        if (! $cart || $cart->lines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        DB::transaction(function () use ($cart, $validated, $client) {
            $cart->update([
                'kind' => Order::KIND_ORDER,
                'status' => Order::STATUS_PENDING,
                'order_date' => now(),
            ]);

            $cart->addresses()->create([
                'type' => OrderAddress::TYPE_SHIPPING,
                'street' => $validated['shipping_street'],
                'city' => $validated['shipping_city'],
                'province' => $validated['shipping_province'] ?? null,
                'postal_code' => $validated['shipping_postal_code'] ?? null,
                'note' => $validated['shipping_note'] ?? null,
            ]);

            if (! empty($validated['installation_street']) || ! empty($validated['installation_city'])) {
                $cart->addresses()->create([
                    'type' => OrderAddress::TYPE_INSTALLATION,
                    'street' => $validated['installation_street'] ?? '',
                    'city' => $validated['installation_city'] ?? '',
                    'province' => null,
                    'postal_code' => $validated['installation_postal_code'] ?? null,
                    'note' => $validated['installation_note'] ?? null,
                ]);
            }

            $total = $cart->lines->sum(fn ($l) => $l->line_total);
            $cart->payments()->create([
                'amount' => $total,
                'payment_method' => $validated['payment_method'],
                'gateway_reference' => null,
                'paid_at' => now(),
            ]);
        });

        $cart->load(['lines.product', 'lines.pack', 'addresses', 'payments']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'status' => $cart->status,
                'order_date' => $cart->order_date?->toIso8601String(),
                'total' => $cart->lines->sum(fn ($l) => $l->line_total),
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('client_id', $request->user()->id)
            ->where('kind', Order::KIND_ORDER)
            ->with(['lines.product', 'lines.pack'])
            ->orderByDesc('order_date')
            ->paginate(15);

        $data = $orders->getCollection()->map(fn (Order $o) => [
            'id' => $o->id,
            'status' => $o->status,
            'order_date' => $o->order_date?->toIso8601String(),
            'shipping_date' => $o->shipping_date?->toIso8601String(),
            'total' => $o->lines->sum(fn ($l) => $l->line_total),
        ]);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        $order->load(['lines.product', 'lines.pack', 'addresses', 'payments']);

        $lines = $order->lines->map(fn (OrderLine $l) => [
            'id' => $l->id,
            'product_id' => $l->product_id,
            'pack_id' => $l->pack_id,
            'product' => $l->product ? ['id' => $l->product->id, 'name' => $l->product->name] : null,
            'pack' => $l->pack ? ['id' => $l->pack->id, 'name' => $l->pack->name] : null,
            'quantity' => $l->quantity,
            'unit_price' => (float) $l->unit_price,
            'line_total' => (float) $l->line_total,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'order_date' => $order->order_date?->toIso8601String(),
                'shipping_date' => $order->shipping_date?->toIso8601String(),
                'lines' => $lines,
                'addresses' => $order->addresses,
                'total' => $order->lines->sum(fn ($l) => $l->line_total),
            ],
        ]);
    }

    public function invoice(Request $request, Order $order): Response
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        $locale = $request->query('locale');
        if (! in_array($locale, ['ca', 'es'], true)) {
            $pref = $request->header('Accept-Language', '');
            $locale = (preg_match('/^(ca|es)([-_]|$)/i', $pref, $m) ? $m[1] : null) ?? config('app.locale');
        }
        app()->setLocale($locale);
        $order->load(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses']);

        // Simple PDF via HTML response; can be replaced with DomPDF or similar
        $html = view('pdf.invoice', ['order' => $order])->render();
        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="invoice-' . $order->id . '.html"',
        ]);
    }
}
