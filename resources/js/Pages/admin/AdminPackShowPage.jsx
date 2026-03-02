import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminPackShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [pack, setPack] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchPack = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/packs/${id}`);
      if (data.success) setPack(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchPack();
  }, [fetchPack]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/packs" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !pack) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{pack.name}</PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/packs" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/packs/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div><dt className="text-sm text-base-content/70">{t('admin.products.name')}</dt><dd>{pack.name}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.description')}</dt><dd>{pack.description}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.price')} (€)</dt><dd>{pack.price != null ? Number(pack.price).toFixed(2) : ''}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_trending')}</dt><dd>{pack.is_trending ? t('common.yes') : t('common.no')}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_active')}</dt><dd>{pack.is_active ? t('common.yes') : t('common.no')}</dd></div>
          </dl>
          {pack.items?.length > 0 && (
            <div className="mt-4 pt-4 border-t border-base-300">
              <dt className="text-sm text-base-content/70 mb-2">{t('admin.packs.products_in_pack')}</dt>
              <dd>
                <ul className="list-disc list-inside space-y-1">
                  {pack.items.map((i) => (
                    <li key={i.product_id}>{i.product?.name}{i.product?.code ? ` (${i.product.code})` : ''}</li>
                  ))}
                </ul>
              </dd>
            </div>
          )}
        </div>
      </div>

      {pack.images?.length > 0 && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">{t('admin.products.images')}</h2>
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
              {pack.images.map((img) => (
                <figure key={img.id} className="rounded-lg overflow-hidden border border-base-300 bg-base-200">
                  <img src={img.url} alt="" className="w-full h-40 object-cover" />
                </figure>
              ))}
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
