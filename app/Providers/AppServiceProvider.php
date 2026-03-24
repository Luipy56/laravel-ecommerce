<?php

namespace App\Providers;

use App\Events\InstallationPriceWasAssigned;
use App\Listeners\SendInstallationPriceAssignedEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(InstallationPriceWasAssigned::class, SendInstallationPriceAssignedEmail::class);
    }
}
