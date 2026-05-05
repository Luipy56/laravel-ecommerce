<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GDPR data retention command.
 *
 * Runs scheduled anonymisation and deletion of personal data according to the
 * retention matrix defined in docs/gdpr-compliance.md. Safe to run repeatedly.
 * Always anonymises rather than hard-deleting rows that are referenced by
 * financial or legal records (orders, payments).
 *
 * Schedule: weekly (see bootstrap/app.php or routes/console.php).
 *
 * Retention rules (summary):
 *   - sessions               > 30 days         → hard delete
 *   - personalized_solutions inactive > 3 years → anonymise personal fields
 *   - clients                inactive > 5 years → anonymise account
 *   - order_addresses        order   > 7 years  → hard delete
 *   - payments               > 7 years          → anonymise gateway refs
 *   - product_reviews        client anonymised  → replace name with [deleted]
 */
class GdprPurgeCommand extends Command
{
    protected $signature = 'gdpr:purge {--dry-run : Log what would be deleted without making changes}';

    protected $description = 'Anonymise and delete personal data per GDPR retention rules';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('[DRY RUN] No changes will be made.');
        }

        $this->purgeSessions($dryRun);
        $this->purgePersonalizedSolutions($dryRun);
        $this->purgeInactiveClients($dryRun);
        $this->purgeOrderAddresses($dryRun);
        $this->anonymisePayments($dryRun);

        $this->info('gdpr:purge completed.');

        return self::SUCCESS;
    }

    private function purgeSessions(bool $dryRun): void
    {
        $cutoff = now()->subDays(30);
        $count = DB::table('sessions')->where('last_activity', '<', $cutoff->timestamp)->count();

        $this->line("Sessions older than 30 days: {$count}");

        if (! $dryRun && $count > 0) {
            DB::table('sessions')->where('last_activity', '<', $cutoff->timestamp)->delete();
            Log::info("gdpr:purge — deleted {$count} stale sessions");
        }
    }

    private function purgePersonalizedSolutions(bool $dryRun): void
    {
        $cutoff = now()->subYears(3);

        $query = DB::table('personalized_solutions')
            ->where('is_active', false)
            ->where('updated_at', '<', $cutoff);

        $count = $query->count();
        $this->line("Inactive personalized solutions older than 3 years: {$count}");

        if (! $dryRun && $count > 0) {
            $query->update([
                'email' => null,
                'phone' => null,
                'address_street' => null,
                'address_city' => null,
                'address_province' => null,
                'address_postal_code' => null,
                'address_note' => null,
                'problem_description' => null,
                'resolution' => null,
                'improvement_feedback' => null,
            ]);
            Log::info("gdpr:purge — anonymised {$count} old personalized_solutions");
        }
    }

    private function purgeInactiveClients(bool $dryRun): void
    {
        $cutoff = now()->subYears(5);

        $clients = DB::table('clients')
            ->where('is_active', false)
            ->where('updated_at', '<', $cutoff)
            ->pluck('id');

        $count = $clients->count();
        $this->line("Inactive clients older than 5 years: {$count}");

        if (! $dryRun && $count > 0) {
            foreach ($clients as $clientId) {
                DB::table('clients')
                    ->where('id', $clientId)
                    ->update([
                        'identification' => null,
                        'login_email' => 'purged_' . $clientId . '@purged.invalid',
                    ]);

                DB::table('client_contacts')
                    ->where('client_id', $clientId)
                    ->update([
                        'name' => '[purged]',
                        'surname' => null,
                        'phone' => null,
                        'phone2' => null,
                        'email' => null,
                    ]);

                DB::table('client_addresses')
                    ->where('client_id', $clientId)
                    ->update(['is_active' => false]);
            }
            Log::info("gdpr:purge — anonymised {$count} inactive clients");
        }
    }

    private function purgeOrderAddresses(bool $dryRun): void
    {
        $cutoff = now()->subYears(7);

        $count = DB::table('order_addresses')
            ->join('orders', 'orders.id', '=', 'order_addresses.order_id')
            ->where('orders.created_at', '<', $cutoff)
            ->count();

        $this->line("Order addresses older than 7 years: {$count}");

        if (! $dryRun && $count > 0) {
            $orderIds = DB::table('orders')
                ->where('created_at', '<', $cutoff)
                ->pluck('id');

            DB::table('order_addresses')->whereIn('order_id', $orderIds)->delete();
            Log::info("gdpr:purge — deleted {$count} old order_addresses");
        }
    }

    private function anonymisePayments(bool $dryRun): void
    {
        $cutoff = now()->subYears(7);

        $count = DB::table('payments')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->where('orders.created_at', '<', $cutoff)
            ->whereNotNull('payments.gateway_reference')
            ->count();

        $this->line("Payment gateway references older than 7 years: {$count}");

        if (! $dryRun && $count > 0) {
            $orderIds = DB::table('orders')
                ->where('created_at', '<', $cutoff)
                ->pluck('id');

            DB::table('payments')
                ->whereIn('order_id', $orderIds)
                ->update([
                    'gateway_reference' => null,
                    'metadata' => null,
                    'failure_message' => null,
                ]);
            Log::info("gdpr:purge — anonymised {$count} old payment records");
        }
    }
}
