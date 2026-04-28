<?php

/**
 * Allowed admin list-view column ids per table. Order = default column order.
 *
 * Keep in sync with `resources/js/config/adminIndexColumnsRegistry.js`.
 * Unknown ids stored in `shop_settings` are stripped on read; empty lists fall back to full defaults.
 */
return [
    'products' => ['id', 'code', 'name', 'category', 'price', 'discount_percent', 'stock', 'is_featured', 'is_trending', 'is_active'],
    'categories' => ['id', 'code', 'name', 'is_active'],
    'clients' => ['id', 'email', 'type', 'identification', 'primary_contact', 'contacts_count', 'addresses_count', 'is_active'],
    'variant_groups' => ['id', 'group_label', 'products_in_group'],
    'orders' => [
        'id',
        'kind',
        'client',
        'order_date',
        'lines_count',
        'total',
        'status',
        'installation_requested',
        'installation_status',
        'installation_price',
        'shipping_date',
        'created_at',
        'updated_at',
    ],
    'packs' => ['id', 'name', 'price', 'products_in_pack', 'is_trending', 'is_active'],
    'admins' => ['id', 'username', 'is_active', 'last_login_at', 'created_at'],
    'personalized_solutions' => ['id', 'email', 'phone', 'problem_description', 'status', 'created_at', 'client_login_email', 'is_active'],
    /** Feature types list (`/admin/features` first table). */
    'feature_types' => ['id', 'name', 'is_active'],
    /** Characteristics list (`/admin/features` second table). */
    'features' => ['id', 'feature_name_id', 'feature_name', 'value', 'is_active'],
];
