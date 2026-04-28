<?php

namespace App\Console\Commands;

use App\Services\Payments\PayPal\PayPalClient;
use Illuminate\Console\Command;
use Throwable;

class PayPalTestCredentialsCommand extends Command
{
    protected $signature = 'paypal:test-credentials';

    protected $description = 'Verify PayPal REST credentials (OAuth against sandbox or live API base URL)';

    public function handle(PayPalClient $client): int
    {
        try {
            $client->assertConfigured();
            $client->getAccessToken();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('PayPal OAuth OK ('.PayPalClient::baseUrl().').');

        return self::SUCCESS;
    }
}
