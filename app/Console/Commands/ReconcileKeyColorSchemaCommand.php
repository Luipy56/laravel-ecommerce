<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\KeyColorSchemaHelper;
use Illuminate\Console\Command;

class ReconcileKeyColorSchemaCommand extends Command
{
    protected $signature = 'db:reconcile-key-color-schema';

    protected $description = 'Apply key-color schema drift fixes on long-lived databases before migrate (safe no-op on fresh installs)';

    public function handle(): int
    {
        $actions = KeyColorSchemaHelper::reconcile();

        if ($actions === []) {
            $this->comment('Key color schema already up to date.');

            return self::SUCCESS;
        }

        foreach ($actions as $action) {
            $this->info($action);
        }

        return self::SUCCESS;
    }
}
