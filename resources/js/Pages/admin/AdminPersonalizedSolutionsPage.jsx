import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';
import { loadAdminListFilters, normalizedActiveTriState, normalizedPeriod, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';
import DecryptionWarningBanner from '../../components/admin/DecryptionWarningBanner';

const PS_FILTERS_PAGE_ID = 'personalized_solutions';

const STATUSES = ['pending_review', 'reviewed', 'client_contacted', 'rejected', 'completed'];

function readPersistedPsFilters() {
  const raw = loadAdminListFilters(PS_FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  const statusFilter =
    typeof raw?.status === 'string' && (raw.status === '' || STATUSES.includes(raw.status)) ? raw.status : '';
  const activeRaw = normalizedActiveTriState(raw?.active);
  const activeFilter = activeRaw === null ? '1' : activeRaw;
  const period = normalizedPeriod(raw?.period);
  const hasPersistedPeriod = period != null;
  return { search, statusFilter, activeFilter, period, hasPersistedPeriod };
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'pending_review': return 'badge-outline badge-warning';
    case 'reviewed': return 'badge-outline badge-info';
    case 'client_contacted': return 'badge-outline badge-success';
    case 'rejected': return 'badge-outline badge-error';
    case 'completed': return 'badge-outline badge-success';
    default: return 'badge-ghost';
  }
}

export default function AdminPersonalizedSolutionsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds } = useAdminIndexColumnVisibility('personalized_solutions');
  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedPsFilters();
  }
  const persisted = persistedRef.current;

  const [solutions, setSolutions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(false);
  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const [statusFilter, setStatusFilter] = useState(() => persisted.statusFilter);
  const [activeFilter, setActiveFilter] = useState(() => persisted.activeFilter);
  const [periodFilter, setPeriodFilter] = useState(() => persisted.period ?? 'week');
  const periodInitialized = true;
  const pageRef = useRef(1);
  const sentinelRef = useRef(null);

  const fetchSolutions = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoading(true);
    else setLoadingMore(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (statusFilter) params.status = statusFilter;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      if (periodFilter && periodFilter !== 'all') params.period = periodFilter;
      const { data } = await api.get('admin/personalized-solutions', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setSolutions(newItems);
        else setSolutions((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMore((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setSolutions([]);
    } finally {
      if (reset) setLoading(false);
      else setLoadingMore(false);
    }
  }, [navigate, searchDebounce, statusFilter, activeFilter, periodFilter]);

  useEffect(() => {
    if (!periodInitialized) return;
    pageRef.current = 1;
    fetchSolutions(1, true);
  }, [fetchSolutions, periodInitialized]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    if (!periodInitialized) return;
    saveAdminListFilters(PS_FILTERS_PAGE_ID, {
      search,
      status: statusFilter,
      active: activeFilter,
      period: periodFilter,
    });
  }, [periodInitialized, search, statusFilter, activeFilter, periodFilter]);

  useEffect(() => {
    if (!hasMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      (entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        if (!hasMore || loadingMore || loading) return;
        const next = pageRef.current + 1;
        pageRef.current = next;
        fetchSolutions(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMore, loadingMore, loading, fetchSolutions]);

  const psHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'email':
        return <th key={colId}>{t('admin.personalized_solutions.email')}</th>;
      case 'phone':
        return <th key={colId}>{t('admin.personalized_solutions.phone')}</th>;
      case 'problem_description':
        return <th key={colId}>{t('admin.personalized_solutions.problem_description')}</th>;
      case 'status':
        return (
          <th key={colId} className="text-center">
            {t('admin.personalized_solutions.status')}
          </th>
        );
      case 'created_at':
        return <th key={colId} className="text-end">{t('admin.personalized_solutions.created_at')}</th>;
      case 'client_login_email':
        return <th key={colId}>{t('admin.personalized_solutions.client_login_email')}</th>;
      case 'is_active':
        return (
          <th key={colId} className="text-center">
            {t('admin.products.is_active')}
          </th>
        );
      default:
        return null;
    }
  };

  const psBodyCell = (colId, s) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {s.id}
          </td>
        );
      case 'email':
        return <td key={colId}>{s.email ?? ''}</td>;
      case 'phone':
        return <td key={colId}>{s.phone ?? ''}</td>;
      case 'problem_description':
        return (
          <td key={colId} className="max-w-[200px] truncate">
            {s.problem_description ?? ''}
          </td>
        );
      case 'status':
        return (
          <td key={colId} className="text-center">
            <span className={`badge badge-sm ${getStatusBadgeClass(s.status)}`}>
              {t(`admin.personalized_solutions.status_${s.status}`)}
            </span>
          </td>
        );
      case 'created_at':
        return (
          <td key={colId} className="text-end">
            {s.created_at ? new Date(s.created_at).toLocaleDateString() : ''}
          </td>
        );
      case 'client_login_email':
        return <td key={colId}>{s.client_login_email ?? ''}</td>;
      case 'is_active':
        return (
          <td key={colId} className="text-center">
            {s.is_active ? t('common.yes') : t('common.no')}
          </td>
        );
      default:
        return null;
    }
  };

  const hasDecryptionError = solutions.some((s) => s._decryption_error);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.personalized_solutions.title')}</PageTitle>
      {hasDecryptionError && <DecryptionWarningBanner />}

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
          placeholder={t('admin.personalized_solutions.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.personalized_solutions.search_placeholder')}
        />
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.personalized_solutions.filter_status')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-44"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            aria-label={t('admin.personalized_solutions.filter_status')}
          >
            <option value="">{t('admin.personalized_solutions.status_all')}</option>
            {STATUSES.map((s) => (
              <option key={s} value={s}>{t(`admin.personalized_solutions.status_${s}`)}</option>
            ))}
          </select>
        </label>
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.personalized_solutions.filter_active')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-40"
            value={activeFilter}
            onChange={(e) => setActiveFilter(e.target.value)}
            aria-label={t('admin.personalized_solutions.filter_active')}
          >
            <option value="">{t('shop.categories.all')}</option>
            <option value="1">{t('common.yes')}</option>
            <option value="0">{t('common.no')}</option>
          </select>
        </label>
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.personalized_solutions.filter_period')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-44"
            value={periodFilter}
            onChange={(e) => setPeriodFilter(e.target.value)}
            aria-label={t('admin.personalized_solutions.filter_period')}
          >
            <option value="week">{t('admin.settings.period_week')}</option>
            <option value="month">{t('admin.settings.period_month')}</option>
            <option value="year">{t('admin.settings.period_year')}</option>
            <option value="all">{t('admin.settings.period_all')}</option>
          </select>
        </label>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : solutions.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.personalized_solutions.no_solutions')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>{orderedVisibleColumnIds.map((colId) => psHeaderCell(colId))}</tr>
              </thead>
              <tbody>
                {solutions.map((s) => (
                  <tr
                    key={s.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/personalized-solutions/${s.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/personalized-solutions/${s.id}`);
                      }
                    }}
                  >
                    {orderedVisibleColumnIds.map((colId) => psBodyCell(colId, s))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <div ref={sentinelRef} className="py-2 flex justify-center" aria-hidden="true">
        {loadingMore && <span className="loading loading-spinner loading-md" />}
      </div>
    </div>
  );
}
