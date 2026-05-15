<?php

namespace App\Models;

use App\Support\CatalogLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class FeatureName extends Model
{
    protected $table = 'feature_names';

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

    public function translations(): HasMany
    {
        return $this->hasMany(FeatureNameTranslation::class, 'feature_name_id');
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class, 'feature_name_id');
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
            ->leftJoin('feature_name_translations as fnt_sort', function ($join) use ($loc): void {
                $join->on('fnt_sort.feature_name_id', '=', 'feature_names.id')->where('fnt_sort.locale', '=', $loc);
            })
            ->orderBy('fnt_sort.name', $dir)
            ->select('feature_names.*');
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
