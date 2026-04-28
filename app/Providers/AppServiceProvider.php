<?php

namespace App\Providers;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Contracts\RebuildsProductSearchText;
use App\Contracts\Search\ElasticsearchProductCatalogSearch;
use App\Events\InstallationPriceWasAssigned;
use App\Events\OrderInstallationQuoteRequested;
use App\Events\OrderPaymentSucceeded;
use App\Events\OrderPlacedPaymentPending;
use App\Events\OrderShipped;
use App\Events\PersonalizedSolutionSubmitted;
use App\Listeners\SendInstallationPriceAssignedEmail;
use App\Listeners\SendOrderInstallationQuoteRequestAdminEmail;
use App\Listeners\SendOrderInstallationQuoteRequestEmail;
use App\Listeners\SendOrderPaymentConfirmationEmail;
use App\Listeners\SendOrderPaymentPendingAdminEmail;
use App\Listeners\SendOrderPaymentPendingEmail;
use App\Listeners\SendOrderShippedEmail;
use App\Listeners\SendPersonalizedSolutionAcknowledgementEmail;
use App\Scout\ElasticsearchClientFactory;
use App\Scout\ElasticsearchEngine;
use App\Services\Payments\PaymentCheckoutService;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use App\Services\ProductSearchTextRebuildService;
use App\Services\Search\ScoutElasticsearchProductCatalogSearch;
use App\Services\Search\SearchSynonymDictionary;
use App\Support\MailLocale;
use App\Support\SqliteDatabaseBootstrap;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Scout\EngineManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->alignSessionDriverForSqliteMemory();

        $defaultName = config('database.default');
        $defaultKey = is_string($defaultName) && $defaultName !== '' ? $defaultName : 'sqlite';
        SqliteDatabaseBootstrap::touchDatabaseFileIfMissing(
            $this->app->environment(),
            config("database.connections.{$defaultKey}", []),
        );

        $this->app->when(PaymentCheckoutService::class)
            ->needs(PaymentCheckoutStarter::class)
            ->give(StripeCheckoutStarter::class);

        $this->app->bind(RebuildsProductSearchText::class, ProductSearchTextRebuildService::class);

        $this->app->bind(ElasticsearchProductCatalogSearch::class, ScoutElasticsearchProductCatalogSearch::class);
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
        $synonymOverlay = (new SearchSynonymDictionary(config('search_synonyms', [])))->elasticsearchIndexOverlay();
        if ($synonymOverlay !== []) {
            $current = config('scout.elasticsearch.index_definitions.products', []);
            config(['scout.elasticsearch.index_definitions.products' => array_replace_recursive($current, $synonymOverlay)]);
        }

        // Skip when laravel/scout is not installed (partial vendor tree); ScoutServiceProvider registers EngineManager.
        if (class_exists(EngineManager::class)) {
            resolve(EngineManager::class)->extend('elasticsearch', function () {
                return new ElasticsearchEngine(
                    ElasticsearchClientFactory::make(config('scout.elasticsearch', [])),
                    (bool) config('scout.soft_delete', false),
                );
            });
        }

        Event::listen(InstallationPriceWasAssigned::class, SendInstallationPriceAssignedEmail::class);
        Event::listen(OrderPaymentSucceeded::class, SendOrderPaymentConfirmationEmail::class);
        Event::listen(OrderInstallationQuoteRequested::class, SendOrderInstallationQuoteRequestEmail::class);
        Event::listen(OrderInstallationQuoteRequested::class, SendOrderInstallationQuoteRequestAdminEmail::class);
        Event::listen(OrderPlacedPaymentPending::class, SendOrderPaymentPendingEmail::class);
        Event::listen(OrderPlacedPaymentPending::class, SendOrderPaymentPendingAdminEmail::class);
        Event::listen(PersonalizedSolutionSubmitted::class, SendPersonalizedSolutionAcknowledgementEmail::class);
        Event::listen(OrderShipped::class, SendOrderShippedEmail::class);

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            app()->setLocale(MailLocale::resolve());

            return (new MailMessage)
                ->subject(__('auth.verify_email_subject'))
                ->line(__('auth.verify_email_line1'))
                ->action(__('auth.verify_email_action'), $url)
                ->line(__('auth.verify_email_line2'));
        });

        ResetPassword::createUrlUsing(function ($user, string $token): string {
            $base = rtrim((string) config('app.url'), '/');
            $path = config('app.frontend_reset_password_path', '/reset-password');
            $email = urlencode($user->getEmailForPasswordReset());

            return $base.$path.'?token='.urlencode($token).'&login_email='.$email;
        });
    }
}
