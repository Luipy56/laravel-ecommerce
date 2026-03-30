<?php

namespace App\Console\Commands;

use App\Contracts\RebuildsProductSearchText;
use Illuminate\Console\Command;

class RebuildProductSearchTextCommand extends Command
{
    protected $signature = 'products:rebuild-search-text';

    protected $description = 'Recompute products.search_text from name, code, and description (for bulk DB changes that skipped Eloquent)';

    public function handle(RebuildsProductSearchText $rebuildsProductSearchText): int
    {
        $n = $rebuildsProductSearchText->rebuildAll();
        $this->info("Updated {$n} product(s).");

        return self::SUCCESS;
    }
}
