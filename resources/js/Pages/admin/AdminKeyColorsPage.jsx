import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { loadAdminListFilters, normalizedActiveTriState, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';

const FILTERS_PAGE_ID = 'key_colors';

function readPersistedFilters() {
  const raw = loadAdminListFilters(FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  const activeRaw = normalizedActiveTriState(raw?.active);
  const activeFilter = activeRaw === null ? '' : activeRaw;
  return { search, activeFilter };
}

export default function AdminKeyColorsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedFilters();
  }
  const persisted = persistedRef.current;

  const [colors, setColors] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const [activeFilter, setActiveFilter] = useState(() => persisted.activeFilter);

  const fetchColors = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/key-colors', { params });
      if (data.success) setColors(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setColors([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, activeFilter]);

  useEffect(() => {
    fetchColors();
  }, [fetchColors]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    saveAdminListFilters(FILTERS_PAGE_ID, { search, active: activeFilter });
  }, [search, activeFilter]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.key_colors.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.key_colors.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.key_colors.search_placeholder')}
          />
          <label className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.key_colors.filter_active')}</span>
            <select
              className="select select-bordered select-sm sm:select-md w-full sm:w-40"
              value={activeFilter}
              onChange={(e) => setActiveFilter(e.target.value)}
              aria-label={t('admin.key_colors.filter_active')}
            >
              <option value="">{t('shop.categories.all')}</option>
              <option value="1">{t('common.yes')}</option>
              <option value="0">{t('common.no')}</option>
            </select>
          </label>
        </div>
        <Link
          to="/admin/key-colors/new"
          className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto"
          aria-label={t('admin.key_colors.add')}
        >
          +
        </Link>
      </div>

      {loading ? (
        <p className="text-sm text-base-content/70">{t('common.loading')}</p>
      ) : colors.length === 0 ? (
        <p className="text-sm text-base-content/70">{t('admin.key_colors.no_colors')}</p>
      ) : (
        <div className="overflow-x-auto">
          <table className="table table-zebra table-sm">
            <thead>
              <tr>
                <th className="w-14">{t('admin.key_colors.color')}</th>
                <th>{t('admin.key_colors.name')}</th>
                <th>{t('admin.key_colors.rgb_code')}</th>
                <th className="text-center">{t('admin.products.is_active')}</th>
                <th className="text-end tabular-nums">{t('admin.key_colors.sort_order')}</th>
              </tr>
            </thead>
            <tbody>
              {colors.map((color) => (
                <tr
                  key={color.id}
                  role="button"
                  tabIndex={0}
                  className="cursor-pointer hover:bg-base-200/50"
                  onClick={() => navigate(`/admin/key-colors/${color.id}`)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                      e.preventDefault();
                      navigate(`/admin/key-colors/${color.id}`);
                    }
                  }}
                >
                  <td>
                    <span
                      className="inline-block w-8 h-8 rounded-full border border-base-300"
                      style={{ backgroundColor: color.rgb_code }}
                      aria-hidden
                    />
                  </td>
                  <td>{color.name}</td>
                  <td className="font-mono text-sm">{color.rgb_code}</td>
                  <td className="text-center">{color.is_active ? t('common.yes') : t('common.no')}</td>
                  <td className="text-end tabular-nums">{color.sort_order}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}
    </div>
  );
}
