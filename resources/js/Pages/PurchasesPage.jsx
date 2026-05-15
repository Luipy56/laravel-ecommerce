import '../scss/main_shop.scss'
import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';

const FALLBACK_IMAGE = '/images/dummy.jpg';

function purchaseDetailPath(line) {
  if (line.kind === 'product' && line.product_id) {
    return `/products/${line.product_id}`;
  }
  if (line.kind === 'pack' && line.pack_id) {
    return `/packs/${line.pack_id}`;
  }
  return null;
}

export default function PurchasesPage() {
  const { t } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const [rows, setRows] = useState([]);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, total: 0 });
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [inputDateFrom, setInputDateFrom] = useState('');
  const [inputDateTo, setInputDateTo] = useState('');
  const [appliedDateFrom, setAppliedDateFrom] = useState('');
  const [appliedDateTo, setAppliedDateTo] = useState('');

  useEffect(() => {
    if (!user) return undefined;
    setLoading(true);
    const params = { page };
    if (appliedDateFrom) params.date_from = appliedDateFrom;
    if (appliedDateTo) params.date_to = appliedDateTo;
    const req = api.get('purchases', { params });
    req
      .then((r) => {
        if (r.data.success) {
          setRows(r.data.data || []);
          setMeta(r.data.meta || { current_page: 1, last_page: 1, total: 0 });
        } else {
          setRows([]);
        }
      })
      .catch(() => setRows([]))
      .finally(() => setLoading(false));
    return undefined;
  }, [user, page, appliedDateFrom, appliedDateTo]);

  const handleApplyFilters = (e) => {
    e.preventDefault();
    setAppliedDateFrom(inputDateFrom);
    setAppliedDateTo(inputDateTo);
    setPage(1);
  };

  const handleClearFilters = () => {
    setInputDateFrom('');
    setInputDateTo('');
    setAppliedDateFrom('');
    setAppliedDateTo('');
    setPage(1);
  };

  if (authLoading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!user) {
    return (
      <div className="text-center py-8">
        <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>
      </div>
    );
  }

  if (loading && rows.length === 0) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }

  const showPagination = meta.last_page > 1;

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <PageTitle>{t('shop.purchases')}</PageTitle>

      <form onSubmit={handleApplyFilters} className="card bg-base-100 shadow border border-base-300 mb-6">
        <div className="card-body gap-4 py-4">
          <p className="text-sm text-base-content/80">{t('shop.purchases.filter_hint')}</p>
          <div className="flex flex-wrap items-end gap-3">
            <label className="form-control w-full min-w-0 max-w-xs">
              <span className="label-text">{t('shop.purchases.date_from')}</span>
              <input
                type="date"
                className="input input-bordered input-sm sm:input-md w-full"
                value={inputDateFrom}
                onChange={(e) => setInputDateFrom(e.target.value)}
              />
            </label>
            <label className="form-control w-full min-w-0 max-w-xs">
              <span className="label-text">{t('shop.purchases.date_to')}</span>
              <input
                type="date"
                className="input input-bordered input-sm sm:input-md w-full"
                value={inputDateTo}
                onChange={(e) => setInputDateTo(e.target.value)}
              />
            </label>
            <div className="flex flex-wrap gap-2">
              <button type="submit" className="btn btn-primary btn-sm sm:btn-md">
                {t('shop.purchases.apply')}
              </button>
              <button type="button" className="btn btn-ghost btn-sm sm:btn-md" onClick={handleClearFilters}>
                {t('shop.purchases.clear')}
              </button>
            </div>
          </div>
        </div>
      </form>

      {rows.length === 0 && !loading ? (
        <p>{t('shop.purchases.empty')}</p>
      ) : (
        <ul className="space-y-4">
          {rows.map((line) => {
            const detailPath = purchaseDetailPath(line);
            const dateStr = line.order_date
              ? new Date(line.order_date).toLocaleDateString()
              : '';
            const mainInner = (
              <>
                <figure className="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-lg overflow-hidden bg-base-200">
                  <img
                    src={line.image_url || FALLBACK_IMAGE}
                    alt=""
                    className="object-cover w-full h-full"
                    onError={(e) => {
                      e.target.onerror = null;
                      e.target.src = FALLBACK_IMAGE;
                    }}
                  />
                </figure>
                <div className="min-w-0 flex-1">
                  <div className="flex flex-wrap items-center gap-2">
                    {line.kind === 'pack' && (
                      <span className="badge badge-primary badge-sm">{t('shop.pack')}</span>
                    )}
                    <span className="font-semibold break-words">{line.name}</span>
                  </div>
                  <p className="text-sm text-base-content/70 mt-1">
                    {dateStr}
                    {line.quantity != null ? ` · ${t('shop.quantity')}: ${line.quantity}` : ''}
                  </p>
                </div>
              </>
            );

            return (
              <li key={line.line_id} className="card bg-base-100 shadow border border-base-200">
                <div className="card-body flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                  {detailPath ? (
                    <Link
                      to={detailPath}
                      className="flex gap-4 min-w-0 flex-1 rounded-lg outline-offset-2 hover:bg-base-200/50 -m-2 p-2 sm:m-0 sm:p-0 sm:hover:bg-transparent focus-visible:ring focus-visible:ring-primary/40"
                      aria-label={`${line.name || ''} ${dateStr}`}
                    >
                      {mainInner}
                    </Link>
                  ) : (
                    <div className="flex gap-4 min-w-0 flex-1">
                      {mainInner}
                    </div>
                  )}
                  <div className="flex flex-col gap-2 sm:items-end shrink-0 border-t border-base-200 pt-3 sm:border-t-0 sm:pt-0">
                    <span className="font-semibold tabular-nums text-end">
                      {Number(line.line_total ?? 0).toFixed(2)} €
                    </span>
                    {line.order_id != null && (
                      <Link
                        to={`/orders/${line.order_id}`}
                        className="btn btn-ghost btn-sm w-full sm:w-auto"
                      >
                        {t('shop.purchases.view_order')}
                      </Link>
                    )}
                  </div>
                </div>
              </li>
            );
          })}
        </ul>
      )}

      {showPagination && (
        <div className="join flex justify-center mt-8">
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('shop.pagination.prev')}
          </button>
          <span className="join-item flex items-center justify-center px-4 py-2 h-8 text-sm text-base-content bg-base-100 border border-base-300">
            {t('shop.pagination.page')} {meta.current_page} {t('shop.pagination.of')} {meta.last_page}
          </span>
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page >= meta.last_page}
            onClick={() => setPage((p) => p + 1)}
          >
            {t('shop.pagination.next')}
          </button>
        </div>
      )}
    </div>
  );
}
