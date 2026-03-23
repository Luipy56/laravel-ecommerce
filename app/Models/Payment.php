<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $table = 'payments';

    public const STATUS_PENDING = 'pending';

    public const STATUS_REQUIRES_ACTION = 'requires_action';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELED = 'canceled';

    public const STATUS_REFUNDED = 'refunded';

    public const GATEWAY_STRIPE = 'stripe';

    public const GATEWAY_REDSYS = 'redsys';

    public const GATEWAY_REVOLUT = 'revolut';

    public const METHOD_CARD = 'card';

    public const METHOD_PAYPAL = 'paypal';

    public const METHOD_BIZUM = 'bizum';

    public const METHOD_REVOLUT = 'revolut';

    protected $fillable = [
        'order_id',
        'amount',
        'payment_method',
        'status',
        'gateway',
        'currency',
        'gateway_reference',
        'failure_code',
        'failure_message',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCEEDED && $this->paid_at !== null;
    }

    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_SUCCEEDED)->whereNotNull('paid_at');
    }
}
