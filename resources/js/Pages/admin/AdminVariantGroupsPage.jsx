import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';
import { loadAdminListFilters, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';

const VARIANT_GROUPS_FILTERS_PAGE_ID = 'variant_groups';

function readPersistedVariantGroupFilters() {
  const raw = loadAdminListFilters(VARIANT_GROUPS_FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  return { search };
}

function productsLabel(products) {
  if (!products?.length) return '';
  const names = products.map((p) => p.name || p.code).filter(Boolean);
  if (names.length <= 2) return names.join(', ');
  return `${names[0]} (+${names.length - 1})`;
}

export default function AdminVariantGroupsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds } = useAdminIndexColumnVisibility('variant_groups');
  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedVariantGroupFilters();
  }
  const persisted = persistedRef.current;
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(false);
  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const pageRef = useRef(1);
  const sentinelRef = useRef(null);

  const fetchGroups = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoading(true);
    else setLoadingMore(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      const { data } = await api.get('admin/variant-groups', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setGroups(newItems);
        else setGroups((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMore((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setGroups([]);
    } finally {
      if (reset) setLoading(false);
      else setLoadingMore(false);
    }
  }, [navigate, searchDebounce]);

  useEffect(() => {
    pageRef.current = 1;
    fetchGroups(1, true);
  }, [fetchGroups]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    saveAdminListFilters(VARIANT_GROUPS_FILTERS_PAGE_ID, { search });
  }, [search]);

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
        fetchGroups(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMore, loadingMore, loading, fetchGroups]);

  const variantGroupHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'group_label':
        return <th key={colId}>{t('admin.variant_groups.group_label')}</th>;
      case 'products_in_group':
        return <th key={colId}>{t('admin.variant_groups.products_in_group')}</th>;
      default:
        return null;
    }
  };

  const variantGroupBodyCell = (colId, g) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {g.id}
          </td>
        );
      case 'group_label':
        return <td key={colId}>{g.name || `#${g.id}`}</td>;
      case 'products_in_group':
        return (
          <td key={colId}>
            {g.products_count === 0
              ? ''
              : productsLabel(g.products) || `${g.products_count} ${t('admin.products.name').toLowerCase()}`}
          </td>
        );
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.variant_groups.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.variant_groups.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.variant_groups.search_placeholder')}
          />
        </div>
        <Link to="/admin/variant-groups/new" className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto" aria-label={t('admin.variant_groups.add')}>
          <span className="text-xl sm:text-2xl leading-none" aria-hidden="true">+</span>
        </Link>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : groups.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.variant_groups.no_groups')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>{orderedVisibleColumnIds.map((colId) => variantGroupHeaderCell(colId))}</tr>
              </thead>
              <tbody>
                {groups.map((g) => (
                  <tr
                    key={g.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/variant-groups/${g.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/variant-groups/${g.id}`);
                      }
                    }}
                  >
                    {orderedVisibleColumnIds.map((colId) => variantGroupBodyCell(colId, g))}
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
