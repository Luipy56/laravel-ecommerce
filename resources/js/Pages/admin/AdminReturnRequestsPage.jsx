import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

const STATUS_VALUES = ['', 'pending_review', 'approved', 'rejected', 'refunded', 'cancelled'];

const STATUS_COLORS = {
  pending_review: 'badge-warning',
  approved: 'badge-info',
  refunded: 'badge-success',
  rejected: 'badge-error',
  cancelled: 'badge-neutral',
};

export default function AdminReturnRequestsPage() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();

  const [rmas, setRmas] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);

  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [statusFilter, setStatusFilter] = useState('pending_review');

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  const fetchRmas = useCallback(async (pageNum) => {
    setLoading(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('admin/return-requests', { params });
      if (data.success) {
        setRmas(data.data ?? []);
        setLastPage(data.meta?.last_page ?? 1);
        setTotal(data.meta?.total ?? 0);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setRmas([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, statusFilter]);

  useEffect(() => { setPage(1); }, [searchDebounce, statusFilter]);
  useEffect(() => { fetchRmas(page); }, [fetchRmas, page]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.returns.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
          placeholder={t('admin.returns.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.returns.search_placeholder')}
        />
        <select
          className="select select-bordered select-sm sm:select-md w-full sm:w-52 shrink-0"
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          aria-label={t('admin.returns.filter_status')}
        >
          <option value="">{t('admin.returns.status_all')}{total > 0 ? ` (${total})` : ''}</option>
          {STATUS_VALUES.filter(Boolean).map((s) => (
            <option key={s} value={s}>{t(`admin.returns.status_${s}`)}</option>
          ))}
        </select>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : rmas.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">{t('admin.returns.empty')}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th className="text-end tabular-nums">{t('admin.returns.col_id')}</th>
                  <th>{t('admin.returns.col_order')}</th>
                  <th>{t('admin.returns.col_client')}</th>
                  <th className="text-center">{t('admin.returns.col_status')}</th>
                  <th className="text-end">{t('admin.returns.col_created_at')}</th>
                </tr>
              </thead>
              <tbody>
                {rmas.map((rma) => (
                  <tr
                    key={rma.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200"
                    onClick={() => navigate(`/admin/returns/${rma.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/returns/${rma.id}`);
                      }
                    }}
                  >
                    <td className="text-end tabular-nums">{rma.id}</td>
                    <td className="tabular-nums">#{rma.order_id}</td>
                    <td className="max-w-[180px]">
                      <span className="block truncate text-sm" title={rma.client_login_email ?? ''}>
                        {rma.client_login_email ?? ''}
                      </span>
                    </td>
                    <td className="text-center">
                      <span className={`badge badge-outline badge-sm ${STATUS_COLORS[rma.status] ?? 'badge-ghost'}`}>
                        {t(`admin.returns.status_${rma.status}`)}
                      </span>
                    </td>
                    <td className="text-end tabular-nums text-xs text-base-content/60 whitespace-nowrap">
                      {rma.created_at
                        ? new Date(rma.created_at).toLocaleDateString(i18n.language, { year: 'numeric', month: '2-digit', day: '2-digit' })
                        : ''}
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {lastPage > 1 && (
        <div className="join flex justify-center">
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page <= 1 || loading}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('shop.pagination.prev')}
          </button>
          <span className="join-item flex items-center justify-center px-4 py-2 h-8 text-sm text-base-content bg-base-100 border border-base-300">
            {t('shop.pagination.page')} {page} {t('shop.pagination.of')} {lastPage}
          </span>
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page >= lastPage || loading}
            onClick={() => setPage((p) => Math.min(lastPage, p + 1))}
          >
            {t('shop.pagination.next')}
          </button>
        </div>
      )}
    </div>
  );
}
