<?php

namespace App\Http\Controllers\Api;

use App\Events\InstallationPriceWasAssigned;
use App\Events\OrderShipped;
use App\Http\Controllers\Controller;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ShopSetting;
use App\Models\OrderLine;
use App\Support\MailLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;

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
                $term = '%'.$term.'%';
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
        if ($request->boolean('installation_pending')) {
            $query->where('kind', Order::KIND_ORDER)
                ->where('installation_requested', true)
                ->where('installation_status', Order::INSTALLATION_PENDING);
        }
        if ($request->filled('period')) {
            $period = (string) $request->input('period');
            $from = match ($period) {
                'week'  => now()->subWeek(),
                'month' => now()->subMonth(),
                'year'  => now()->subYear(),
                default => null,
            };
            if ($from !== null) {
                $query->where('created_at', '>=', $from);
            }
        }

        $perPage = max(1, min(100, (int) $request->get('per_page', 20)));
        $orders = $query->paginate($perPage);

        $data = $orders->getCollection()->map(function (Order $o) {
            $o->loadMissing('lines');
            $total = $o->grand_total;

            return [
                'id' => $o->id,
                'kind' => $o->kind,
                'status' => $o->status,
                'client_id' => $o->client_id,
                'client_login_email' => $o->relationLoaded('client') && $o->client ? $o->client->login_email : null,
                'order_date' => $o->order_date?->toIso8601String(),
                'shipping_date' => $o->shipping_date?->toIso8601String(),
                'installation_requested' => (bool) $o->installation_requested,
                'installation_status' => $o->installation_status,
                'installation_price' => $o->installation_price !== null ? (float) $o->installation_price : null,
                'total' => round($total, 2),
                'lines_count' => $o->lines->count(),
                'created_at' => $o->created_at?->toIso8601String(),
                'updated_at' => $o->updated_at?->toIso8601String(),
            ];
        })->values()->all();

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'client:id,login_email,identification',
            'lines.product:id,name,code',
            'lines.product.images',
            'lines.pack:id,name,contains_keys',
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
                'pack' => $l->pack ? ['id' => $l->pack->id, 'name' => $l->pack->name, 'contains_keys' => (bool) $l->pack->contains_keys] : null,
                'keys_all_same' => (bool) ($l->keys_all_same ?? false),
                'image_url' => $imageUrl,
                'quantity' => $l->quantity,
                'unit_price' => (float) $l->unit_price,
                'offer' => (float) ($l->offer ?? 0),
                'extra_keys_qty' => (int) ($l->extra_keys_qty ?? 0),
                'extra_key_unit_price' => $l->extra_key_unit_price !== null ? (float) $l->extra_key_unit_price : null,
                'line_total' => (float) $l->line_total,
            ];
        })->values()->all();

        $order->loadMissing('lines');
        $total = $order->grand_total;

        $decryptionError = $order->client?->hasDecryptionErrors() ?? false;

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
                'installation_requested' => (bool) $order->installation_requested,
                'installation_status' => $order->installation_status,
                'installation_price' => $order->installation_price !== null ? (float) $order->installation_price : null,
                'lines_subtotal' => $order->lines_subtotal,
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
                    'currency' => $p->currency,
                    'status' => $p->status,
                    'gateway' => $p->gateway,
                    'payment_method' => $p->payment_method,
                    'gateway_reference' => $p->gateway_reference,
                    'failure_code' => $p->failure_code,
                    'failure_message' => $p->failure_message,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                ])->values()->all(),
                'total' => round($total, 2),
                'created_at' => $order->created_at?->toIso8601String(),
                'updated_at' => $order->updated_at?->toIso8601String(),
                '_decryption_error' => $decryptionError,
            ],
        ]);
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $previousStatus = $order->status;

        $rules = [
            'shipping_date' => ['nullable', 'date'],
            'installation_price' => ['nullable', 'numeric', 'min:0'],
            'installation_status' => ['nullable', 'string', 'in:'.implode(',', [
                Order::INSTALLATION_PENDING,
                Order::INSTALLATION_PRICED,
                Order::INSTALLATION_REJECTED,
            ])],
        ];
        if ($order->kind === Order::KIND_ORDER) {
            $rules['status'] = ['required', 'string', 'in:'.implode(',', [
                Order::STATUS_PENDING,
                Order::STATUS_AWAITING_PAYMENT,
                Order::STATUS_AWAITING_INSTALLATION_PRICE,
                Order::STATUS_IN_TRANSIT,
                Order::STATUS_SENT,
                Order::STATUS_INSTALLATION_PENDING,
                Order::STATUS_INSTALLATION_CONFIRMED,
                Order::STATUS_RETURNED,
            ])];
        }
        $validated = $request->validate($rules);

        $previousInstallationStatus = $order->installation_status;

        if ($order->kind === Order::KIND_ORDER && isset($validated['status'])) {
            $order->status = $validated['status'];
        }
        if (array_key_exists('shipping_date', $validated)) {
            $order->shipping_date = $validated['shipping_date'] ? \Carbon\Carbon::parse($validated['shipping_date']) : null;
        }
        if ($order->kind === Order::KIND_ORDER && $order->shipping_price === null) {
            $order->shipping_price = ShopSetting::shippingFlatEur();
        }

        if ($order->installation_requested && $order->kind === Order::KIND_ORDER) {
            if (array_key_exists('installation_price', $validated)) {
                $order->installation_price = $validated['installation_price'] !== null && $validated['installation_price'] !== ''
                    ? $validated['installation_price']
                    : null;
            }
            if (array_key_exists('installation_status', $validated) && $validated['installation_status'] !== null) {
                $order->installation_status = $validated['installation_status'];
            }

            if ($order->installation_status === Order::INSTALLATION_REJECTED) {
                $order->installation_price = null;
                if ($order->status === Order::STATUS_AWAITING_INSTALLATION_PRICE) {
                    $order->status = Order::STATUS_PENDING;
                }
            } elseif ($order->installation_price !== null) {
                $order->installation_status = Order::INSTALLATION_PRICED;
                if ($order->status === Order::STATUS_AWAITING_INSTALLATION_PRICE) {
                    $order->status = Order::STATUS_PENDING;
                }
            } elseif ($order->installation_price === null && $order->installation_status === Order::INSTALLATION_PRICED) {
                $order->installation_status = Order::INSTALLATION_PENDING;
            }
        }

        $order->save();

        $shouldDispatchInstallationMail = $order->installation_requested
            && $order->installation_status === Order::INSTALLATION_PRICED
            && $order->installation_price !== null
            && $previousInstallationStatus !== Order::INSTALLATION_PRICED;

        if ($shouldDispatchInstallationMail) {
            InstallationPriceWasAssigned::dispatch($order->fresh(['client', 'lines']));
        }

        $shippedStates = [Order::STATUS_IN_TRANSIT, Order::STATUS_SENT];
        if ($order->kind === Order::KIND_ORDER
            && in_array($order->status, $shippedStates, true)
            && ! in_array($previousStatus, $shippedStates, true)) {
            OrderShipped::dispatch($order->fresh(['client', 'lines', 'addresses']));
        }

        $order->loadMissing('lines');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'kind' => $order->kind,
                'status' => $order->status,
                'order_date' => $order->order_date?->toIso8601String(),
                'shipping_date' => $order->shipping_date?->toIso8601String(),
                'shipping_price' => $order->shipping_price !== null ? (float) $order->shipping_price : null,
                'installation_requested' => (bool) $order->installation_requested,
                'installation_status' => $order->installation_status,
                'installation_price' => $order->installation_price !== null ? (float) $order->installation_price : null,
                'total' => round($order->grand_total, 2),
            ],
        ]);
    }

    /**
     * Send the “order in transit” transactional email to the customer (manual action from admin).
     * Only allowed when the order is a real order and status is in_transit.
     */
    public function sendInTransitCustomerMail(Request $request, Order $order): JsonResponse
    {
        if ($order->kind !== Order::KIND_ORDER || $order->status !== Order::STATUS_IN_TRANSIT) {
            return response()->json([
                'success' => false,
                'message' => __('admin.orders.notify_in_transit_mail_invalid_status'),
            ], 422);
        }

        $validated = $request->validate([
            'delivery_eta' => ['required', 'string', 'in:today,few_days,unknown'],
        ]);

        $estimateKey = match ($validated['delivery_eta']) {
            'unknown' => 'soon',
            default => $validated['delivery_eta'],
        };

        $order->loadMissing(['client', 'lines', 'addresses']);
        $client = $order->client;
        if (! $client?->login_email) {
            return response()->json([
                'success' => false,
                'message' => __('admin.orders.notify_in_transit_mail_no_client_email'),
            ], 422);
        }

        $locale = MailLocale::resolve();
        Mail::to($client->login_email)->locale($locale)->send(new OrderShippedMail($order, $estimateKey));

        return response()->json(['success' => true]);
    }

    public function invoice(Request $request, Order $order): Response
    {
        if ($order->kind !== Order::KIND_ORDER) {
            abort(404);
        }

        $locale = $this->resolveDocLocale($request);
        app()->setLocale($locale);
        $order->load(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses', 'payments']);

        $html = view('pdf.invoice', ['order' => $order])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="invoice-'.$order->id.'.html"',
        ]);
    }

    public function deliveryNote(Request $request, Order $order): Response
    {
        if ($order->kind !== Order::KIND_ORDER) {
            abort(404);
        }

        $locale = $this->resolveDocLocale($request);
        app()->setLocale($locale);
        $order->load(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses']);

        $html = view('pdf.delivery_note', ['order' => $order])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="delivery-note-'.$order->id.'.html"',
        ]);
    }

    private function resolveDocLocale(Request $request): string
    {
        $allowed = config('app.available_locales', ['ca', 'es', 'en']);
        $locale = $request->query('locale');
        if (in_array($locale, $allowed, true)) {
            return $locale;
        }
        $pref = $request->header('Accept-Language', '');
        if (preg_match('/^(ca|es|en)([-_]|$)/i', $pref, $m)) {
            return strtolower($m[1]);
        }

        return config('app.locale');
    }

}
