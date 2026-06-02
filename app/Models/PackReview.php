<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackReview extends Model
{
    protected $table = 'pack_reviews';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'pack_id',
        'client_id',
        'order_id',
        'rating',
        'comment',
        'status',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'order_id' => 'integer',
        ];
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeHidden($query)
    {
        return $query->where('status', self::STATUS_HIDDEN);
    }
}
