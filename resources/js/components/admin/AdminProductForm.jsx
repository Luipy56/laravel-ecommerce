import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { adminProductPayloadSchema, parseWithZod } from '../../validation';

const defaultProduct = {
  category_id: '',
  variant_group_id: '',
  code: '',
  name: '',
  description: '',
  price: 0,
  discount_percent: '',
  purchase_price: '',
  stock: 0,
  weight_kg: '',
  is_double_clutch: false,
  has_card: false,
  security_level: '',
  competitor_url: '',
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

const ACCEPTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_IMAGE_SIZE_MB = 10;

export default function AdminProductForm({
  product = null,
  categories = [],
  variantGroups = [],
  features = [],
  onSubmit,
  onAddImages = null,
  onRemoveImage = null,
  loading = false,
  imagesLoading = false,
  error = '',
}) {
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
        discount_percent: product.discount_percent != null && product.discount_percent !== '' ? String(product.discount_percent) : '',
        purchase_price: product.purchase_price ?? '',
        stock: product.stock ?? 0,
        weight_kg: product.weight_kg ?? '',
        is_double_clutch: product.is_double_clutch ?? false,
        has_card: product.has_card ?? false,
        security_level: product.security_level ?? '',
        competitor_url: product.competitor_url ?? '',
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
  const [featuresSectionOpen, setFeaturesSectionOpen] = useState(false);
  const [pendingFiles, setPendingFiles] = useState([]);
  const [clientError, setClientError] = useState('');

  const update = (key, value) => {
    setClientError('');
    setForm((prev) => ({ ...prev, [key]: value }));
  };

  const handleFileSelect = (e) => {
    const files = Array.from(e.target.files || []).filter(
      (f) => ACCEPTED_IMAGE_TYPES.includes(f.type) && f.size <= MAX_IMAGE_SIZE_MB * 1024 * 1024
    );
    if (product && onAddImages) {
      if (files.length) onAddImages(files);
      e.target.value = '';
    } else {
      setPendingFiles((prev) => [...prev, ...files]);
      e.target.value = '';
    }
  };

  const removePendingFile = (index) => {
    setPendingFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const toggleFeaturesGroup = (name) => {
    setFeaturesExpanded((prev) => ({ ...prev, [name]: !prev[name] }));
  };

  const toggleFeature = (id) => {
    setFeatureIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    setClientError('');
    const payload = {
      category_id: Number(form.category_id),
      variant_group_id: form.variant_group_id ? Number(form.variant_group_id) : null,
      code: form.code || null,
      name: form.name,
      description: form.description || null,
      price: Number(form.price),
      discount_percent:
        form.discount_percent !== '' && form.discount_percent != null ? Number(form.discount_percent) : null,
      purchase_price: form.purchase_price !== '' && form.purchase_price != null ? Number(form.purchase_price) : null,
      stock: Number(form.stock),
      weight_kg: form.weight_kg !== '' && form.weight_kg != null ? Number(form.weight_kg) : null,
      is_double_clutch: !!form.is_double_clutch,
      has_card: !!form.has_card,
      security_level: form.security_level || null,
      competitor_url: form.competitor_url?.trim() ? form.competitor_url.trim() : null,
      is_extra_keys_available: !!form.is_extra_keys_available,
      extra_key_unit_price: form.extra_key_unit_price !== '' ? Number(form.extra_key_unit_price) : null,
      is_featured: !!form.is_featured,
      is_trending: !!form.is_trending,
      is_active: !!form.is_active,
      feature_ids: featureIds,
    };
    const parsed = parseWithZod(adminProductPayloadSchema, payload, t);
    if (!parsed.ok) {
      setClientError(parsed.firstError);
      return;
    }
    const toSubmit = { ...parsed.data };
    if (!product && pendingFiles.length > 0) toSubmit.files = pendingFiles;
    onSubmit(toSubmit);
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {(error || clientError) && (
        <div role="alert" className="alert alert-error text-sm">
          {clientError || error}
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
                  {g.name || `#${g.id}`} ({g.products_count ?? g.products?.length ?? 0} {t('admin.products.name').toLowerCase()})
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
          <span className="form-label">{t('admin.products.sale_price')} (€) *</span>
          <input
            type="number"
            step="0.01"
            min="0"
            className="input input-bordered w-full"
            value={form.price}
            onChange={(e) => update('price', e.target.value)}
            required
            aria-label={t('admin.products.sale_price')}
          />
        </label>
        <label className="form-field">
          <span className="form-label">{t('admin.products.discount_percent')}</span>
          <input
            type="number"
            step="0.01"
            min="0"
            max="100"
            className="input input-bordered w-full"
            value={form.discount_percent}
            onChange={(e) => update('discount_percent', e.target.value)}
            aria-label={t('admin.products.discount_percent')}
          />
          <span className="text-xs text-base-content/70">{t('admin.products.discount_percent_help')}</span>
        </label>
        <label className="form-field">
          <span className="form-label">{t('admin.products.purchase_price')} (€)</span>
          <input
            type="number"
            step="0.01"
            min="0"
            className="input input-bordered w-full"
            value={form.purchase_price}
            onChange={(e) => update('purchase_price', e.target.value)}
            aria-label={t('admin.products.purchase_price')}
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
        <label className="form-field">
          <span className="form-label">{t('admin.products.weight_kg')}</span>
          <input
            type="number"
            step="0.001"
            min="0"
            className="input input-bordered w-full"
            value={form.weight_kg}
            onChange={(e) => update('weight_kg', e.target.value)}
            aria-label={t('admin.products.weight_kg')}
          />
        </label>
        <label className="form-field sm:col-span-2">
          <span className="form-label">{t('admin.products.security_level')}</span>
          <select
            className="select select-bordered w-full max-w-md"
            value={form.security_level}
            onChange={(e) => update('security_level', e.target.value)}
            aria-label={t('admin.products.security_level')}
          >
            <option value="">{t('admin.products.security_level_none')}</option>
            <option value="standard">{t('admin.products.security_level_standard')}</option>
            <option value="high">{t('admin.products.security_level_high')}</option>
            <option value="very_high">{t('admin.products.security_level_very_high')}</option>
          </select>
        </label>
        <label className="form-field sm:col-span-2">
          <span className="form-label">{t('admin.products.competitor_url')}</span>
          <input
            type="text"
            inputMode="url"
            className="input input-bordered w-full"
            value={form.competitor_url}
            onChange={(e) => update('competitor_url', e.target.value)}
            placeholder="https://"
            aria-label={t('admin.products.competitor_url')}
          />
        </label>
      </div>

      <div className="flex flex-wrap gap-4">
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.is_double_clutch}
            onChange={(e) => update('is_double_clutch', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_double_clutch')}</span>
        </label>
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={form.has_card}
            onChange={(e) => update('has_card', e.target.checked)}
          />
          <span className="label-text">{t('admin.products.has_card')}</span>
        </label>
      </div>

      <div className="form-field">
        <span className="form-label">{t('admin.products.images')}</span>
        <div className="space-y-3 rounded-box border border-base-300 bg-base-200/50 p-4">
          {product?.images?.length > 0 && (
            <div className="flex flex-wrap gap-3">
              {product.images.map((img) => (
                <div key={img.id} className="relative group">
                  <img
                    src={img.url}
                    alt={t('admin.products.thumbnail_alt')}
                    className="size-20 object-cover rounded-lg border border-base-300"
                  />
                  {onRemoveImage && (
                    <button
                      type="button"
                      className="btn btn-error btn-xs btn-circle absolute -top-1 -right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                      onClick={() => onRemoveImage(img.id)}
                      disabled={imagesLoading}
                      aria-label={t('admin.products.remove_image')}
                    >
                      ×
                    </button>
                  )}
                </div>
              ))}
            </div>
          )}
          {!product && pendingFiles.length > 0 && (
            <div className="flex flex-wrap gap-3">
              {pendingFiles.map((file, i) => (
                <div key={i} className="relative group">
                  <img
                    src={URL.createObjectURL(file)}
                    alt={t('admin.products.thumbnail_pending_alt')}
                    className="size-20 object-cover rounded-lg border border-base-300"
                  />
                  <button
                    type="button"
                    className="btn btn-error btn-xs btn-circle absolute -top-1 -right-1 opacity-0 group-hover:opacity-100 transition-opacity"
                    onClick={() => removePendingFile(i)}
                    aria-label={t('admin.products.remove_image')}
                  >
                    ×
                  </button>
                </div>
              ))}
            </div>
          )}
          <label className="cursor-pointer block">
              <input
                type="file"
                className="file-input file-input-bordered file-input-sm w-full max-w-xs"
                accept={ACCEPTED_IMAGE_TYPES.join(',')}
                multiple
                onChange={handleFileSelect}
                disabled={imagesLoading}
                aria-label={t('admin.products.add_images')}
              />
              <span className="ml-2 text-sm text-base-content/70">{t('admin.products.add_images_hint')}</span>
            </label>
        </div>
      </div>

      {features.length > 0 && (
        <div className="form-field">
          <div className={`collapse collapse-arrow rounded-box border border-base-300 bg-base-200/50 ${featuresSectionOpen ? 'collapse-open' : ''}`}>
            <input
              type="checkbox"
              id="product-features-collapse"
              checked={featuresSectionOpen}
              onChange={(e) => setFeaturesSectionOpen(e.target.checked)}
              aria-label={t('admin.products.features')}
              className="sr-only"
            />
            <label htmlFor="product-features-collapse" className="collapse-title min-h-0 py-3 pr-10 font-medium cursor-pointer">
              {t('admin.products.features')}
            </label>
            <div className="collapse-content">
              <div className="space-y-4 px-4 pb-4 pt-0">
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
                            onClick={(e) => {
                              e.stopPropagation();
                              toggleFeaturesGroup(key);
                            }}
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
