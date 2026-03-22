<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLine extends Model
{
    protected $table = 'order_lines';

    protected $fillable = [
        'order_id',
        'product_id',
        'pack_id',
        'quantity',
        'unit_price',
        'offer',
        'keys_all_same',
        'extra_keys_qty',
        'extra_key_unit_price',
        'is_included',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'offer' => 'decimal:2',
            'extra_key_unit_price' => 'decimal:2',
            'keys_all_same' => 'boolean',
            'is_included' => 'boolean',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function pack(): BelongsTo
    {
        return $this->belongsTo(Pack::class);
    }

    /** Display name: product name or pack name. */
    public function getDisplayNameAttribute(): string
    {
        if ($this->product_id) {
            return $this->product?->name ?? '';
        }

        return $this->pack?->name ?? '';
    }

    /** Line total (quantity * unit_price - offer + extra keys). Installation is on the order, not the line. */
    public function getLineTotalAttribute(): float
    {
        if (isset($this->attributes['is_included']) && ! $this->is_included) {
            return 0;
        }
        $sub = (float) $this->unit_price * (int) $this->quantity;
        $off = (float) ($this->offer ?? 0);
        $extra = (int) ($this->extra_keys_qty ?? 0) * (float) ($this->extra_key_unit_price ?? 0);

        return round($sub - $off + $extra, 2);
    }
}
