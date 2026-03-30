<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Events\InstallationPriceWasAssigned;
use App\Events\OrderInstallationQuoteRequested;
use App\Events\OrderPaymentSucceeded;
use App\Events\OrderShipped;
use App\Events\PersonalizedSolutionSubmitted;
use App\Listeners\SendInstallationPriceAssignedEmail;
use App\Listeners\SendOrderInstallationQuoteRequestEmail;
use App\Listeners\SendOrderPaymentConfirmationEmail;
use App\Listeners\SendOrderShippedEmail;
use App\Listeners\SendPersonalizedSolutionAcknowledgementEmail;
use App\Services\Payments\PaymentCheckoutService;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->alignSessionDriverForSqliteMemory();

        $this->app->when(PaymentCheckoutService::class)
            ->needs(PaymentCheckoutStarter::class)
            ->give(StripeCheckoutStarter::class);
    }

    /**
     * Avoid database session driver against SQLite :memory: (no stable migrated schema for sessions).
     * PHPUnit sets SESSION_DRIVER=array, but cached config can still force "database"; local .env can
     * pair DB_DATABASE=:memory: with SESSION_DRIVER=database.
     */
    private function alignSessionDriverForSqliteMemory(): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $default = config('database.default');
        $sessionConnection = config('session.connection') ?: $default;
        $conn = config("database.connections.{$sessionConnection}");
        if (($conn['driver'] ?? null) !== 'sqlite') {
            return;
        }

        if (($conn['database'] ?? null) === ':memory:') {
            config(['session.driver' => 'array']);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(InstallationPriceWasAssigned::class, SendInstallationPriceAssignedEmail::class);
        Event::listen(OrderPaymentSucceeded::class, SendOrderPaymentConfirmationEmail::class);
        Event::listen(OrderInstallationQuoteRequested::class, SendOrderInstallationQuoteRequestEmail::class);
        Event::listen(PersonalizedSolutionSubmitted::class, SendPersonalizedSolutionAcknowledgementEmail::class);
        Event::listen(OrderShipped::class, SendOrderShippedEmail::class);
    }
}
