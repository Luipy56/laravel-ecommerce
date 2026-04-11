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
        'installation_requested',
        'installation_price',
        'installation_status',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'shipping_date' => 'datetime',
            'shipping_price' => 'decimal:2',
            'installation_requested' => 'boolean',
            'installation_price' => 'decimal:2',
        ];
    }

    public const KIND_CART = 'cart';

    public const KIND_ORDER = 'order';

    public const KIND_LIKE = 'like';

    public const STATUS_PENDING = 'pending';

    /** Order placed; PayPal (or external) payment not captured yet — do not treat as paid/fulfilment queue. */
    public const STATUS_AWAITING_PAYMENT = 'awaiting_payment';

    public const STATUS_AWAITING_INSTALLATION_PRICE = 'awaiting_installation_price';

    public const STATUS_IN_TRANSIT = 'in_transit';

    public const STATUS_SENT = 'sent';

    public const STATUS_INSTALLATION_PENDING = 'installation_pending';

    public const STATUS_INSTALLATION_CONFIRMED = 'installation_confirmed';

    public const INSTALLATION_PENDING = 'pending';

    public const INSTALLATION_PRICED = 'priced';

    public const INSTALLATION_REJECTED = 'rejected';

    /** Flat shipping fee (EUR) for every confirmed order; not applied to carts/likes. */
    public const SHIPPING_FLAT_EUR = 9.0;

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
        return 'ORD-'.$this->id;
    }

    public function isCart(): bool
    {
        return $this->kind === self::KIND_CART;
    }

    /** Sum of line totals (products/packs only; no order-level installation). */
    public function getLinesSubtotalAttribute(): float
    {
        $lines = $this->relationLoaded('lines') ? $this->lines : $this->lines()->get();

        return round((float) $lines->sum(fn ($l) => (float) $l->line_total), 2);
    }

    /** Full amount due including installation (when priced) and shipping. */
    public function getGrandTotalAttribute(): float
    {
        $base = (float) $this->lines_subtotal;
        if ($this->installation_requested
            && $this->installation_status === self::INSTALLATION_PRICED
            && $this->installation_price !== null) {
            $base += (float) $this->installation_price;
        }
        if ($this->kind === self::KIND_ORDER) {
            $base += self::SHIPPING_FLAT_EUR;
        }

        return round($base, 2);
    }

    public function hasSuccessfulPayment(): bool
    {
        return $this->payments()->successful()->exists();
    }

    /**
     * Client may complete payment (POST pay) when order is confirmed, no payment yet, and
     * installation quote is not required or already priced.
     */
    public function clientMayPay(): bool
    {
        if ($this->kind !== self::KIND_ORDER || $this->hasSuccessfulPayment()) {
            return false;
        }
        if ($this->installation_requested) {
            if ($this->installation_status === self::INSTALLATION_REJECTED) {
                return true;
            }

            return $this->installation_status === self::INSTALLATION_PRICED
                && $this->installation_price !== null;
        }

        return true;
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
