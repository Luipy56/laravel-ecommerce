/**
 * Admin list column ids and i18n labels per table.
 *
 * Must stay in sync with `config/admin_index_columns.php` (same table ids, same column ids, same order).
 * If the PHP registry gains a column, add it here and wire the `<th>` / `<td>` on the matching admin list page.
 */

export const ADMIN_INDEX_COLUMN_REGISTRY = {
  products: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'code', labelKey: 'admin.products.code' },
    { id: 'name', labelKey: 'admin.products.name' },
    { id: 'category', labelKey: 'admin.products.category' },
    { id: 'price', labelKey: 'admin.products.price' },
    { id: 'discount_percent', labelKey: 'admin.products.discount_percent' },
    { id: 'stock', labelKey: 'admin.products.stock' },
    { id: 'is_featured', labelKey: 'admin.products.is_featured' },
    { id: 'is_trending', labelKey: 'admin.products.is_trending' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  categories: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'code', labelKey: 'admin.products.code' },
    { id: 'name', labelKey: 'admin.products.name' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  clients: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'email', labelKey: 'admin.clients.email' },
    { id: 'type', labelKey: 'admin.clients.filter_type' },
    { id: 'identification', labelKey: 'admin.clients.identification' },
    { id: 'primary_contact', labelKey: 'admin.clients.primary_contact' },
    { id: 'contacts_count', labelKey: 'admin.clients.contacts_count' },
    { id: 'addresses_count', labelKey: 'admin.clients.addresses_count' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  variant_groups: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'group_label', labelKey: 'admin.variant_groups.group_label' },
    { id: 'products_in_group', labelKey: 'admin.variant_groups.products_in_group' },
  ],
  orders: [
    { id: 'id', labelKey: 'admin.orders.id' },
    { id: 'kind', labelKey: 'admin.orders.kind' },
    { id: 'client', labelKey: 'admin.orders.client' },
    { id: 'order_date', labelKey: 'admin.orders.order_date' },
    { id: 'lines_count', labelKey: 'admin.orders.lines_count' },
    { id: 'total', labelKey: 'admin.orders.total' },
    { id: 'status', labelKey: 'admin.orders.status' },
    { id: 'installation_requested', labelKey: 'admin.orders.column_installation_requested' },
    { id: 'installation_status', labelKey: 'admin.orders.installation_status_label' },
    { id: 'installation_price', labelKey: 'admin.orders.column_installation_price' },
    { id: 'shipping_date', labelKey: 'admin.orders.shipping_date' },
    { id: 'created_at', labelKey: 'admin.orders.created_at' },
    { id: 'updated_at', labelKey: 'admin.orders.updated_at' },
  ],
  packs: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'name', labelKey: 'admin.products.name' },
    { id: 'price', labelKey: 'admin.products.price' },
    { id: 'products_in_pack', labelKey: 'admin.packs.products_in_pack' },
    { id: 'is_trending', labelKey: 'admin.products.is_trending' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  admins: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'username', labelKey: 'admin.admins.username' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
    { id: 'last_login_at', labelKey: 'admin.admins.last_login_at' },
    { id: 'created_at', labelKey: 'admin.admins.created_at' },
  ],
  personalized_solutions: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'email', labelKey: 'admin.personalized_solutions.email' },
    { id: 'phone', labelKey: 'admin.personalized_solutions.phone' },
    { id: 'problem_description', labelKey: 'admin.personalized_solutions.problem_description' },
    { id: 'status', labelKey: 'admin.personalized_solutions.status' },
    { id: 'created_at', labelKey: 'admin.personalized_solutions.created_at' },
    { id: 'client_login_email', labelKey: 'admin.personalized_solutions.client_login_email' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  feature_types: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'name', labelKey: 'admin.features.type' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
  features: [
    { id: 'id', labelKey: 'admin.common.column_id' },
    { id: 'feature_name_id', labelKey: 'admin.features.feature_name_id' },
    { id: 'feature_name', labelKey: 'admin.features.type' },
    { id: 'value', labelKey: 'admin.features.value' },
    { id: 'is_active', labelKey: 'admin.products.is_active' },
  ],
};

/** Stable section order on the settings page. */
export const ADMIN_INDEX_TABLE_ORDER = [
  'products',
  'categories',
  'clients',
  'variant_groups',
  'orders',
  'packs',
  'admins',
  'personalized_solutions',
  'feature_types',
  'features',
];

export const ADMIN_INDEX_TABLE_META = {
  products: { titleKey: 'admin.products.title' },
  categories: { titleKey: 'admin.categories.title' },
  clients: { titleKey: 'admin.clients.title' },
  variant_groups: { titleKey: 'admin.variant_groups.title' },
  orders: { titleKey: 'admin.orders.title' },
  packs: { titleKey: 'admin.packs.title' },
  admins: { titleKey: 'admin.admins.title' },
  personalized_solutions: { titleKey: 'admin.personalized_solutions.title' },
  feature_types: { titleKey: 'admin.feature_types.title' },
  features: { titleKey: 'admin.features.section_values' },
};
