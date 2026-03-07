<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackImage extends Model
{
    protected $table = 'pack_images';

    protected $fillable = [
        'pack_id',
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

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /** URL for display (files in public disk, same as ProductImage). */
    public function getUrlAttribute(): string
    {
        if (empty($this->storage_path)) {
            return '/images/dummy.jpg';
        }
        return '/uploads/' . ltrim($this->storage_path, '/');
    }
}
