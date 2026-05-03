<?php

namespace App\Http\Controllers\Api;

use App\Events\OrderInstallationQuoteRequested;
use App\Events\OrderPlacedPaymentPending;
use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\Payment;
use App\Models\ShopSetting;
use App\Support\InstallationAutoPricing;
use App\Services\Payments\PaymentCheckoutService;
use App\Support\MailLocale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class OrderController extends Controller
{
    public function checkout(Request $request, PaymentCheckoutService $paymentCheckoutService): JsonResponse
    {
        $client = $request->user();
        $cart = Order::where('client_id', $client->id)->where('kind', Order::KIND_CART)->with('lines')->first();
        if (! $cart || $cart->lines->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Cart is empty.'], 422);
        }

        $cart->loadMissing('lines');
        $merchandiseSubtotal = $cart->lines_subtotal;
        $installPricing = InstallationAutoPricing::fromMerged(ShopSetting::allMerged());
        $awaitingInstallationQuote = $cart->installation_requested
            && $installPricing->quoteRequired($merchandiseSubtotal);
        $automaticInstallationFee = $installPricing->automaticFee($merchandiseSubtotal);

        $checkoutMethodIn = implode(',', PaymentCheckoutService::checkoutMethodKeysFromConfig());

        // Temporary workaround for demo stacks: CHECKOUT_DEMO_SKIP_PAYMENT + checkout_demo_skip_payment on the request.
        $demoSkipActive = ! $awaitingInstallationQuote
            && $request->boolean('checkout_demo_skip_payment')
            && PaymentCheckoutService::checkoutDemoSkipPaymentAllowed();

        $rules = [
            'checkout_demo_skip_payment' => ['sometimes', 'boolean'],
            'payment_method' => ($cart->installation_requested && $awaitingInstallationQuote)
                ? ['nullable', 'string', 'in:'.$checkoutMethodIn]
                : ($demoSkipActive
                    ? ['prohibited']
                    : ['required', 'string', 'in:'.$checkoutMethodIn]),
            'shipping_street' => ['required', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_province' => ['nullable', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'string', 'regex:/^\d{1,20}$/'],
            'shipping_note' => ['nullable', 'string'],
            'installation_street' => ['nullable', 'string', 'max:255'],
            'installation_city' => ['nullable', 'string', 'max:100'],
            'installation_postal_code' => ['nullable', 'string', 'regex:/^\d{0,20}$/'],
            'installation_note' => ['nullable', 'string'],
        ];

        if ($cart->installation_requested) {
            $rules['installation_street'] = ['required', 'string', 'max:255'];
            $rules['installation_city'] = ['required', 'string', 'max:100'];
            $rules['installation_postal_code'] = ['required', 'string', 'regex:/^\d{1,20}$/'];
        }

        $validated = $request->validate($rules);

        $resolvedPaymentMethod = ($cart->installation_requested && $awaitingInstallationQuote)
            ? ($validated['payment_method'] ?? null)
            : ($demoSkipActive
                ? Payment::METHOD_CHECKOUT_DEMO_SKIP
                : $validated['payment_method']);

        if (! $awaitingInstallationQuote && ! $demoSkipActive && ! PaymentCheckoutService::isPaymentMethodAvailable($resolvedPaymentMethod)) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.method_unavailable'),
                'code' => 'payment_method_not_configured',
            ], 422);
        }

        DB::transaction(function () use ($cart, $validated, $awaitingInstallationQuote, $automaticInstallationFee, $resolvedPaymentMethod) {
            $installationPrice = null;
            $installationStatus = null;
            if ($cart->installation_requested) {
                if ($awaitingInstallationQuote) {
                    $installationStatus = Order::INSTALLATION_PENDING;
                } else {
                    $installationPrice = $automaticInstallationFee;
                    $installationStatus = Order::INSTALLATION_PRICED;
                }
            }

            $needsAwaitingPayment = $resolvedPaymentMethod === Payment::METHOD_PAYPAL;
            $initialStatus = $awaitingInstallationQuote
                ? Order::STATUS_AWAITING_INSTALLATION_PRICE
                : ($needsAwaitingPayment ? Order::STATUS_AWAITING_PAYMENT : Order::STATUS_PENDING);
            $cart->update([
                'kind' => Order::KIND_ORDER,
                'status' => $initialStatus,
                'order_date' => now(),
                'shipping_price' => ShopSetting::shippingFlatEur(),
                'installation_status' => $installationStatus,
                'installation_price' => $installationPrice,
            ]);

            $cart->addresses()->create([
                'type' => OrderAddress::TYPE_SHIPPING,
                'street' => $validated['shipping_street'],
                'city' => $validated['shipping_city'],
                'province' => $validated['shipping_province'] ?? null,
                'postal_code' => $validated['shipping_postal_code'] ?? null,
                'note' => $validated['shipping_note'] ?? null,
            ]);

            if ($cart->installation_requested) {
                $cart->addresses()->create([
                    'type' => OrderAddress::TYPE_INSTALLATION,
                    'street' => $validated['installation_street'] ?? '',
                    'city' => $validated['installation_city'] ?? '',
                    'province' => null,
                    'postal_code' => $validated['installation_postal_code'] ?? null,
                    'note' => $validated['installation_note'] ?? null,
                ]);
            } elseif (! empty($validated['installation_street']) || ! empty($validated['installation_city'])) {
                $cart->addresses()->create([
                    'type' => OrderAddress::TYPE_INSTALLATION,
                    'street' => $validated['installation_street'] ?? '',
                    'city' => $validated['installation_city'] ?? '',
                    'province' => null,
                    'postal_code' => $validated['installation_postal_code'] ?? null,
                    'note' => $validated['installation_note'] ?? null,
                ]);
            }

            if (! $awaitingInstallationQuote) {
                $cart->load('lines');
                $total = $cart->grand_total;
                $cart->payments()->create([
                    'amount' => $total,
                    'payment_method' => $resolvedPaymentMethod,
                    'status' => Payment::STATUS_PENDING,
                    'currency' => 'EUR',
                ]);
            }
        });

        $cart->refresh()->load(['lines.product', 'lines.pack', 'addresses', 'payments']);

        $paymentCheckout = null;
        $paymentError = null;
        if (! $awaitingInstallationQuote) {
            $payment = $cart->payments()->latest()->first();
            if ($demoSkipActive && $payment) {
                try {
                    $paymentCheckoutService->markCheckoutDemoSkipSuccess($payment);
                } catch (Throwable $e) {
                    report($e);
                    $paymentError = $e->getMessage();
                }
                $paymentCheckout = ['gateway' => 'checkout_demo_skip', 'demo_skip' => true];
            } elseif ($payment && PaymentCheckoutService::shouldSimulateCheckoutForPayment($payment)) {
                try {
                    $paymentCheckoutService->simulateSuccess($payment);
                } catch (Throwable $e) {
                    report($e);
                    $paymentError = $e->getMessage();
                }
                $paymentCheckout = ['gateway' => 'simulated', 'simulated' => true];
            } elseif ($payment) {
                try {
                    $paymentCheckout = $paymentCheckoutService->start($payment);
                } catch (PaymentProviderNotConfiguredException $e) {
                    $paymentError = __('shop.payment.method_unavailable');
                } catch (Throwable $e) {
                    report($e);
                    $paymentError = $this->checkoutPaymentStartErrorMessage($payment, $e);
                }
            }
        }

        $cart->refresh()->load('payments');

        $mailLocale = MailLocale::resolve($request->getPreferredLanguage(config('app.available_locales', ['ca', 'es', 'en'])));
        if ($awaitingInstallationQuote) {
            OrderInstallationQuoteRequested::dispatch(
                $cart->fresh(['client', 'lines.product', 'lines.pack', 'addresses']),
                $mailLocale
            );
        } elseif (! $cart->hasSuccessfulPayment()) {
            OrderPlacedPaymentPending::dispatch(
                $cart->fresh(['client', 'lines.product', 'lines.pack', 'addresses', 'payments']),
                $mailLocale
            );
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $cart->id,
                'status' => $cart->status,
                'order_date' => $cart->order_date?->toIso8601String(),
                'awaiting_installation_quote' => $awaitingInstallationQuote,
                'installation_requested' => (bool) $cart->installation_requested,
                'installation_status' => $cart->installation_status,
                'installation_price' => $cart->installation_price !== null ? (float) $cart->installation_price : null,
                'lines_subtotal' => $cart->lines_subtotal,
                'shipping_flat_eur' => $cart->shipping_price !== null
                    ? (float) $cart->shipping_price
                    : ShopSetting::shippingFlatEur(),
                'grand_total' => $cart->grand_total,
                'has_payment' => $cart->hasSuccessfulPayment(),
                'payment_checkout' => $paymentCheckout,
                'payment_error' => $paymentError,
            ],
        ], 201);
    }

    public function pay(Request $request, Order $order, PaymentCheckoutService $paymentCheckoutService): JsonResponse
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        if ($order->hasSuccessfulPayment()) {
            return response()->json(['success' => false, 'message' => __('shop.order.already_paid')], 422);
        }
        if (! $order->clientMayPay()) {
            return response()->json(['success' => false, 'message' => __('shop.order.cannot_pay_yet')], 422);
        }

        $checkoutMethodIn = implode(',', PaymentCheckoutService::checkoutMethodKeysFromConfig());

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:'.$checkoutMethodIn],
        ]);

        if (! PaymentCheckoutService::isPaymentMethodAvailable($validated['payment_method'])) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.method_unavailable'),
                'code' => 'payment_method_not_configured',
            ], 422);
        }

        $order->load('lines');
        $total = $order->grand_total;

        DB::transaction(function () use ($order, $validated, $total) {
            $order->payments()->create([
                'amount' => $total,
                'payment_method' => $validated['payment_method'],
                'status' => Payment::STATUS_PENDING,
                'currency' => 'EUR',
            ]);
            if ($validated['payment_method'] === Payment::METHOD_PAYPAL) {
                $order->update(['status' => Order::STATUS_AWAITING_PAYMENT]);
            }
        });

        $payment = $order->payments()->latest()->first();

        if (PaymentCheckoutService::shouldSimulateCheckoutForPayment($payment)) {
            try {
                $paymentCheckoutService->simulateSuccess($payment);
            } catch (Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
            $paymentCheckout = ['gateway' => 'simulated', 'simulated' => true];
        } else {
            try {
                $paymentCheckout = $paymentCheckoutService->start($payment);
            } catch (PaymentProviderNotConfiguredException $e) {
                return $this->jsonWhenPaymentStartFailed($payment, $e);
            } catch (Throwable $e) {
                report($e);

                return $this->jsonWhenPaymentStartFailed($payment, $e);
            }
        }

        $order->refresh()->load(['lines', 'payments']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'grand_total' => $order->grand_total,
                'has_payment' => $order->hasSuccessfulPayment(),
                'payment_checkout' => $paymentCheckout,
            ],
        ]);
    }

    /** Drop installation request so the client can pay product total only (e.g. after admin rejection). */
    public function waiveInstallation(Request $request, Order $order): JsonResponse
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        if ($order->hasSuccessfulPayment()) {
            return response()->json(['success' => false, 'message' => __('shop.order.already_paid')], 422);
        }
        if ($order->status !== Order::STATUS_AWAITING_INSTALLATION_PRICE) {
            return response()->json(['success' => false, 'message' => __('shop.order.waive_installation_invalid')], 422);
        }

        $order->update([
            'installation_requested' => false,
            'installation_status' => null,
            'installation_price' => null,
            'status' => Order::STATUS_PENDING,
        ]);

        $order->load('lines');

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'status' => $order->status,
                'installation_requested' => false,
                'grand_total' => $order->grand_total,
            ],
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('client_id', $request->user()->id)
            ->where('kind', Order::KIND_ORDER)
            ->with(['lines.product', 'lines.pack'])
            ->orderByDesc('order_date')
            ->paginate(15);

        $data = $orders->getCollection()->map(function (Order $o) {
            $o->loadMissing('lines');

            return [
                'id' => $o->id,
                'status' => $o->status,
                'order_date' => $o->order_date?->toIso8601String(),
                'shipping_date' => $o->shipping_date?->toIso8601String(),
                'installation_requested' => (bool) $o->installation_requested,
                'installation_status' => $o->installation_status,
                'installation_price' => $o->installation_price !== null ? (float) $o->installation_price : null,
                'lines_subtotal' => $o->lines_subtotal,
                'shipping_flat_eur' => $o->shipping_price !== null
                    ? (float) $o->shipping_price
                    : ShopSetting::shippingFlatEur(),
                'grand_total' => $o->grand_total,
                'has_payment' => $o->hasSuccessfulPayment(),
                'can_pay' => $o->clientMayPay(),
            ];
        });

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

        $payMethods = PaymentCheckoutService::paymentMethodsAvailability();
        $anyPayMethod = $payMethods['card'] || $payMethods['paypal'];

        $lines = $order->lines->map(fn ($l) => [
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
                'status_timeline' => $order->buildClientStatusTimeline(),
                'order_date' => $order->order_date?->toIso8601String(),
                'shipping_date' => $order->shipping_date?->toIso8601String(),
                'installation_requested' => (bool) $order->installation_requested,
                'installation_status' => $order->installation_status,
                'installation_price' => $order->installation_price !== null ? (float) $order->installation_price : null,
                'lines' => $lines,
                'addresses' => $order->addresses,
                'lines_subtotal' => $order->lines_subtotal,
                'shipping_flat_eur' => $order->shipping_price !== null
                    ? (float) $order->shipping_price
                    : ShopSetting::shippingFlatEur(),
                'grand_total' => $order->grand_total,
                'has_payment' => $order->hasSuccessfulPayment(),
                'can_pay' => $order->clientMayPay(),
                'payment_methods_available' => [
                    'card' => $payMethods['card'],
                    'paypal' => $payMethods['paypal'],
                ],
                'payments_simulated' => $payMethods['simulated'],
                'paypal_missing_credentials' => PaymentCheckoutService::paypalMissingCredentialsForStorefront(),
                'stripe_missing_credentials' => PaymentCheckoutService::stripeMissingCredentialsForStorefront(),
                'local_checkout_needs_debug' => app()->environment('local') && ! $payMethods['simulated'] && ! $anyPayMethod,
                'payments' => $order->payments->map(fn (Payment $p) => [
                    'id' => $p->id,
                    'status' => $p->status,
                    'gateway' => $p->gateway,
                    'amount' => (float) $p->amount,
                    'currency' => $p->currency,
                    'payment_method' => $p->payment_method,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                    'failure_code' => $p->failure_code,
                ]),
            ],
        ]);
    }

    public function invoice(Request $request, Order $order): Response
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        if (! $order->hasSuccessfulPayment()) {
            abort(403);
        }
        $allowed = config('app.available_locales', ['ca', 'es', 'en']);
        $locale = $request->query('locale');
        if (! in_array($locale, $allowed, true)) {
            $pref = $request->header('Accept-Language', '');
            $locale = (preg_match('/^(ca|es|en)([-_]|$)/i', $pref, $m) ? strtolower($m[1]) : null) ?? config('app.locale');
        }
        if (! in_array($locale, $allowed, true)) {
            $locale = config('app.locale');
        }
        app()->setLocale($locale);
        $order->load(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses']);

        $html = view('pdf.invoice', ['order' => $order])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="invoice-'.$order->id.'.html"',
        ]);
    }

    public function deliveryNote(Request $request, Order $order): Response
    {
        if ($order->client_id !== $request->user()->id || $order->kind !== Order::KIND_ORDER) {
            abort(404);
        }
        if (! $order->hasSuccessfulPayment()) {
            abort(403);
        }
        $allowed = config('app.available_locales', ['ca', 'es', 'en']);
        $locale = $request->query('locale');
        if (! in_array($locale, $allowed, true)) {
            $pref = $request->header('Accept-Language', '');
            $locale = (preg_match('/^(ca|es|en)([-_]|$)/i', $pref, $m) ? strtolower($m[1]) : null) ?? config('app.locale');
        }
        if (! in_array($locale, $allowed, true)) {
            $locale = config('app.locale');
        }
        app()->setLocale($locale);
        $order->load(['lines.product', 'lines.pack', 'addresses', 'client.contacts', 'client.addresses']);

        $html = view('pdf.delivery_note', ['order' => $order])->render();

        return response($html, 200, [
            'Content-Type' => 'text/html',
            'Content-Disposition' => 'inline; filename="delivery-note-'.$order->id.'.html"',
        ]);
    }

    private function paymentStartExceptionMatchesPayPalOAuth(Throwable $e): bool
    {
        $m = strtolower($e->getMessage());

        return str_contains($m, 'paypal oauth')
            || str_contains($m, 'client authentication failed')
            || str_contains($m, 'invalid_client');
    }

    private function jsonWhenPaymentStartFailed(Payment $payment, Throwable $e): JsonResponse
    {
        if ($e instanceof PaymentProviderNotConfiguredException) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.method_unavailable'),
                'code' => 'payment_method_not_configured',
            ], 422);
        }

        if ($payment->payment_method === Payment::METHOD_PAYPAL) {
            if (str_starts_with($e->getMessage(), 'PAYPAL_START_ERROR|')) {
                $msg = __('shop.payment.paypal_credentials_format_invalid');
                if (config('app.debug')) {
                    $msg .= ' ('.$e->getMessage().')';
                }

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'code' => 'paypal_credentials_invalid',
                ], 422);
            }
            if ($this->paymentStartExceptionMatchesPayPalOAuth($e)) {
                $msg = __('shop.payment.paypal_oauth_failed');
                if (config('app.debug')) {
                    $msg .= ' '.$e->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'message' => $msg,
                    'code' => 'paypal_oauth_failed',
                ], 422);
            }
        }

        return response()->json([
            'success' => false,
            'message' => config('app.debug') ? $e->getMessage() : __('shop.payment.provider_error'),
        ], 422);
    }

    private function checkoutPaymentStartErrorMessage(Payment $payment, Throwable $e): string
    {
        if ($payment->payment_method === Payment::METHOD_PAYPAL) {
            if (str_starts_with($e->getMessage(), 'PAYPAL_START_ERROR|')) {
                $msg = __('shop.payment.paypal_credentials_format_invalid');
                if (config('app.debug')) {
                    $msg .= ' ('.$e->getMessage().')';
                }

                return $msg;
            }
            if ($this->paymentStartExceptionMatchesPayPalOAuth($e)) {
                $msg = __('shop.payment.paypal_oauth_failed');
                if (config('app.debug')) {
                    $msg .= ' '.$e->getMessage();
                }

                return $msg;
            }
        }

        return config('app.debug') ? $e->getMessage() : __('shop.payment.provider_error');
    }
}
