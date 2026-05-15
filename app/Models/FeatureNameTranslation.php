<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureNameTranslation extends Model
{
    protected $table = 'feature_name_translations';

    protected $fillable = [
        'feature_name_id',
        'locale',
        'name',
    ];

    public function featureName(): BelongsTo
    {
        return $this->belongsTo(FeatureName::class, 'feature_name_id');
    }
}
