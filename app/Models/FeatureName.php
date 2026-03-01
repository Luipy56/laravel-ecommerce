<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureName extends Model
{
    protected $table = 'feature_names';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class, 'feature_name_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
