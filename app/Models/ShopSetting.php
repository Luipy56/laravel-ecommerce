<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $table = 'shop_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public const KEY_LOW_STOCK_ENABLED = 'low_stock_enabled';

    public const KEY_LOW_STOCK_THRESHOLD = 'low_stock_threshold';

    public const KEY_LOW_STOCK_BLACKLIST_ENABLED = 'low_stock_blacklist_enabled';

    public const KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS = 'low_stock_blacklist_product_ids';

    public const KEY_OVERSTOCK_ENABLED = 'overstock_enabled';

    public const KEY_OVERSTOCK_THRESHOLD = 'overstock_threshold';

    public const KEY_OVERSTOCK_BLACKLIST_ENABLED = 'overstock_blacklist_enabled';

    public const KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS = 'overstock_blacklist_product_ids';

    public const KEY_ACCEPT_PERSONALIZED_SOLUTIONS = 'accept_personalized_solutions';

    /** JSON object: table_id => list of visible column ids (see config/admin_index_columns.php). */
    public const KEY_ADMIN_INDEX_COLUMNS = 'admin_index_columns';

    /** Home featured section: max products per group; 0 = unlimited. */
    public const KEY_FEATURED_MAX_MANUAL = 'featured_max_manual';

    public const KEY_FEATURED_MAX_LOW_STOCK = 'featured_max_low_stock';

    public const KEY_FEATURED_MAX_OVERSTOCK = 'featured_max_overstock';

    /** Flat shipping fee (EUR) for new orders; persisted on each order row at checkout. */
    public const KEY_SHIPPING_FLAT_EUR = 'shipping_flat_eur';

    /**
     * JSON: quote_when_merchandise_above_eur, tiers[{max_merchandise_eur, fee_eur}] sorted ascending by max.
     */
    public const KEY_INSTALLATION_AUTO_PRICING = 'installation_auto_pricing';

    /**
     * Default time period filter for admin order/solution lists.
     * Allowed values: 'week', 'month', 'year', 'all'.
     */
    public const KEY_ADMIN_LIST_DEFAULT_PERIOD = 'admin_list_default_period';

    /**
     * @var array<string, mixed>
     */
    public const DEFAULTS = [
        self::KEY_LOW_STOCK_ENABLED => false,
        self::KEY_LOW_STOCK_THRESHOLD => 10,
        self::KEY_LOW_STOCK_BLACKLIST_ENABLED => false,
        self::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS => [],
        self::KEY_OVERSTOCK_ENABLED => false,
        self::KEY_OVERSTOCK_THRESHOLD => 100,
        self::KEY_OVERSTOCK_BLACKLIST_ENABLED => false,
        self::KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS => [],
        self::KEY_ACCEPT_PERSONALIZED_SOLUTIONS => true,
        self::KEY_FEATURED_MAX_MANUAL => 0,
        self::KEY_FEATURED_MAX_LOW_STOCK => 0,
        self::KEY_FEATURED_MAX_OVERSTOCK => 0,
        self::KEY_SHIPPING_FLAT_EUR => 9.0,
        self::KEY_INSTALLATION_AUTO_PRICING => [
            'quote_when_merchandise_above_eur' => 1000,
            'tiers' => [
                ['max_merchandise_eur' => 250, 'fee_eur' => 90],
                ['max_merchandise_eur' => 500, 'fee_eur' => 120],
                ['max_merchandise_eur' => 1000, 'fee_eur' => 180],
            ],
        ],
        self::KEY_ADMIN_LIST_DEFAULT_PERIOD => 'week',
    ];

    public static function shippingFlatEur(): float
    {
        $v = self::get(self::KEY_SHIPPING_FLAT_EUR);

        return round((float) $v, 2);
    }

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $fallback = array_key_exists($key, self::DEFAULTS)
            ? self::DEFAULTS[$key]
            : $default;

        $row = static::query()->where('key', $key)->first();
        if ($row === null) {
            return $fallback;
        }

        return $row->value;
    }

    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function allMerged(): array
    {
        $out = self::DEFAULTS;
        foreach (static::query()->get(['key', 'value']) as $row) {
            $out[$row->key] = $row->value;
        }

        return $out;
    }
}
