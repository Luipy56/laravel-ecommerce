<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\ModelObserver;

class ProductTranslation extends Model
{
    protected $table = 'product_translations';

    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'description',
        'search_text',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductTranslation $t): void {
            $code = Product::query()->whereKey($t->product_id)->value('code');
            $t->search_text = Product::normalizeSearchText($t->name, $code !== null ? (string) $code : null, $t->description);
        });

        static::saved(function (ProductTranslation $t): void {
            $p = Product::query()->whereKey($t->product_id)->first();
            if ($p !== null && $p->shouldBeSearchable() && ! ModelObserver::syncingDisabledFor($p)) {
                $p->searchable();
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
