<?php

namespace App\Services\Payments\Revolut;

use App\Exceptions\PaymentProviderNotConfiguredException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RevolutClient
{
    public function assertConfigured(): void
    {
        if (! RevolutCredentials::areConfigured()) {
            throw new PaymentProviderNotConfiguredException(
                'Revolut is not configured (REVOLUT_MERCHANT_API_KEY).',
            );
        }
    }

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function createOrder(array $body): array
    {
        $response = $this->request('post', '/api/orders', $body);
        if (! $response->successful()) {
            throw new RuntimeException('Revolut order creation failed: '.$response->body());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Revolut order creation returned invalid JSON.');
        }

        return $json;
    }

    /**
     * @return array<string, mixed>
     */
    public function retrieveOrder(string $orderId): array
    {
        $response = $this->request('get', '/api/orders/'.rawurlencode($orderId));
        if (! $response->successful()) {
            throw new RuntimeException('Revolut order retrieve failed: '.$response->body());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Revolut order retrieve returned invalid JSON.');
        }

        return $json;
    }

    /**
     * @return list<array{id: string, url: string, events?: list<string>}>
     */
    public function listWebhooks(): array
    {
        $response = $this->request('get', '/api/webhooks');
        if (! $response->successful()) {
            throw new RuntimeException('Revolut list webhooks failed: '.$response->body());
        }

        $json = $response->json();
        $webhooks = is_array($json) ? ($json['webhooks'] ?? $json) : [];
        if (! is_array($webhooks)) {
            return [];
        }

        $out = [];
        foreach ($webhooks as $row) {
            if (! is_array($row) || ! is_string($row['id'] ?? null) || ! is_string($row['url'] ?? null)) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  list<string>  $events
     * @return array{id: string, url: string, events: list<string>, signing_secret: string}
     */
    public function createWebhook(string $url, array $events): array
    {
        $response = $this->request('post', '/api/webhooks', [
            'url' => $url,
            'events' => array_values($events),
        ]);
        if (! $response->successful()) {
            throw new RuntimeException('Revolut create webhook failed: '.$response->body());
        }

        $json = $response->json();
        if (! is_array($json) || ! is_string($json['signing_secret'] ?? null)) {
            throw new RuntimeException('Revolut create webhook response missing signing_secret.');
        }

        return $json;
    }

    /**
     * @return array{id: string, url: string, events?: list<string>, signing_secret?: string}
     */
    public function retrieveWebhook(string $webhookId): array
    {
        $response = $this->request('get', '/api/webhooks/'.rawurlencode($webhookId));
        if (! $response->successful()) {
            throw new RuntimeException('Revolut retrieve webhook failed: '.$response->body());
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RuntimeException('Revolut retrieve webhook returned invalid JSON.');
        }

        return $json;
    }

    /**
     * @param  array<string, mixed>|null  $body
     */
    private function request(string $method, string $path, ?array $body = null): Response
    {
        $this->assertConfigured();

        /** @var string $apiKey */
        $apiKey = config('services.revolut.api_key');

        $pending = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Revolut-Api-Version' => RevolutCredentials::apiVersion(),
            'Accept' => 'application/json',
        ])->timeout(30);

        $url = rtrim(RevolutCredentials::merchantBaseUrl(), '/').$path;

        return match (strtolower($method)) {
            'get' => $pending->get($url),
            'post' => $pending->asJson()->post($url, $body ?? []),
            default => throw new RuntimeException('Unsupported Revolut HTTP method: '.$method),
        };
    }
}
