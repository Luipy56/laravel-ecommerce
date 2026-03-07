<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'client_id',
        'kind',
        'status',
        'order_date',
        'shipping_date',
        'shipping_price',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'shipping_date' => 'datetime',
            'shipping_price' => 'decimal:2',
        ];
    }

    public const KIND_CART = 'cart';
    public const KIND_ORDER = 'order';
    public const KIND_LIKE = 'like';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_SENT = 'sent';
    public const STATUS_INSTALLATION_PENDING = 'installation_pending';
    public const STATUS_INSTALLATION_CONFIRMED = 'installation_confirmed';

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class, 'order_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /** Display reference for admin (e.g. invoices, links). */
    public function getReferenceAttribute(): string
    {
        return 'ORD-' . $this->id;
    }

    public function isCart(): bool
    {
        return $this->kind === self::KIND_CART;
    }

    public function scopeCarts($query)
    {
        return $query->where('kind', self::KIND_CART);
    }

    public function scopeOrders($query)
    {
        return $query->where('kind', self::KIND_ORDER);
    }

    public function scopeLikes($query)
    {
        return $query->where('kind', self::KIND_LIKE);
    }
}
