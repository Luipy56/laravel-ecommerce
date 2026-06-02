<?php

namespace App\Models;

use App\Support\CatalogLocale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Feature extends Model
{
    protected $table = 'features';

    protected $fillable = [
        'feature_name_id',
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
        return $this->hasMany(FeatureTranslation::class);
    }

    public function featureName(): BelongsTo
    {
        return $this->belongsTo(FeatureName::class, 'feature_name_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_features');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrderByTranslatedValue($query, ?string $locale = null, string $dir = 'asc')
    {
        $loc = CatalogLocale::normalize($locale ?? app()->getLocale());
        $dir = strtolower($dir) === 'desc' ? 'desc' : 'asc';

        return $query
            ->leftJoin('feature_translations as ft_sort', function ($join) use ($loc): void {
                $join->on('ft_sort.feature_id', '=', 'features.id')->where('ft_sort.locale', '=', $loc);
            })
            ->orderBy('ft_sort.value', $dir)
            ->select('features.*');
    }

    public function translatedValue(?string $locale = null, ?Collection $rows = null): ?string
    {
        $rows ??= $this->relationLoaded('translations') ? $this->translations : $this->translations()->get();
        foreach (CatalogLocale::fallbackChain($locale ?? app()->getLocale()) as $loc) {
            $t = $rows->firstWhere('locale', $loc);
            $v = $t?->value;
            if ($v !== null && trim((string) $v) !== '') {
                return is_string($v) ? $v : null;
            }
        }

        // Fallback to legacy value column when no translation rows exist yet
        $legacy = $this->attributes['value'] ?? null;
        if ($legacy !== null && trim((string) $legacy) !== '') {
            return (string) $legacy;
        }

        return null;
    }

    public function getValueAttribute(): ?string
    {
        return $this->translatedValue();
    }
}
