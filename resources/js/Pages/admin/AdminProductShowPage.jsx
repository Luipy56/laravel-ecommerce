import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { catalogFeatureTypeLabel } from '../../lib/catalogFeatureTypeLabel';

export default function AdminProductShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [product, setProduct] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchProduct = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/products/${id}`);
      if (data.success) setProduct(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchProduct();
  }, [fetchProduct]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !product) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  const securityLabel =
    product.security_level === 'standard'
      ? t('admin.products.security_level_standard')
      : product.security_level === 'high'
        ? t('admin.products.security_level_high')
        : product.security_level === 'very_high'
          ? t('admin.products.security_level_very_high')
          : '';

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{product.name}</PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/products" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/products/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt className="text-sm text-base-content/70">{t('admin.products.category')}</dt><dd>{product.category?.name}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.code')}</dt><dd>{product.code}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.name')}</dt><dd>{product.name}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.description')}</dt><dd>{product.description}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.sale_price')} (€)</dt><dd>{product.price != null ? Number(product.price).toFixed(2) : ''}</dd></div>
            <div>
              <dt className="text-sm text-base-content/70">{t('admin.products.discount_percent')}</dt>
              <dd>
                {product.discount_percent != null &&
                product.discount_percent !== '' &&
                Number(product.discount_percent) > 0
                  ? `${Number(product.discount_percent)}%`
                  : ''}
              </dd>
            </div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.purchase_price')} (€)</dt><dd>{product.purchase_price != null ? Number(product.purchase_price).toFixed(2) : ''}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.stock')}</dt><dd>{product.stock}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.weight_kg')}</dt><dd>{product.weight_kg != null ? Number(product.weight_kg).toFixed(3) : ''}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.security_level')}</dt><dd>{securityLabel}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.competitor_url')}</dt><dd>{product.competitor_url ? <a href={product.competitor_url} className="link link-primary" target="_blank" rel="noopener noreferrer">{product.competitor_url}</a> : ''}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_double_clutch')}</dt><dd>{product.is_double_clutch ? t('common.yes') : t('common.no')}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.has_card')}</dt><dd>{product.has_card ? t('common.yes') : t('common.no')}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_active')}</dt><dd>{product.is_active ? t('common.yes') : t('common.no')}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_featured')}</dt><dd>{product.is_featured ? t('common.yes') : t('common.no')}</dd></div>
            <div className="sm:col-span-2">
              <dt className="text-sm text-base-content/70">{t('admin.products.is_trending')}</dt>
              <dd>
                {product.is_trending ? t('common.yes') : t('common.no')}
                <p className="text-sm text-base-content/60 mt-1">{t('admin.products.is_trending_managed_by_settings')}</p>
              </dd>
            </div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_extra_keys_available')}</dt><dd>{product.is_extra_keys_available ? t('common.yes') : t('common.no')}</dd></div>
            {product.is_extra_keys_available && <div><dt className="text-sm text-base-content/70">{t('admin.products.extra_key_unit_price')} (€)</dt><dd>{product.extra_key_unit_price != null ? Number(product.extra_key_unit_price).toFixed(2) : ''}</dd></div>}
          </dl>
          {product.features?.length > 0 && (
            <div className="mt-4 pt-4 border-t border-base-300">
              <dt className="text-sm text-base-content/70 mb-1">{t('admin.products.features')}</dt>
              <dd className="flex flex-wrap gap-2">
                {product.features.map((f) => {
                  const typeLabel = catalogFeatureTypeLabel(
                    { type: f.type, feature_name_code: f.feature_name_code },
                    t
                  );
                  const text = typeLabel ? `${typeLabel}: ${f.value ?? ''}` : String(f.value ?? '');
                  return (
                    <span key={f.id} className="badge badge-outline badge-neutral">{text}</span>
                  );
                })}
              </dd>
            </div>
          )}
        </div>
      </div>

      {product.images?.length > 0 && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">{t('admin.products.images')}</h2>
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
              {product.images.map((img, i) => (
                <figure key={img.id} className="rounded-lg overflow-hidden border border-base-300 bg-base-200">
                  <img
                    src={img.url}
                    alt={t('admin.products.image_alt', { name: product.name || '', index: i + 1 })}
                    className="w-full h-40 object-cover"
                  />
                </figure>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
