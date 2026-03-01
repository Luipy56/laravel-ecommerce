<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProductImage extends Model
{
    protected $table = 'product_images';

    protected $fillable = [
        'product_id',
        'storage_path',
        'filename',
        'size_bytes',
        'checksum',
        'content_type',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** URL for display (storage path or placeholder). */
    public function getUrlAttribute(): string
    {
        if (empty($this->storage_path)) {
            return '/images/dummy.jpg';
        }
        return Storage::url($this->storage_path);
    }
}
