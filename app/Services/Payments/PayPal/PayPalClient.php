<?php

namespace App\Services\Payments\PayPal;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/** Minimal PayPal REST v2 client (OAuth + checkout orders). */
class PayPalClient
{
    public static function baseUrl(): string
    {
        $mode = config('services.paypal.mode', 'sandbox');

        return $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    public function assertConfigured(): void
    {
        if (! self::envCredentialsPresent()) {
            throw new RuntimeException('PayPal is not configured (PAYPAL_CLIENT_ID, PAYPAL_SECRET).');
        }
    }

    public static function envCredentialsPresent(): bool
    {
        $id = trim((string) config('services.paypal.client_id', ''));
        $secret = trim((string) config('services.paypal.secret', ''));

        return $id !== '' && $secret !== '';
    }

    /**
     * Alias for {@see envCredentialsPresent()}. Some call sites used this name; keep it so
     * payment config and checkout availability never fatally error on a missing method.
     */
    public static function envCredentialsLookValid(): bool
    {
        return self::envCredentialsPresent();
    }

    public function getAccessToken(): string
    {
        $this->assertConfigured();
        $clientId = (string) config('services.paypal.client_id');
        $secret = (string) config('services.paypal.secret');
        $mode = (string) config('services.paypal.mode', 'sandbox');
        $cacheKey = 'paypal.access_token.'.hash('sha256', $clientId.'|'.$mode);

        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $secret)
            ->timeout(30)
            ->post(self::baseUrl().'/v1/oauth2/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            $message = 'PayPal OAuth failed.';
            if (config('app.debug')) {
                $json = $response->json();
                if (is_array($json) && isset($json['error_description']) && is_string($json['error_description'])) {
                    $message .= ' '.$json['error_description'];
                } else {
                    $message .= ' HTTP '.$response->status().'.';
                }
            }

            throw new RuntimeException($message);
        }

        $token = $response->json('access_token');
        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal OAuth returned no access token.');
        }

        $expiresIn = (int) $response->json('expires_in', 32400);
        $ttlSeconds = max(120, $expiresIn - 120);
        Cache::put($cacheKey, $token, now()->addSeconds($ttlSeconds));

        return $token;
    }

    /** @return array<string, mixed> */
    public function getOrder(string $paypalOrderId): array
    {
        $response = $this->authorizedJson('GET', '/v2/checkout/orders/'.$paypalOrderId);

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function createOrder(string $value, string $currencyCode, string $referenceId, string $customId, string $invoiceId): array
    {
        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $referenceId,
                    'custom_id' => $customId,
                    'invoice_id' => $invoiceId,
                    'amount' => [
                        'currency_code' => strtoupper($currencyCode),
                        'value' => $value,
                    ],
                ],
            ],
            'application_context' => [
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'PAY_NOW',
            ],
        ];

        $response = $this->authorizedJson('POST', '/v2/checkout/orders', $payload);

        if (! $response->successful()) {
            throw new RuntimeException('PayPal create order failed.');
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new RuntimeException('PayPal create order returned invalid JSON.');
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $order  PayPal checkout order JSON (v2).
     */
    public static function approvalUrlFromOrderResponse(array $order): ?string
    {
        foreach ($order['links'] ?? [] as $link) {
            if (! is_array($link)) {
                continue;
            }
            if (($link['rel'] ?? '') === 'approve' && isset($link['href']) && is_string($link['href']) && $link['href'] !== '') {
                return $link['href'];
            }
        }

        return null;
    }

    /**
     * Capture a PayPal order. On duplicate capture, fetches the order and returns it if already COMPLETED.
     *
     * @return array<string, mixed>
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $response = $this->authorizedJson('POST', '/v2/checkout/orders/'.$paypalOrderId.'/capture', new \stdClass);

        $data = $response->json();
        if (! is_array($data)) {
            $data = [];
        }

        if ($response->successful() && ($data['status'] ?? '') === 'COMPLETED') {
            return $data;
        }

        if ($response->successful()) {
            throw new RuntimeException('PayPal capture did not complete.');
        }

        $order = $this->getOrder($paypalOrderId);
        if (($order['status'] ?? '') === 'COMPLETED') {
            return $order;
        }

        throw new RuntimeException('PayPal capture failed.');
    }

    /**
     * @param  array<string, mixed>|\stdClass  $json
     */
    private function authorizedJson(string $method, string $path, array|\stdClass $json = []): Response
    {
        $token = $this->getAccessToken();
        $url = self::baseUrl().$path;

        $pending = Http::withToken($token)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'Prefer' => 'return=representation',
            ])
            ->timeout(60);

        $body = $json instanceof \stdClass ? $json : ($json === [] ? new \stdClass : $json);

        return match (strtoupper($method)) {
            'GET' => $pending->get($url),
            'POST' => $pending->post($url, $body),
            default => throw new RuntimeException('Unsupported HTTP method: '.$method),
        };
    }
}
