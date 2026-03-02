import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const defaultProduct = {
  category_id: '',
  variant_group_id: '',
  code: '',
  name: '',
  description: '',
  price: 0,
  stock: 0,
  is_installable: false,
  installation_price: '',
  is_extra_keys_available: false,
  extra_key_unit_price: '',
  is_featured: false,
  is_trending: false,
  is_active: true,
};

function groupFeaturesByType(features) {
  const byName = new Map();
  for (const f of features) {
    const name = f.feature_name || '';
    if (!byName.has(name)) byName.set(name, []);
    byName.get(name).push(f);
  }
  return Array.from(byName.entries()).map(([name, list]) => ({ name, list }));
}

export default function AdminProductForm({ product = null, categories = [], variantGroups = [], features = [], onSubmit, loading = false, error = '' }) {
  const { t } = useTranslation();
  const [form, setForm] = useState(() => {
    if (product) {
      return {
        category_id: product.category_id ?? '',
        variant_group_id: product.variant_group_id ?? '',
        code: product.code ?? '',
        name: product.name ?? '',
        description: product.description ?? '',
        price: product.price ?? 0,
        stock: product.stock ?? 0,
        is_installable: product.is_installable ?? false,
        installation_price: product.installation_price ?? '',
        is_extra_keys_available: product.is_extra_keys_available ?? false,
        extra_key_unit_price: product.extra_key_unit_price ?? '',
        is_featured: product.is_featured ?? false,
        is_trending: product.is_trending ?? false,
        is_active: product.is_active ?? true,
      };
    }
    return { ...defaultProduct };
  });
  const [featureIds, setFeatureIds] = useState(() => (product?.features ?? []).map((f) => f.id) ?? []);
  const [featuresExpanded, setFeaturesExpanded] = useState({});

  const update = (key, value) => setForm((prev) => ({ ...prev, [key]: value }));

  const toggleFeaturesGroup = (name) => {
    setFeaturesExpanded((prev) => ({ ...prev, [name]: !prev[name] }));
  };

  const toggleFeature = (id) => {
    setFeatureIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const payload = {
      category_id: Number(form.category_id),
      variant_group_id: form.variant_group_id ? Number(form.variant_group_id) : null,
      code: form.code || null,
      name: form.name,
      description: form.description || null,
      price: Number(form.price),
      stock: Number(form.stock),
      is_installable: !!form.is_installable,
      installation_price: form.installation_price !== '' ? Number(form.installation_price) : null,
      is_extra_keys_available: !!form.is_extra_keys_available,
      extra_key_unit_price: form.extra_key_unit_price !== '' ? Number(form.extra_key_unit_price) : null,
      is_featured: !!form.is_featured,
      is_trending: !!form.is_trending,
      is_active: !!form.is_active,
      feature_ids: featureIds,
    };
    onSubmit(payload);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <div role="alert" className="alert alert-error text-sm">
          {error}
        </div>
      )}

      <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
        <label className="form-field">
          <span className="form-label">{t('admin.products.category')} *</span>
          <select
            className="select select-bordered w-full"
            value={form.category_id}
            onChange={(e) => update('category_id', e.target.value)}
            required
            aria-label={t('admin.products.category')}
          >
            <option value="">{t('shop.categories.all')}</option>
            {categories.map((c) => (
              <option key={c.id} value={c.id}>
                {c.name}
              </option>
            ))}
          </select>
        </label>
        <label className="form-field">
          <span className="form-label">{t('admin.products.code')}</span>
          <input
            type="text"
            className="input input-bordered w-full"
            value={form.code}
            onChange={(e) => update('code', e.target.value)}
            aria-label={t('admin.products.code')}
          />
        </label>
        {variantGroups.length > 0 && (
          <label className="form-field md:col-span-2">
            <span className="form-label">{t('admin.products.variant_group')}</span>
            <select
              className="select select-bordered w-full"
              value={form.variant_group_id}
              onChange={(e) => update('variant_group_id', e.target.value)}
              aria-label={t('admin.products.variant_group')}
            >
              <option value=""></option>
              {variantGroups.map((g) => (
                <option key={g.id} value={g.id}>
                  #{g.id} ({g.products_count ?? g.products?.length ?? 0} {t('admin.products.name').toLowerCase()})
                </option>
              ))}
            </select>
          </label>
        )}
      </div>

      <label className="form-field">
        <span className="form-label">{t('admin.products.name')} *</span>
        <input
          type="text"
          className="input input-bordered w-full"
          value={form.name}
          onChange={(e) => update('name', e.target.value)}
          required
          aria-label={t('admin.products.name')}
        />
      </label>

      <label className="form-field">
        <span className="form-label">{t('admin.products.description')}</span>
        <textarea
          className="textarea textarea-bordered w-full"
          rows={4}
          value={form.description}
          onChange={(e) => update('description', e.target.value)}
          aria-label={t('admin.products.description')}
        />
      </label>

      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <label className="form-field">
          <span className="form-label">{t('admin.products.price')} (€) *</span>
          <input
            type="number"
            step="0.01"
            min="0"
            className="input input-bordered w-full"
            value={form.price}
            onChange={(e) => update('price', e.target.value)}
            required
            aria-label={t('admin.products.price')}
          />
        </label>
        <label className="form-field">
          <span className="form-label">{t('admin.products.stock')} *</span>
          <input
            type="number"
            min="0"
            className="input input-bordered w-full"
            value={form.stock}
            onChange={(e) => update('stock', e.target.value)}
            required
            aria-label={t('admin.products.stock')}
          />
        </label>
      </div>

      {features.length > 0 && (
        <div className="form-field">
          <span className="form-label">{t('admin.products.features')}</span>
          <div className="space-y-4 rounded-box border border-base-300 bg-base-200/50 p-4">
            {groupFeaturesByType(features).map(({ name, list }) => {
              const key = name || 'unnamed';
              const isExpanded = featuresExpanded[key];
              const visibleCount = 3;
              const visible = isExpanded ? list : list.slice(0, visibleCount);
              const hasMore = list.length > visibleCount;
              const hiddenCount = list.length - visibleCount;
              return (
                <div key={key}>
                  <p className="text-sm font-medium text-base-content/80 mb-2">{name || ''}</p>
                  <div className="flex flex-wrap gap-2 items-center">
                    {visible.map((f) => (
                      <label key={f.id} className="label cursor-pointer gap-2 bg-base-100 px-3 py-2 rounded-lg border border-base-300">
                        <input
                          type="checkbox"
                          className="checkbox checkbox-sm"
                          checked={featureIds.includes(f.id)}
                          onChange={() => toggleFeature(f.id)}
                          aria-label={f.value}
                        />
                        <span className="label-text text-sm">{f.value}</span>
                      </label>
                    ))}
                    {hasMore && (
                      <button
                        type="button"
                        className="btn btn-ghost btn-sm btn-square shrink-0"
                        onClick={() => toggleFeaturesGroup(key)}
                        aria-expanded={isExpanded}
                        aria-label={isExpanded ? t('common.close') : undefined}
                        title={isExpanded ? t('common.close') : `+${hiddenCount}`}
                      >
                        {isExpanded ? '−' : `+${hiddenCount}`}
                      </button>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      )}

      <div className="flex flex-wrap gap-4">
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_active}
            onChange={(e) => update('is_active', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_active')}</span>
        </label>
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_featured}
            onChange={(e) => update('is_featured', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_featured')}</span>
        </label>
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_trending}
            onChange={(e) => update('is_trending', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_trending')}</span>
        </label>
      </div>

      <div className="divider" />

      <div className="flex w-full flex-col gap-2">
        <div className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_installable}
            onChange={(e) => update('is_installable', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_installable')}</span>
        </div>
        {form.is_installable && (
          <label className="form-field max-w-xs">
            <span className="form-label">{t('admin.products.installation_price')} (€)</span>
            <input
              type="number"
              step="0.01"
              min="0"
              className="input input-bordered w-full"
              value={form.installation_price}
              onChange={(e) => update('installation_price', e.target.value)}
              aria-label={t('admin.products.installation_price')}
            />
          </label>
        )}
      </div>

      <div className="flex w-full flex-col gap-2">
        <div className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_extra_keys_available}
            onChange={(e) => update('is_extra_keys_available', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_extra_keys_available')}</span>
        </div>
        {form.is_extra_keys_available && (
          <label className="form-field max-w-xs">
            <span className="form-label">{t('admin.products.extra_key_unit_price')} (€)</span>
            <input
              type="number"
              step="0.01"
              min="0"
              className="input input-bordered w-full"
              value={form.extra_key_unit_price}
              onChange={(e) => update('extra_key_unit_price', e.target.value)}
              aria-label={t('admin.products.extra_key_unit_price')}
            />
          </label>
        )}
      </div>

      <div className="flex justify-between gap-2 pt-4">
        <Link to="/admin/products" className="btn btn-ghost">
          {t('common.back')}
        </Link>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
