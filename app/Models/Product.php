<?php

namespace App\Models;

use App\Support\CatalogLocale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Scout\Searchable;

/**
 * Sellable catalog item: pricing, stock, merchandising flags, and Scout indexing for search.
 *
 * Localised name/description live in {@see ProductTranslation}; per-locale search_text is maintained there.
 */
class Product extends Model
{
    use Searchable;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'variant_group_id',
        'code',
        'price',
        'purchase_price',
        'stock',
        'weight_kg',
        'is_double_clutch',
        'has_card',
        'security_level',
        'competitor_url',
        'is_extra_keys_available',
        'extra_key_unit_price',
        'is_featured',
        'is_trending',
        'is_active',
        'discount_percent',
    ];

    protected static function booted(): void
    {
        static::saved(function (Product $product): void {
            if ($product->wasChanged('code')) {
                $product->translations()->each(function (ProductTranslation $t): void {
                    $t->save();
                });
            }
        });

        static::saved(fn () => Cache::forget('sitemap.xml'));
        static::deleted(fn () => Cache::forget('sitemap.xml'));
    }

    /**
     * Builds a single normalised string used for catalog and full-text search.
     *
     * @param  string|null  $name  Product display name.
     * @param  string|null  $code  Internal or supplier code.
     * @param  string|null  $description  Long description text.
     * @return string Lowercase, whitespace-collapsed text; diacritics folded when intl or iconv is available.
     */
    public static function normalizeSearchText(?string $name, ?string $code, ?string $description): string
    {
        $merged = trim(preg_replace('/\s+/u', ' ', implode(' ', array_filter([
            $name ?? '',
            $code ?? '',
            $description ?? '',
        ], fn ($part) => $part !== ''))));

        $folded = self::foldDiacritics($merged);

        return mb_strtolower($folded, 'UTF-8');
    }

    private static function foldDiacritics(string $text): string
    {
        if (extension_loaded('intl') && class_exists(\Transliterator::class)) {
            $t = \Transliterator::create('NFD; [:Nonspacing Mark:] Remove; NFC');
            if ($t !== null) {
                $out = $t->transliterate($text);
                if ($out !== false && $out !== '') {
                    return $out;
                }
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false && $converted !== '') {
                return $converted;
            }
        }

        return $text;
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'extra_key_unit_price' => 'decimal:2',
            'avg_rating' => 'decimal:2',
            'reviews_count' => 'integer',
            'is_double_clutch' => 'boolean',
            'has_card' => 'boolean',
            'is_extra_keys_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function variantGroup(): BelongsTo
    {
        return $this->belongsTo(ProductVariantGroup::class, 'variant_group_id');
    }

    /**
     * Other products in the same variant group (siblings). Order by translated name asc.
     */
    public function variantSiblings()
    {
        if (! $this->variant_group_id) {
            return collect();
        }

        return Product::query()
            ->where('variant_group_id', $this->variant_group_id)
            ->where('id', '!=', $this->id)
            ->active()
            ->orderByTranslatedName()
            ->with('translations')
            ->get();
    }

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'product_features');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function packItems(): HasMany
    {
        return $this->hasMany(PackItem::class, 'product_id');
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class, 'product_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
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
            ->leftJoin('product_translations as pt_sort', function ($join) use ($loc): void {
                $join->on('pt_sort.product_id', '=', 'products.id')->where('pt_sort.locale', '=', $loc);
            })
            ->orderBy('pt_sort.name', $dir)
            ->select('products.*');
    }

    /** @param  Collection<int, ProductTranslation>|null  $rows */
    public function translatedName(?string $locale = null, ?Collection $rows = null): ?string
    {
        return $this->translatedField('name', $locale, $rows);
    }

    /** @param  Collection<int, ProductTranslation>|null  $rows */
    public function translatedDescription(?string $locale = null, ?Collection $rows = null): ?string
    {
        return $this->translatedField('description', $locale, $rows);
    }

    /**
     * @param  Collection<int, ProductTranslation>|null  $rows
     */
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

        // Fallback to legacy column when no translation rows exist yet
        $legacy = $this->attributes[$field] ?? null;
        if ($legacy !== null && trim((string) $legacy) !== '') {
            return (string) $legacy;
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

    /** Customer-facing unit price after optional percentage discount. */
    public function effectivePrice(): float
    {
        $base = (float) $this->price;
        $d = $this->discount_percent;
        if ($d === null) {
            return round($base, 2);
        }
        $pct = min(100.0, max(0.0, (float) $d));
        if ($pct <= 0) {
            return round($base, 2);
        }

        return round($base * (1 - $pct / 100), 2);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('translations');
        $by = $this->translations->keyBy('locale');

        $out = [
            'id' => $this->getKey(),
            'code' => $this->code !== null ? (string) $this->code : '',
            'is_active' => (bool) $this->is_active,
        ];

        $inputs = [];
        foreach (CatalogLocale::SUPPORTED as $loc) {
            $t = $by->get($loc);
            $name = $t?->name;
            $desc = $t?->description;
            $st = $t?->search_text;
            $out['name_'.$loc] = $name !== null ? (string) $name : '';
            $out['description_'.$loc] = $desc !== null ? (string) $desc : '';
            $out['search_text_'.$loc] = $st !== null ? (string) $st : '';
            if ($name !== null && trim((string) $name) !== '') {
                $inputs[] = trim((string) $name);
            }
        }
        if ($this->code !== null && trim((string) $this->code) !== '') {
            $inputs[] = trim((string) $this->code);
        }
        $inputs = array_values(array_unique(array_filter($inputs, fn ($v) => $v !== '')));
        if ($inputs === []) {
            $inputs = [(string) $this->getKey()];
        }

        $out['suggest'] = [
            'input' => $inputs,
            'weight' => $this->is_featured ? 2 : 1,
        ];

        return $out;
    }
}
