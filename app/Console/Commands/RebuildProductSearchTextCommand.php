<?php

namespace App\Console\Commands;

use App\Contracts\RebuildsProductSearchText;
use Illuminate\Console\Command;

class RebuildProductSearchTextCommand extends Command
{
    protected $signature = 'products:rebuild-search-text
                            {--stale : Only rows where search_text is null or does not match normalized fields}
                            {--chunk=100 : Number of records per database chunk}';

    protected $description = 'Recompute products.search_text from name, code, and description (for bulk DB changes that skipped Eloquent)';

    public function handle(RebuildsProductSearchText $rebuildsProductSearchText): int
    {
        $chunk = max(1, (int) $this->option('chunk'));
        $n = $this->option('stale')
            ? $rebuildsProductSearchText->rebuildStale($chunk)
            : $rebuildsProductSearchText->rebuildAll($chunk);
        $this->info("Updated {$n} product(s).");

        return self::SUCCESS;
    }
}
