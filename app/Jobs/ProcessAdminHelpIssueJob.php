<?php

namespace App\Jobs;

use App\Services\AdminHelpIssueProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessAdminHelpIssueJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 960;

    public function handle(AdminHelpIssueProcessor $processor): void
    {
        $processed = $processor->processPending(1);

        if ($processed === 0) {
            Log::info('admin_help: queue job found nothing to process');
        }
    }
}
