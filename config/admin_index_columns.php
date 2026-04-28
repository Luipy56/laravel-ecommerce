<?php

/**
 * Allowed admin list-view column ids per table. Order = default column order.
 *
 * Keep in sync with `resources/js/config/adminIndexColumnsRegistry.js`.
 * Unknown ids stored in `shop_settings` are stripped on read; empty lists fall back to full defaults.
 */
return [
    'products' => ['code', 'name', 'category', 'price', 'discount_percent', 'stock', 'is_active'],
    'categories' => ['code', 'name', 'is_active'],
    'clients' => ['email', 'type', 'identification', 'primary_contact', 'contacts_count', 'addresses_count', 'is_active'],
    'variant_groups' => ['group_label', 'products_in_group'],
    'orders' => ['id', 'kind', 'client', 'order_date', 'lines_count', 'total', 'status'],
    'packs' => ['name', 'price', 'products_in_pack', 'is_trending', 'is_active'],
    'admins' => ['username', 'is_active', 'last_login_at', 'created_at'],
    'personalized_solutions' => ['email', 'phone', 'problem_description', 'status', 'created_at', 'is_active'],
    /** Feature types list (`/admin/features` first table). */
    'feature_types' => ['name', 'is_active'],
    /** Characteristics list (`/admin/features` second table). */
    'features' => ['feature_name', 'value', 'is_active'],
];
