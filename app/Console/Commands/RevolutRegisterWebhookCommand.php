<?php

namespace App\Console\Commands;

use App\Services\Payments\Revolut\RevolutClient;
use App\Services\Payments\Revolut\RevolutCredentials;
use Illuminate\Console\Command;
use Throwable;

/**
 * Idempotent webhook registration: list first; create only when URL is missing.
 * Never call repeatedly — Revolut rate-limits and caps at 10 webhooks.
 */
class RevolutRegisterWebhookCommand extends Command
{
    protected $signature = 'revolut:register-webhook
                            {--url= : Webhook URL (default: APP_URL + /api/v1/payments/webhooks/revolut)}
                            {--write-env= : Optional absolute path to .env to upsert REVOLUT_WEBHOOK_SECRET}
                            {--force-create : Create even if URL already registered (avoid; can spam)}';

    protected $description = 'Register Revolut Merchant webhook once (or reuse existing) and print signing_secret';

    /** @var list<string> */
    private const EVENTS = [
        'ORDER_COMPLETED',
        'ORDER_AUTHORISED',
        'ORDER_CANCELLED',
    ];

    public function handle(RevolutClient $client): int
    {
        if (! RevolutCredentials::areConfigured()) {
            $this->error('REVOLUT_MERCHANT_API_KEY is not set.');

            return self::FAILURE;
        }

        $url = $this->option('url');
        if (! is_string($url) || $url === '') {
            $url = rtrim((string) config('app.url'), '/').'/api/v1/payments/webhooks/revolut';
        }

        $this->info('Target URL: '.$url);
        $this->info('Merchant API: '.RevolutCredentials::merchantBaseUrl().' (sandbox='.(RevolutCredentials::sandbox() ? 'true' : 'false').')');

        try {
            $existing = $client->listWebhooks();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $match = null;
        foreach ($existing as $row) {
            if (rtrim((string) $row['url'], '/') === rtrim($url, '/')) {
                $match = $row;
                break;
            }
        }

        $signingSecret = null;
        $webhookId = null;

        if ($match !== null && ! $this->option('force-create')) {
            $webhookId = $match['id'];
            $this->warn('Webhook already registered (id='.$webhookId.'). Retrieving signing_secret (no create).');
            try {
                $detail = $client->retrieveWebhook($webhookId);
            } catch (Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
            $signingSecret = is_string($detail['signing_secret'] ?? null) ? $detail['signing_secret'] : null;
        } else {
            if ($match !== null && $this->option('force-create')) {
                $this->warn('--force-create set: creating another webhook for the same URL.');
            }
            try {
                $created = $client->createWebhook($url, self::EVENTS);
            } catch (Throwable $e) {
                $this->error($e->getMessage());

                return self::FAILURE;
            }
            $webhookId = is_string($created['id'] ?? null) ? $created['id'] : null;
            $signingSecret = $created['signing_secret'];
            $this->info('Created webhook id='.($webhookId ?? '?'));
        }

        if (! is_string($signingSecret) || $signingSecret === '') {
            $this->error('No signing_secret in Revolut response. Check Merchant API permissions.');

            return self::FAILURE;
        }

        $envPath = $this->option('write-env');
        if (is_string($envPath) && $envPath !== '') {
            if (! is_file($envPath) || ! is_writable($envPath)) {
                $this->error('Cannot write --write-env path: '.$envPath);

                return self::FAILURE;
            }
            $this->upsertEnvValue($envPath, 'REVOLUT_WEBHOOK_SECRET', $signingSecret);
            $this->info('Wrote REVOLUT_WEBHOOK_SECRET to '.$envPath);
        } else {
            $this->line('Set in .env (do not commit / do not post to Discord):');
            $this->line('REVOLUT_WEBHOOK_SECRET='.$signingSecret);
        }

        return self::SUCCESS;
    }

    private function upsertEnvValue(string $path, string $key, string $value): void
    {
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException('Failed to read '.$path);
        }
        $line = $key.'='.$value;
        $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $line, $contents, 1);
        } else {
            $contents = rtrim($contents)."\n\n".$line."\n";
        }
        if (file_put_contents($path, $contents) === false) {
            throw new \RuntimeException('Failed to write '.$path);
        }
    }
}
