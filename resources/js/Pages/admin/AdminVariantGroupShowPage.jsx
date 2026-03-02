import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

const PLACEHOLDER_IMAGE = '/images/dummy.jpg';

export default function AdminVariantGroupShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [group, setGroup] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchGroup = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/variant-groups/${id}`);
      if (data.success) setGroup(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchGroup();
  }, [fetchGroup]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !group) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  const products = group.products ?? [];

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.variant_groups.group_label')} #{group.id}</PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/variant-groups" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/variant-groups/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">
            {t('admin.variant_groups.products_in_group')} ({products.length})
          </h2>
          {!products.length ? (
            <p className="text-base-content/70">{t('admin.products.no_products')}</p>
          ) : (
            <>
              {/* Desktop: table */}
              <div className="overflow-x-auto hidden sm:block">
                <table className="table table-zebra table-sm">
                  <thead>
                    <tr>
                      <th className="w-14" aria-label={t('admin.products.images')} />
                      <th>{t('admin.products.name')}</th>
                      <th>{t('admin.products.code')}</th>
                      <th className="text-end">{t('admin.products.price')}</th>
                      <th className="text-end">{t('admin.products.stock')}</th>
                      <th>{t('admin.products.category')}</th>
                      <th>{t('admin.products.is_active')}</th>
                      <th className="w-24" />
                    </tr>
                  </thead>
                  <tbody>
                    {products.map((p) => (
                      <tr
                        key={p.id}
                        role="button"
                        tabIndex={0}
                        onClick={() => navigate(`/admin/products/${p.id}`)}
                        onKeyDown={(e) => {
                          if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            navigate(`/admin/products/${p.id}`);
                          }
                        }}
                        className="cursor-pointer hover:bg-base-200/50"
                      >
                        <td>
                          <div className="avatar">
                            <div className="mask mask-squircle w-10 h-10 bg-base-300">
                              <img
                                src={p.image_url || PLACEHOLDER_IMAGE}
                                alt=""
                                className="object-cover w-full h-full"
                              />
                            </div>
                          </div>
                        </td>
                        <td className="font-medium">{p.name}</td>
                        <td className="text-base-content/70">{p.code}</td>
                        <td className="text-end tabular-nums">
                          {p.price != null ? Number(p.price).toFixed(2) : ''} €
                        </td>
                        <td className="text-end tabular-nums">{p.stock}</td>
                        <td className="text-base-content/70">{p.category?.name}</td>
                        <td>
                          <span className={`badge badge-sm ${p.is_active ? 'badge-success' : 'badge-ghost'}`}>
                            {p.is_active ? t('common.yes') : t('common.no')}
                          </span>
                        </td>
                        <td onClick={(e) => e.stopPropagation()}>
                          <Link
                            to={`/admin/products/${p.id}/edit`}
                            className="btn btn-ghost btn-xs"
                            aria-label={t('admin.products.edit')}
                          >
                            {t('common.edit')}
                          </Link>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              {/* Mobile: cards */}
              <div className="flex flex-col gap-3 sm:hidden">
                {products.map((p) => (
                  <Link
                    key={p.id}
                    to={`/admin/products/${p.id}`}
                    className="card card-border bg-base-200/50 hover:bg-base-200 border-base-300"
                  >
                    <div className="card-body p-4 flex-row items-center gap-3">
                      <div className="avatar shrink-0">
                        <div className="mask mask-squircle w-14 h-14 bg-base-300">
                          <img
                            src={p.image_url || PLACEHOLDER_IMAGE}
                            alt=""
                            className="object-cover w-full h-full"
                          />
                        </div>
                      </div>
                      <div className="min-w-0 flex-1">
                        <p className="font-medium truncate">{p.name}</p>
                        <p className="text-sm text-base-content/70">
                          {p.code}
                          {p.category?.name && ` · ${p.category.name}`}
                        </p>
                        <div className="flex flex-wrap items-center gap-2 mt-1">
                          {p.price != null && (
                            <span className="text-sm font-medium tabular-nums">
                              {Number(p.price).toFixed(2)} €
                            </span>
                          )}
                          <span className="text-sm text-base-content/70">
                            {t('admin.products.stock')}: {p.stock}
                          </span>
                          <span className={`badge badge-sm ${p.is_active ? 'badge-success' : 'badge-ghost'}`}>
                            {p.is_active ? t('common.yes') : t('common.no')}
                          </span>
                        </div>
                      </div>
                      <span onClick={(e) => e.stopPropagation()}>
                        <Link
                          to={`/admin/products/${p.id}/edit`}
                          className="btn btn-ghost btn-sm shrink-0"
                          aria-label={t('admin.products.edit')}
                        >
                          {t('common.edit')}
                        </Link>
                      </span>
                    </div>
                  </Link>
                ))}
              </div>
            </>
          )}
        </div>
      </div>
    </div>
  );
}
