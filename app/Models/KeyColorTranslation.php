<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KeyColorTranslation extends Model
{
    protected $table = 'key_color_translations';

    protected $fillable = [
        'key_color_id',
        'locale',
        'name',
    ];

    public function keyColor(): BelongsTo
    {
        return $this->belongsTo(KeyColor::class, 'key_color_id');
    }
}
