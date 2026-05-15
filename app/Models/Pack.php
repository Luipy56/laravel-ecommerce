<?php

namespace App\Models;

use App\Support\CatalogLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Pack extends Model
{
    protected $table = 'packs';

    protected $fillable = [
        'price',
        'is_trending',
        'is_active',
        'contains_keys',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
            'contains_keys' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(PackTranslation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PackItem::class, 'pack_id')->where('is_active', true);
    }

    /** All pack items (no is_active filter); use for admin. */
    public function packItems(): HasMany
    {
        return $this->hasMany(PackItem::class, 'pack_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackImage::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class, 'pack_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderByTranslatedName(Builder $query, ?string $locale = null, string $dir = 'asc'): Builder
    {
        $loc = CatalogLocale::normalize($locale ?? app()->getLocale());
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        return $query
            ->leftJoin('pack_translations as pkt_sort', function ($join) use ($loc): void {
                $join->on('pkt_sort.pack_id', '=', 'packs.id')->where('pkt_sort.locale', '=', $loc);
            })
            ->orderBy('pkt_sort.name', $dir)
            ->select('packs.*');
    }

    public function translatedName(?string $locale = null, ?Collection $rows = null): ?string
    {
        return $this->translatedField('name', $locale, $rows);
    }

    public function translatedDescription(?string $locale = null, ?Collection $rows = null): ?string
    {
        return $this->translatedField('description', $locale, $rows);
    }

    private function translatedField(string $field, ?string $locale, ?Collection $rows): ?string
    {
        $rows ??= $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        foreach (CatalogLocale::fallbackChain($locale ?? app()->getLocale()) as $loc) {
            $t = $rows->firstWhere('locale', $loc);
            $v = $t?->{$field};
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

    public function getDescriptionAttribute(): ?string
    {
        return $this->translatedDescription();
    }
}
