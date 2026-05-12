import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const ACCEPTED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
const MAX_IMAGE_SIZE_MB = 10;

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

export default function AdminPackForm({ pack = null, products = [], onSubmit, onAddImages = null, onRemoveImage = null, loading = false, imagesLoading = false, error = '' }) {
  const { t } = useTranslation();
  const [name, setName] = useState(pack?.name ?? '');
  const [description, setDescription] = useState(pack?.description ?? '');
  const [price, setPrice] = useState(pack?.price ?? 0);
  const [isTrending, setIsTrending] = useState(pack?.is_trending ?? false);
  const [isActive, setIsActive] = useState(pack?.is_active ?? true);
  const [containsKeys, setContainsKeys] = useState(pack?.contains_keys ?? false);
  const [productIds, setProductIds] = useState(() => pack?.product_ids ?? []);
  const [productsSectionOpen, setProductsSectionOpen] = useState(false);
  const [pendingFiles, setPendingFiles] = useState([]);

  const handleFileSelect = (e) => {
    const files = Array.from(e.target.files || []).filter(
      (f) => ACCEPTED_IMAGE_TYPES.includes(f.type) && f.size <= MAX_IMAGE_SIZE_MB * 1024 * 1024
    );
    if (pack && onAddImages && files.length) {
      onAddImages(files);
      e.target.value = '';
    } else if (!pack) {
      setPendingFiles((prev) => [...prev, ...files]);
      e.target.value = '';
    }
    e.target.value = '';
  };

  const removePendingFile = (index) => {
    setPendingFiles((prev) => prev.filter((_, i) => i !== index));
  };

  const toggleProduct = (id) => {
    setProductIds((prev) => (prev.includes(id) ? prev.filter((x) => x !== id) : [...prev, id]));
  };

  const handleSubmit = (e) => {
    e.preventDefault();
    const payload = {
      name: name.trim(),
      description: description.trim() || null,
      price: Number(price),
      is_trending: !!isTrending,
      is_active: !!isActive,
      contains_keys: !!containsKeys,
      product_ids: productIds,
    };
    if (!pack && pendingFiles.length > 0) payload.files = pendingFiles;
    onSubmit(payload);
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
        <span className="form-label">{t('admin.products.name')} *</span>
        <input
          type="text"
          className="input input-bordered w-full"
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
          aria-label={t('admin.products.name')}
        />
      </label>

      <label className="form-field">
        <span className="form-label">{t('admin.products.description')}</span>
        <textarea
          className="textarea textarea-bordered w-full min-h-24"
          value={description}
          onChange={(e) => setDescription(e.target.value)}
          aria-label={t('admin.products.description')}
        />
      </label>

      <label className="form-field max-w-xs">
        <span className="form-label">{t('admin.products.price')} (€) *</span>
        <input
          type="number"
          step="0.01"
          min="0"
          className="input input-bordered w-full"
          value={price}
          onChange={(e) => setPrice(e.target.value)}
          required
          aria-label={t('admin.products.price')}
        />
      </label>

      <div className="form-field">
        <span className="form-label">{t('admin.products.images')}</span>
        <div className="space-y-3 rounded-box border border-base-300 bg-base-200/50 p-4">
          {pack?.images?.length > 0 && (
            <div className="flex flex-wrap gap-3">
              {pack.images.map((img) => (
                <div key={img.id} className="relative group">
                  <img
                    src={img.url}
                    alt={t('admin.packs.thumbnail_alt')}
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
          {!pack && pendingFiles.length > 0 && (
            <div className="flex flex-wrap gap-3">
              {pendingFiles.map((file, i) => (
                <div key={i} className="relative group">
                  <img
                    src={URL.createObjectURL(file)}
                    alt={t('admin.packs.thumbnail_pending_alt')}
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

      {products.length > 0 && (
        <div className="form-field">
          <div className={`collapse collapse-arrow rounded-box border border-base-300 bg-base-200/50 ${productsSectionOpen ? 'collapse-open' : ''}`}>
            <input
              type="checkbox"
              id="pack-products-collapse"
              checked={productsSectionOpen}
              onChange={(e) => setProductsSectionOpen(e.target.checked)}
              aria-label={t('admin.packs.products_in_pack')}
              className="sr-only"
            />
            <label htmlFor="pack-products-collapse" className="collapse-title min-h-0 py-3 pr-10 font-medium cursor-pointer">
              {t('admin.packs.products_in_pack')}
            </label>
            <div className="collapse-content">
              <div className="space-y-4 px-4 pb-4 pt-0">
                {grouped.map(({ name: catName, list }) => (
                  <div key={catName || 'unnamed'}>
                    <p className="text-sm font-medium text-base-content/80 mb-2">{catName || ''}</p>
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                      {list.map((p) => (
                        <div
                          key={p.id}
                          role="button"
                          tabIndex={0}
                          className="flex items-start gap-2 bg-base-100 px-3 py-2 rounded-lg border border-base-300 cursor-pointer select-none min-h-16"
                          onClick={() => toggleProduct(p.id)}
                          onDoubleClick={() => window.open(`/admin/products/${p.id}`, '_blank')}
                          onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggleProduct(p.id); } }}
                        >
                          <input
                            type="checkbox"
                            className="checkbox checkbox-sm mt-0.5 shrink-0"
                            checked={productIds.includes(p.id)}
                            readOnly
                            aria-label={p.name}
                          />
                          <div className="min-w-0 overflow-hidden">
                            <span className="text-sm font-semibold leading-tight block truncate" title={`${p.name}${p.code ? ` (${p.code})` : ''}`}>{p.name}{p.code ? ` (${p.code})` : ''}</span>
                            {p.price != null && <span className="text-xs text-base-content/50 block">{Number(p.price).toFixed(2)} €</span>}
                          </div>
                        </div>
                      ))}
                    </div>
                  </div>
                ))}
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
            checked={isActive}
            onChange={(e) => setIsActive(e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_active')}</span>
        </label>
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={isTrending}
            onChange={(e) => setIsTrending(e.target.checked)}
          />
          <span className="label-text">{t('admin.products.is_trending')}</span>
        </label>
        <label className="label cursor-pointer gap-2">
          <input
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={containsKeys}
            onChange={(e) => setContainsKeys(e.target.checked)}
          />
          <span className="label-text">{t('admin.packs.contains_keys')}</span>
        </label>
      </div>

      <div className="flex justify-between gap-2 pt-4">
        <Link to="/admin/packs" className="btn btn-ghost">
          {t('common.back')}
        </Link>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
