<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Feature extends Model
{
    protected $table = 'features';

    protected $fillable = [
        'feature_name_id',
        'value',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
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
}
