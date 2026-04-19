<?php

/**
 * Admin data explorer: allowlisted tables and columns only (no raw SQL from clients).
 * Adjust allowlists when schema changes; run migrate:fresh --seed after edits.
 */

return [

    'max_per_page' => (int) env('ADMIN_DATA_EXPLORER_MAX_PER_PAGE', 100),

    'max_export_rows' => (int) env('ADMIN_DATA_EXPLORER_MAX_EXPORT_ROWS', 5000),

    'max_aggregate_groups' => (int) env('ADMIN_DATA_EXPLORER_MAX_AGGREGATE_GROUPS', 200),

    /*
    | Session statement timeout for explorer queries (best effort):
    | - PostgreSQL: SET LOCAL statement_timeout
    | - MariaDB / mariadb driver: SET SESSION max_statement_time (seconds)
    | - MySQL 8.0.3+ (mysql driver): SET SESSION max_execution_time (milliseconds)
    | - Older MySQL: no session-level SELECT timeout; queries still run without this guard.
    */

    'query_timeout_seconds' => (int) env('ADMIN_DATA_EXPLORER_QUERY_TIMEOUT', 25),

    /*
    |--------------------------------------------------------------------------
    | Tables (keys = DB table names)
    |--------------------------------------------------------------------------
    | columns: exhaustive allowlist for SELECT / WHERE / ORDER BY.
    | search_columns: subset used for optional global text search (OR LIKE).
    | date_columns: columns offered for optional date range filter.
    */
    'tables' => [
        'orders' => [
            'label_key' => 'admin.data_explorer.tables.orders',
            'columns' => [
                'id', 'client_id', 'kind', 'status', 'order_date', 'shipping_date',
                'shipping_price', 'installation_requested', 'installation_price',
                'installation_status', 'created_at', 'updated_at',
            ],
            'search_columns' => ['kind', 'status', 'installation_status'],
            'date_columns' => ['order_date', 'shipping_date', 'created_at', 'updated_at'],
        ],
        'order_lines' => [
            'label_key' => 'admin.data_explorer.tables.order_lines',
            'columns' => [
                'id', 'order_id', 'product_id', 'pack_id', 'quantity', 'unit_price',
                'offer', 'keys_all_same', 'extra_keys_qty', 'extra_key_unit_price',
                'is_included', 'created_at', 'updated_at',
            ],
            'search_columns' => [],
            'date_columns' => ['created_at', 'updated_at'],
        ],
        'clients' => [
            'label_key' => 'admin.data_explorer.tables.clients',
            'columns' => [
                'id', 'type', 'identification', 'login_email', 'is_active',
                'created_at', 'updated_at',
            ],
            'search_columns' => ['identification', 'login_email', 'type'],
            'date_columns' => ['created_at', 'updated_at'],
        ],
        'products' => [
            'label_key' => 'admin.data_explorer.tables.products',
            'columns' => [
                'id', 'category_id', 'variant_group_id', 'code', 'name', 'description',
                'price', 'discount_percent', 'purchase_price', 'stock', 'weight_kg',
                'is_double_clutch', 'has_card', 'security_level', 'competitor_url',
                'is_extra_keys_available', 'extra_key_unit_price', 'is_featured',
                'is_trending', 'is_active', 'search_text', 'created_at', 'updated_at',
            ],
            'search_columns' => ['code', 'name', 'search_text'],
            'date_columns' => ['created_at', 'updated_at'],
        ],
        'order_addresses' => [
            'label_key' => 'admin.data_explorer.tables.order_addresses',
            'columns' => [
                'id', 'order_id', 'type', 'street', 'city', 'province',
                'postal_code', 'note', 'created_at', 'updated_at',
            ],
            'search_columns' => ['street', 'city', 'province', 'postal_code', 'type'],
            'date_columns' => ['created_at', 'updated_at'],
        ],
        'payments' => [
            'label_key' => 'admin.data_explorer.tables.payments',
            'columns' => [
                'id', 'order_id', 'amount', 'payment_method', 'status', 'gateway',
                'currency', 'gateway_reference', 'failure_code', 'failure_message',
                'paid_at', 'created_at', 'updated_at',
            ],
            'search_columns' => ['payment_method', 'status', 'gateway', 'gateway_reference'],
            'date_columns' => ['paid_at', 'created_at', 'updated_at'],
        ],
        'personalized_solutions' => [
            'label_key' => 'admin.data_explorer.tables.personalized_solutions',
            'columns' => [
                'id', 'client_id', 'order_id', 'email', 'phone',
                'address_street', 'address_city', 'address_province', 'address_postal_code',
                'address_note', 'problem_description', 'resolution', 'status',
                'is_active', 'created_at', 'updated_at',
            ],
            'search_columns' => ['email', 'phone', 'status', 'address_postal_code'],
            'date_columns' => ['created_at', 'updated_at'],
        ],
        'admins' => [
            'label_key' => 'admin.data_explorer.tables.admins',
            'columns' => [
                'id', 'username', 'is_active', 'last_login_at', 'created_at', 'updated_at',
            ],
            'search_columns' => ['username'],
            'date_columns' => ['last_login_at', 'created_at', 'updated_at'],
        ],
    ],

];
