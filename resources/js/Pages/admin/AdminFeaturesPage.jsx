import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';

export default function AdminFeaturesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds: orderedFeatureTypeCols } = useAdminIndexColumnVisibility('feature_types');
  const { orderedVisibleColumnIds: orderedFeatureCols } = useAdminIndexColumnVisibility('features');

  // Feature types (tipos de características) – first list
  const [featureTypesList, setFeatureTypesList] = useState([]);
  const [loadingTypes, setLoadingTypes] = useState(true);
  const [loadingMoreTypes, setLoadingMoreTypes] = useState(false);
  const [hasMoreTypes, setHasMoreTypes] = useState(false);
  const [searchTypes, setSearchTypes] = useState('');
  const [searchDebounceTypes, setSearchDebounceTypes] = useState('');
  const [activeFilterTypes, setActiveFilterTypes] = useState('');
  const pageTypesRef = useRef(1);
  const sentinelTypesRef = useRef(null);

  // Feature names for the "filter by type" dropdown in the second list
  const [featureNamesForSelect, setFeatureNamesForSelect] = useState([]);

  // Features (características) – second list
  const [features, setFeatures] = useState([]);
  const [loadingFeatures, setLoadingFeatures] = useState(true);
  const [loadingMoreFeatures, setLoadingMoreFeatures] = useState(false);
  const [hasMoreFeatures, setHasMoreFeatures] = useState(false);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [typeId, setTypeId] = useState('');
  const [activeFilter, setActiveFilter] = useState('');
  const pageFeaturesRef = useRef(1);
  const sentinelFeaturesRef = useRef(null);

  const fetchFeatureNamesForSelect = useCallback(async () => {
    try {
      const { data } = await api.get('admin/feature-names', { params: { per_page: 500 } });
      if (data.success) setFeatureNamesForSelect(data.data || []);
    } catch {
      setFeatureNamesForSelect([]);
    }
  }, []);

  const fetchFeatureTypesList = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoadingTypes(true);
    else setLoadingMoreTypes(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounceTypes) params.search = searchDebounceTypes;
      if (activeFilterTypes !== '') params.is_active = activeFilterTypes === '1';
      const { data } = await api.get('admin/feature-names', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setFeatureTypesList(newItems);
        else setFeatureTypesList((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMoreTypes((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageTypesRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setFeatureTypesList([]);
    } finally {
      if (reset) setLoadingTypes(false);
      else setLoadingMoreTypes(false);
    }
  }, [navigate, searchDebounceTypes, activeFilterTypes]);

  const fetchFeatures = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoadingFeatures(true);
    else setLoadingMoreFeatures(true);
    try {
      const params = { page: pageNum, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (typeId) params.feature_name_id = typeId;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/features', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setFeatures(newItems);
        else setFeatures((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMoreFeatures((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageFeaturesRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setFeatures([]);
    } finally {
      if (reset) setLoadingFeatures(false);
      else setLoadingMoreFeatures(false);
    }
  }, [navigate, searchDebounce, typeId, activeFilter]);

  useEffect(() => {
    fetchFeatureNamesForSelect();
  }, [fetchFeatureNamesForSelect]);

  useEffect(() => {
    pageTypesRef.current = 1;
    fetchFeatureTypesList(1, true);
  }, [fetchFeatureTypesList]);

  useEffect(() => {
    pageFeaturesRef.current = 1;
    fetchFeatures(1, true);
  }, [fetchFeatures]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounceTypes(searchTypes.trim()), 300);
    return () => clearTimeout(tid);
  }, [searchTypes]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    if (!hasMoreTypes) return;
    const el = sentinelTypesRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      (entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        if (!hasMoreTypes || loadingMoreTypes || loadingTypes) return;
        const next = pageTypesRef.current + 1;
        pageTypesRef.current = next;
        fetchFeatureTypesList(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMoreTypes, loadingMoreTypes, loadingTypes, fetchFeatureTypesList]);

  useEffect(() => {
    if (!hasMoreFeatures) return;
    const el = sentinelFeaturesRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      (entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        if (!hasMoreFeatures || loadingMoreFeatures || loadingFeatures) return;
        const next = pageFeaturesRef.current + 1;
        pageFeaturesRef.current = next;
        fetchFeatures(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMoreFeatures, loadingMoreFeatures, loadingFeatures, fetchFeatures]);

  const featureTypeHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'name':
        return <th key={colId}>{t('admin.features.type')}</th>;
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

  const featureTypeBodyCell = (colId, n) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {n.id}
          </td>
        );
      case 'name':
        return <td key={colId}>{n.name}</td>;
      case 'is_active':
        return (
          <td key={colId} className="text-center">
            {n.is_active ? t('common.yes') : t('common.no')}
          </td>
        );
      default:
        return null;
    }
  };

  const featureHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'feature_name_id':
        return (
          <th key={colId} className="text-end tabular-nums">
            {t('admin.features.feature_name_id')}
          </th>
        );
      case 'feature_name':
        return <th key={colId}>{t('admin.features.type')}</th>;
      case 'value':
        return <th key={colId}>{t('admin.features.value')}</th>;
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

  const featureBodyCell = (colId, f) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {f.id}
          </td>
        );
      case 'feature_name_id':
        return (
          <td key={colId} className="text-end tabular-nums">
            {f.feature_name_id ?? ''}
          </td>
        );
      case 'feature_name':
        return <td key={colId}>{f.feature_name}</td>;
      case 'value':
        return <td key={colId}>{f.value}</td>;
      case 'is_active':
        return (
          <td key={colId} className="text-center">
            {f.is_active ? t('common.yes') : t('common.no')}
          </td>
        );
      default:
        return null;
    }
  };

  return (
    <div className="space-y-10">
      <PageTitle>{t('admin.features.title')}</PageTitle>

      {/* Section 1: Tipos de características */}
      <section className="space-y-4">
        <h2 className="text-lg font-semibold text-base-content border-b border-base-300 pb-1">
          {t('admin.feature_types.title')}
        </h2>
        <div className="flex flex-wrap items-center gap-2 sm:gap-4">
          <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
            <input
              type="search"
              className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
              placeholder={t('admin.feature_types.search_placeholder')}
              value={searchTypes}
              onChange={(e) => setSearchTypes(e.target.value)}
              aria-label={t('admin.feature_types.search_placeholder')}
            />
            <label className="flex items-center gap-2 shrink-0">
              <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.feature_types.filter_active')}</span>
              <select
                className="select select-bordered select-sm sm:select-md w-full sm:w-40"
                value={activeFilterTypes}
                onChange={(e) => setActiveFilterTypes(e.target.value)}
                aria-label={t('admin.feature_types.filter_active')}
              >
                <option value="">{t('shop.categories.all')}</option>
                <option value="1">{t('common.yes')}</option>
                <option value="0">{t('common.no')}</option>
              </select>
            </label>
          </div>
          <Link to="/admin/feature-names/new" className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto" aria-label={t('admin.feature_types.add')}>
            <span className="text-xl sm:text-2xl leading-none" aria-hidden="true">+</span>
          </Link>
        </div>
        <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
          {loadingTypes ? (
            <div className="flex justify-center py-12">
              <span className="loading loading-spinner loading-lg" aria-hidden="true" />
            </div>
          ) : featureTypesList.length === 0 ? (
            <div className="p-8 text-center text-base-content/70">
              {t('admin.feature_types.no_types')}
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
                <thead>
                  <tr>{orderedFeatureTypeCols.map((colId) => featureTypeHeaderCell(colId))}</tr>
                </thead>
                <tbody>
                  {featureTypesList.map((n) => (
                    <tr
                      key={n.id}
                      role="button"
                      tabIndex={0}
                      className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                      onClick={() => navigate(`/admin/feature-names/${n.id}`)}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                          e.preventDefault();
                          navigate(`/admin/feature-names/${n.id}`);
                        }
                      }}
                    >
                      {orderedFeatureTypeCols.map((colId) => featureTypeBodyCell(colId, n))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
        <div ref={sentinelTypesRef} className="py-2 flex justify-center" aria-hidden="true">
          {loadingMoreTypes && <span className="loading loading-spinner loading-md" />}
        </div>
      </section>

      {/* Section 2: Características (valores) */}
      <section className="space-y-4">
        <h2 className="text-lg font-semibold text-base-content border-b border-base-300 pb-1">
          {t('admin.features.section_values')}
        </h2>
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
              <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.features.filter_type')}</span>
              <select
                className="select select-bordered select-sm sm:select-md w-full sm:w-48"
                value={typeId}
                onChange={(e) => setTypeId(e.target.value)}
                aria-label={t('admin.features.filter_type')}
              >
                <option value="">{t('shop.categories.all')}</option>
                {featureNamesForSelect.map((n) => (
                  <option key={n.id} value={n.id}>
                    {n.name}
                  </option>
                ))}
              </select>
            </label>
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
          <Link to="/admin/features/new" className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto" aria-label={t('admin.features.add')}>
            <span className="text-xl sm:text-2xl leading-none" aria-hidden="true">+</span>
          </Link>
        </div>
        <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
          {loadingFeatures ? (
            <div className="flex justify-center py-12">
              <span className="loading loading-spinner loading-lg" aria-hidden="true" />
            </div>
          ) : features.length === 0 ? (
            <div className="p-8 text-center text-base-content/70">
              {t('admin.features.no_features')}
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
                <thead>
                  <tr>{orderedFeatureCols.map((colId) => featureHeaderCell(colId))}</tr>
                </thead>
                <tbody>
                  {features.map((f) => (
                    <tr
                      key={f.id}
                      role="button"
                      tabIndex={0}
                      className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                      onClick={() => navigate(`/admin/features/${f.id}`)}
                      onKeyDown={(e) => {
                        if (e.key === 'Enter' || e.key === ' ') {
                          e.preventDefault();
                          navigate(`/admin/features/${f.id}`);
                        }
                      }}
                    >
                      {orderedFeatureCols.map((colId) => featureBodyCell(colId, f))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
        <div ref={sentinelFeaturesRef} className="py-2 flex justify-center" aria-hidden="true">
          {loadingMoreFeatures && <span className="loading loading-spinner loading-md" />}
        </div>
      </section>
    </div>
  );
}
