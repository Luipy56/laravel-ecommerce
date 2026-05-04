import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';
import { loadAdminListFilters, normalizedActiveTriState, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';

const CATEGORIES_FILTERS_PAGE_ID = 'categories';

function readPersistedCategoryFilters() {
  const raw = loadAdminListFilters(CATEGORIES_FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  const activeRaw = normalizedActiveTriState(raw?.active);
  const activeFilter = activeRaw === null ? '' : activeRaw;
  return { search, activeFilter };
}

export default function AdminCategoriesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds } = useAdminIndexColumnVisibility('categories');
  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedCategoryFilters();
  }
  const persisted = persistedRef.current;
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(false);
  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const [activeFilter, setActiveFilter] = useState(() => persisted.activeFilter);
  const pageRef = useRef(1);
  const sentinelRef = useRef(null);

  const fetchCategories = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoading(true);
    else setLoadingMore(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/categories', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setCategories(newItems);
        else setCategories((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMore((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setCategories([]);
    } finally {
      if (reset) setLoading(false);
      else setLoadingMore(false);
    }
  }, [navigate, searchDebounce, activeFilter]);

  useEffect(() => {
    pageRef.current = 1;
    fetchCategories(1, true);
  }, [fetchCategories]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    saveAdminListFilters(CATEGORIES_FILTERS_PAGE_ID, { search, active: activeFilter });
  }, [search, activeFilter]);

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
        fetchCategories(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMore, loadingMore, loading, fetchCategories]);

  const categoryHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'code':
        return <th key={colId}>{t('admin.products.code')}</th>;
      case 'name':
        return <th key={colId}>{t('admin.products.name')}</th>;
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

  const categoryBodyCell = (colId, c) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {c.id}
          </td>
        );
      case 'code':
        return <td key={colId}>{c.code ?? ''}</td>;
      case 'name':
        return <td key={colId}>{c.name}</td>;
      case 'is_active':
        return (
          <td key={colId} className="text-center">
            {c.is_active ? t('common.yes') : t('common.no')}
          </td>
        );
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.categories.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.categories.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.categories.search_placeholder')}
          />
          <label className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.categories.filter_active')}</span>
            <select
              className="select select-bordered select-sm sm:select-md w-full sm:w-40"
              value={activeFilter}
              onChange={(e) => setActiveFilter(e.target.value)}
              aria-label={t('admin.categories.filter_active')}
            >
              <option value="">{t('shop.categories.all')}</option>
              <option value="1">{t('common.yes')}</option>
              <option value="0">{t('common.no')}</option>
            </select>
          </label>
        </div>
        <Link to="/admin/categories/new" className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto" aria-label={t('admin.categories.add')}>
          <span className="text-xl sm:text-2xl leading-none" aria-hidden="true">+</span>
        </Link>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : categories.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.categories.no_categories')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>{orderedVisibleColumnIds.map((colId) => categoryHeaderCell(colId))}</tr>
              </thead>
              <tbody>
                {categories.map((c) => (
                  <tr
                    key={c.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/categories/${c.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/categories/${c.id}`);
                      }
                    }}
                  >
                    {orderedVisibleColumnIds.map((colId) => categoryBodyCell(colId, c))}
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
