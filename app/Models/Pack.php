<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pack extends Model
{
    protected $table = 'packs';

    protected $fillable = [
        'name',
        'description',
        'price',
        'is_trending',
        'is_active',
        'is_installable',
        'installation_price',
        'contains_keys',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'installation_price' => 'decimal:2',
            'is_trending' => 'boolean',
            'is_active' => 'boolean',
            'is_installable' => 'boolean',
            'contains_keys' => 'boolean',
        ];
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
