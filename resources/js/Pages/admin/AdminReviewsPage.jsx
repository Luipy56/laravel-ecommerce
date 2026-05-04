import React, { useEffect, useRef, useState, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import StarRating from '../../components/StarRating';
import { loadAdminListFilters, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';

const REVIEWS_FILTERS_PAGE_ID = 'reviews';
const REVIEW_STATUS_VALUES = ['', 'pending', 'approved', 'rejected'];

function readPersistedReviewFilters() {
  const raw = loadAdminListFilters(REVIEWS_FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  const st = raw?.status;
  const statusFilter =
    typeof st === 'string' && REVIEW_STATUS_VALUES.includes(st) ? st : 'pending';
  return { search, statusFilter };
}

const STATUS_COLORS = {
  pending: 'badge-warning',
  approved: 'badge-success',
  rejected: 'badge-error',
};

export default function AdminReviewsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedReviewFilters();
  }
  const persisted = persistedRef.current;

  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [lastPage, setLastPage] = useState(1);
  const [total, setTotal] = useState(0);
  const [pendingCount, setPendingCount] = useState(0);

  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const [statusFilter, setStatusFilter] = useState(() => persisted.statusFilter);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  const fetchReviews = useCallback(async (pageNum) => {
    setLoading(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('admin/reviews', { params });
      if (data.success) {
        setReviews(data.data ?? []);
        setLastPage(data.meta?.last_page ?? 1);
        setTotal(data.meta?.total ?? 0);
        setPendingCount(data.meta?.pending_count ?? 0);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setReviews([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, statusFilter]);

  useEffect(() => {
    setPage(1);
  }, [searchDebounce, statusFilter]);

  useEffect(() => {
    fetchReviews(page);
  }, [fetchReviews, page]);

  useEffect(() => {
    saveAdminListFilters(REVIEWS_FILTERS_PAGE_ID, { search, status: statusFilter });
  }, [search, statusFilter]);

  const truncate = (s, n = 80) => (s && s.length > n ? `${s.slice(0, n)}…` : s ?? '');

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.reviews.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
          placeholder={t('admin.reviews.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.reviews.search_placeholder')}
        />
        <select
          className="select select-bordered select-sm sm:select-md w-full sm:w-44 shrink-0"
          value={statusFilter}
          onChange={(e) => setStatusFilter(e.target.value)}
          aria-label={t('admin.reviews.filter_status')}
        >
          <option value="">{t('shop.categories.all')}{total > 0 ? ` (${total})` : ''}</option>
          <option value="pending">{t('admin.reviews.status_pending')}{pendingCount > 0 ? ` (${pendingCount})` : ''}</option>
          <option value="approved">{t('admin.reviews.status_approved')}</option>
          <option value="rejected">{t('admin.reviews.status_rejected')}</option>
        </select>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : reviews.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">{t('admin.reviews.empty')}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th className="text-end tabular-nums">{t('admin.common.column_id')}</th>
                  <th>{t('admin.reviews.column_product')}</th>
                  <th>{t('admin.reviews.column_client')}</th>
                  <th className="text-center">{t('admin.reviews.column_rating')}</th>
                  <th>{t('admin.reviews.column_comment')}</th>
                  <th className="text-center">{t('admin.reviews.column_status')}</th>
                  <th className="text-end">{t('admin.common.column_date')}</th>
                </tr>
              </thead>
              <tbody>
                {reviews.map((row) => (
                  <tr
                    key={row.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200"
                    onClick={() => navigate(`/admin/reviews/${row.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/reviews/${row.id}`);
                      }
                    }}
                  >
                    <td className="text-end tabular-nums">{row.id}</td>
                    <td className="max-w-[140px]">
                      <span className="block truncate" title={row.product?.name ?? ''}>
                        {row.product?.name ?? ''}
                      </span>
                    </td>
                    <td className="max-w-[140px]">
                      <span className="block truncate text-sm" title={row.client_email ?? ''}>
                        {row.client_name || row.client_email || ''}
                      </span>
                    </td>
                    <td className="text-center">
                      <StarRating value={row.rating} size="xs" />
                    </td>
                    <td className="max-w-[200px] text-sm text-base-content/70">
                      <span className="block truncate">{truncate(row.comment)}</span>
                    </td>
                    <td className="text-center">
                      <span className={`badge badge-soft badge-sm ${STATUS_COLORS[row.status] ?? 'badge-ghost'}`}>
                        {t(`admin.reviews.status_${row.status}`)}
                      </span>
                    </td>
                    <td className="text-end tabular-nums text-xs text-base-content/60 whitespace-nowrap">
                      {row.created_at
                        ? new Date(row.created_at).toLocaleDateString('ca-ES', { year: 'numeric', month: '2-digit', day: '2-digit' })
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
