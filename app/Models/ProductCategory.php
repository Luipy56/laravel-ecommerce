<?php

namespace App\Models;

use App\Support\CatalogLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $fillable = [
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('sitemap.xml'));
        static::deleted(fn () => Cache::forget('sitemap.xml'));
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductCategoryTranslation::class, 'product_category_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderByTranslatedName(Builder $query, ?string $locale = null, string $dir = 'asc'): Builder
    {
        $loc = CatalogLocale::normalize($locale ?? app()->getLocale());
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        return $query
            ->leftJoin('product_category_translations as pct_sort', function ($join) use ($loc): void {
                $join->on('pct_sort.product_category_id', '=', 'product_categories.id')->where('pct_sort.locale', '=', $loc);
            })
            ->orderBy('pct_sort.name', $dir)
            ->select('product_categories.*');
    }

    public function translatedName(?string $locale = null, ?Collection $rows = null): ?string
    {
        $rows ??= $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        foreach (CatalogLocale::fallbackChain($locale ?? app()->getLocale()) as $loc) {
            $t = $rows->firstWhere('locale', $loc);
            $v = $t?->name;
            if ($v !== null && trim((string) $v) !== '') {
                return is_string($v) ? $v : null;
            }
        }

        return null;
    }

    public function getNameAttribute(): ?string
    {
        return $this->translatedName();
    }
}
