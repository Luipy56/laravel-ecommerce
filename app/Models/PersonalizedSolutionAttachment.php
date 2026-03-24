<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalizedSolutionAttachment extends Model
{
    protected $table = 'personalized_solution_attachments';

    protected $fillable = [
        'personalized_solution_id',
        'storage_path',
        'original_filename',
        'size_bytes',
        'checksum',
        'content_type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function personalizedSolution(): BelongsTo
    {
        return $this->belongsTo(PersonalizedSolution::class, 'personalized_solution_id');
    }

    /** URL for display (files in public disk, same as ProductImage). */
    public function getUrlAttribute(): string
    {
        if (empty($this->storage_path)) {
            return '';
        }
        return '/uploads/' . ltrim($this->storage_path, '/');
    }
}
