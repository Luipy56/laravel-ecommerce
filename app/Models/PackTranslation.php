<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackTranslation extends Model
{
    protected $table = 'pack_translations';

    protected $fillable = [
        'pack_id',
        'locale',
        'name',
        'description',
        'search_text',
    ];

    protected static function booted(): void
    {
        static::saving(function (PackTranslation $t): void {
            $t->search_text = Product::normalizeSearchText($t->name, '', $t->description);
        });
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }
}
