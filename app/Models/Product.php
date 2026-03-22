<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'variant_group_id',
        'code',
        'name',
        'description',
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

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'extra_key_unit_price' => 'decimal:2',
            'is_double_clutch' => 'boolean',
            'has_card' => 'boolean',
            'is_extra_keys_available' => 'boolean',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
        ];
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
     * Other products in the same variant group (siblings). Order by name asc.
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
            ->orderBy('name')
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
