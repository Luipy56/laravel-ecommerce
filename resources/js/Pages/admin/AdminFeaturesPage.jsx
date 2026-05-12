import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { IconChevronDown } from '../../components/icons';
import { loadAdminListFilters, normalizedActiveTriState, normalizedStoredSearch, saveAdminListFilters } from '../../utils/adminListFiltersStorage';

const FILTERS_PAGE_ID = 'features_manager';

function readPersistedFilters() {
  const raw = loadAdminListFilters(FILTERS_PAGE_ID);
  const search = normalizedStoredSearch(raw?.search ?? '', '');
  const activeRaw = normalizedActiveTriState(raw?.active);
  const activeFilter = activeRaw === null ? '' : activeRaw;
  return { search, activeFilter };
}

export default function AdminFeaturesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const persistedRef = useRef(undefined);
  if (persistedRef.current === undefined) {
    persistedRef.current = readPersistedFilters();
  }
  const persisted = persistedRef.current;

  const [featureNames, setFeatureNames] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState(() => persisted.search);
  const [searchDebounce, setSearchDebounce] = useState(() => persisted.search.trim());
  const [activeFilter, setActiveFilter] = useState(() => persisted.activeFilter);
  const [expanded, setExpanded] = useState({});

  const fetchData = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/feature-names-with-features', { params });
      if (data.success) setFeatureNames(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setFeatureNames([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, activeFilter]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    saveAdminListFilters(FILTERS_PAGE_ID, { search, active: activeFilter });
  }, [search, activeFilter]);

  const toggleExpanded = (id) => {
    setExpanded((prev) => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.features.title')}</PageTitle>

      {/* Toolbar: search + filter + add buttons */}
      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.features.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.features.search_placeholder')}
          />
          <label className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.features.filter_active')}</span>
            <select
              className="select select-bordered select-sm sm:select-md w-full sm:w-40"
              value={activeFilter}
              onChange={(e) => setActiveFilter(e.target.value)}
              aria-label={t('admin.features.filter_active')}
            >
              <option value="">{t('shop.categories.all')}</option>
              <option value="1">{t('common.yes')}</option>
              <option value="0">{t('common.no')}</option>
            </select>
          </label>
        </div>
        <div className="flex items-center gap-2 shrink-0 ml-auto">
          <Link to="/admin/feature-names/new" className="btn btn-outline btn-sm sm:btn-md shrink-0" aria-label={t('admin.feature_types.add')}>
            + {t('admin.features.add_type')}
          </Link>
          <Link to="/admin/features/new" className="btn btn-primary btn-sm sm:btn-md shrink-0" aria-label={t('admin.features.add')}>
            + {t('admin.features.add_value')}
          </Link>
        </div>
      </div>

      {/* Collapsible feature-name sections */}
      {loading ? (
        <div className="flex justify-center py-12">
          <span className="loading loading-spinner loading-lg" aria-hidden="true" />
        </div>
      ) : featureNames.length === 0 ? (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="p-8 text-center text-base-content/70">
            {t('admin.features.no_features')}
          </div>
        </div>
      ) : (
        <div className="space-y-2">
          {featureNames.map((fn) => {
            const isOpen = !!expanded[fn.id];
            return (
              <div key={fn.id} className="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
                {/* Collapsible header */}
                <div
                  className="flex items-center gap-3 px-4 py-3 select-none hover:bg-base-200/50 transition-colors"
                  aria-expanded={isOpen}
                >
                  <button
                    type="button"
                    className="text-base-content/50 transition-transform duration-200 cursor-pointer p-1 -m-1"
                    style={{ transform: isOpen ? 'rotate(0deg)' : 'rotate(-90deg)' }}
                    onClick={() => toggleExpanded(fn.id)}
                    aria-label={isOpen ? t('common.collapse') : t('common.expand')}
                  >
                    <IconChevronDown className="h-4 w-4" aria-hidden="true" />
                  </button>
                  <div
                    className="flex items-center gap-2 flex-1 cursor-pointer"
                    role="button"
                    tabIndex={0}
                    onClick={() => navigate(`/admin/feature-names/${fn.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/feature-names/${fn.id}`);
                      }
                    }}
                  >
                    <span className="font-semibold text-base-content">{fn.name}</span>
                    {!fn.is_active && (
                      <span className="badge badge-sm badge-warning">{t('admin.features.inactive')}</span>
                    )}
                  </div>
                </div>

                {/* Expanded content: grid of feature values */}
                {isOpen && (
                  <div className="border-t border-base-200">
                    {fn.features.length === 0 ? (
                      <div className="px-4 py-6 text-center text-sm text-base-content/50">
                        {t('admin.features.no_values')}
                      </div>
                    ) : (
                      <div className="grid grid-cols-3 divide-x divide-base-200">
                        {fn.features.map((f) => (
                          <div
                            key={f.id}
                            role="button"
                            tabIndex={0}
                            className="px-4 py-2 cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none flex items-center justify-between gap-2"
                            onClick={() => navigate(`/admin/features/${f.id}`)}
                            onKeyDown={(e) => {
                              if (e.key === 'Enter' || e.key === ' ') {
                                e.preventDefault();
                                navigate(`/admin/features/${f.id}`);
                              }
                            }}
                          >
                            <span className="flex items-center gap-2">
                              <span className="text-xs text-base-content/40 tabular-nums">#{f.id}</span>
                              <span className="text-sm">{f.value}</span>
                            </span>
                            {!f.is_active && (
                              <span className="badge badge-xs badge-warning">{t('admin.features.inactive')}</span>
                            )}
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                )}
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
}
