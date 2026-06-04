<?php

use App\Console\Commands\GdprPurgeCommand;
use App\Console\Commands\ProcessAdminHelpIssuesCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// GDPR data retention — runs every Sunday at 02:00
Schedule::command(GdprPurgeCommand::class)->weeklyOn(0, '02:00');

// Admin Help → GitHub issues (pending JSON payloads; daily fallback if queue job missed)
Schedule::command(ProcessAdminHelpIssuesCommand::class, [
    '--limit' => config('admin_help.fallback_schedule_limit', 10),
])
    ->dailyAt((string) config('admin_help.fallback_schedule_at', '03:00'));
