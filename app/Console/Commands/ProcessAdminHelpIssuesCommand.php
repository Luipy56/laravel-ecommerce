<?php

namespace App\Console\Commands;

use App\Services\AdminHelpIssueProcessor;
use App\Services\AdminHelpIssueRequestService;
use Illuminate\Console\Command;

class ProcessAdminHelpIssuesCommand extends Command
{
    protected $signature = 'admin-help:process {--limit=10 : Maximum pending requests to process} {--dry-run : Validate and claim without creating GitHub issues}';

    protected $description = 'Process pending admin Help requests into GitHub issues via cursor-agent and gh';

    public function handle(
        AdminHelpIssueRequestService $requests,
        AdminHelpIssueProcessor $processor,
    ): int {
        $limit = max(1, (int) $this->option('limit'));
        $dryRun = (bool) $this->option('dry-run');

        $requests->ensureDirectories();
        $recovered = $requests->recoverStaleProcessing();
        if ($recovered > 0) {
            $this->info("Recovered {$recovered} stale processing request(s) to pending.");
        }

        if ($dryRun) {
            $this->info('[DRY RUN] No GitHub issues will be created.');
        }

        $pendingCount = count($requests->listPendingIds());
        if ($pendingCount === 0) {
            $this->info('No pending admin help requests.');

            return self::SUCCESS;
        }

        $this->info("Processing up to {$limit} of {$pendingCount} pending request(s)...");
        $processed = $processor->processPending($limit, $dryRun);
        $this->info("Finished: {$processed} request(s) processed.");

        return self::SUCCESS;
    }
}
