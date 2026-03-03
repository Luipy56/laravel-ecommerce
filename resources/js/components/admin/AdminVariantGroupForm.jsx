import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

function groupProductsByCategory(products) {
  const byCategory = new Map();
  for (const p of products) {
    const name = p.category?.name ?? '';
    if (!byCategory.has(name)) byCategory.set(name, []);
    byCategory.get(name).push(p);
  }
  return Array.from(byCategory.entries())
    .map(([name, list]) => ({ name, list }))
    .sort((a, b) => (a.name || '').localeCompare(b.name || ''));
}

export default function AdminVariantGroupForm({ group = null, products = [], onSubmit, loading = false, error = '' }) {
  const { t } = useTranslation();
  const [name, setName] = useState(group?.name ?? '');
  const [productIds, setProductIds] = useState(() => group?.product_ids ?? []);

  const toggleProduct = (id) => {
    setProductIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({ name: name.trim() || null, product_ids: productIds });
  };

  const grouped = groupProductsByCategory(products);

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <div role="alert" className="alert alert-error text-sm">
          {error}
        </div>
      )}

      <label className="form-field">
        <span className="form-label">{t('admin.variant_groups.name')}</span>
        <input
          type="text"
          className="input input-bordered w-full"
          value={name}
          onChange={(e) => setName(e.target.value)}
          aria-label={t('admin.variant_groups.name')}
        />
      </label>

      {products.length > 0 ? (
        <div className="form-field">
          <span className="form-label">{t('admin.variant_groups.products_in_group')}</span>
          <p className="text-sm text-base-content/70 mb-2">
            {t('admin.variant_groups.products_help')}
          </p>
          <div className="space-y-4 rounded-box border border-base-300 bg-base-200/50 p-4">
            {grouped.map(({ name: catName, list }) => (
              <div key={catName || 'unnamed'}>
                <p className="text-sm font-medium text-base-content/80 mb-2">{catName || ''}</p>
                <div className="flex flex-wrap gap-2">
                  {list.map((p) => (
                    <label
                      key={p.id}
                      className="label cursor-pointer gap-2 bg-base-100 px-3 py-2 rounded-lg border border-base-300"
                    >
                      <input
                        type="checkbox"
                        className="checkbox checkbox-sm"
                        checked={productIds.includes(p.id)}
                        onChange={() => toggleProduct(p.id)}
                        aria-label={p.name}
                      />
                      <span className="label-text text-sm">{p.name}{p.code ? ` (${p.code})` : ''}</span>
                    </label>
                  ))}
                </div>
              </div>
            ))}
          </div>
        </div>
      ) : (
        <p className="text-base-content/70">{t('admin.products.no_products')}</p>
      )}

      <div className="flex justify-between gap-2 pt-4">
        <Link to="/admin/variant-groups" className="btn btn-ghost">
          {t('common.back')}
        </Link>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
