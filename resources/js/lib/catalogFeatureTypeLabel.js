/**
 * Resolves a human-readable feature dimension label for storefront and admin.
 * Prefers API `type` / `feature_name`; then i18n `shop.feature_label.<code>`; then a simple code fallback.
 *
 * @param {{ type?: string | null, feature_name?: string | null, name?: string | null, feature_name_code?: string | null }} item
 * @param {(key: string, opts?: Record<string, unknown>) => string} t
 * @returns {string}
 */
export function catalogFeatureTypeLabel(item, t) {
  const raw = String(item?.type ?? item?.feature_name ?? item?.name ?? '').trim();
  if (raw) return raw;
  const code = item?.feature_name_code != null ? String(item.feature_name_code).trim() : '';
  if (!code) return '';
  if (typeof t !== 'function') return code.replace(/_/g, ' ');
  const i18nKey = `shop.feature_label.${code}`;
  const translated = t(i18nKey, { defaultValue: '' });
  if (translated && translated !== i18nKey) return translated;
  return code.replace(/_/g, ' ');
}
