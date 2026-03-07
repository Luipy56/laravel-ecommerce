import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminFeaturesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  // Feature types (tipos de características) – first list
  const [featureTypesList, setFeatureTypesList] = useState([]);
  const [loadingTypes, setLoadingTypes] = useState(true);
  const [searchTypes, setSearchTypes] = useState('');
  const [searchDebounceTypes, setSearchDebounceTypes] = useState('');
  const [activeFilterTypes, setActiveFilterTypes] = useState('');

  // Feature names for the "filter by type" dropdown in the second list
  const [featureNamesForSelect, setFeatureNamesForSelect] = useState([]);

  // Features (características) – second list
  const [features, setFeatures] = useState([]);
  const [loadingFeatures, setLoadingFeatures] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [typeId, setTypeId] = useState('');
  const [activeFilter, setActiveFilter] = useState('');

  const fetchFeatureNamesForSelect = useCallback(async () => {
    try {
      const { data } = await api.get('admin/feature-names');
      if (data.success) setFeatureNamesForSelect(data.data || []);
    } catch {
      setFeatureNamesForSelect([]);
    }
  }, []);

  const fetchFeatureTypesList = useCallback(async () => {
    setLoadingTypes(true);
    try {
      const params = {};
      if (searchDebounceTypes) params.search = searchDebounceTypes;
      if (activeFilterTypes !== '') params.is_active = activeFilterTypes === '1';
      const { data } = await api.get('admin/feature-names', { params });
      if (data.success) setFeatureTypesList(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setFeatureTypesList([]);
    } finally {
      setLoadingTypes(false);
    }
  }, [navigate, searchDebounceTypes, activeFilterTypes]);

  const fetchFeatures = useCallback(async () => {
    setLoadingFeatures(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (typeId) params.feature_name_id = typeId;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/features', { params });
      if (data.success) setFeatures(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setFeatures([]);
    } finally {
      setLoadingFeatures(false);
    }
  }, [navigate, searchDebounce, typeId, activeFilter]);

  useEffect(() => {
    fetchFeatureNamesForSelect();
  }, [fetchFeatureNamesForSelect]);

  useEffect(() => {
    fetchFeatureTypesList();
  }, [fetchFeatureTypesList]);

  useEffect(() => {
    fetchFeatures();
  }, [fetchFeatures]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounceTypes(searchTypes.trim()), 300);
    return () => clearTimeout(tid);
  }, [searchTypes]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

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
                  <tr>
                    <th>{t('admin.features.type')}</th>
                    <th className="text-center">{t('admin.products.is_active')}</th>
                  </tr>
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
                      <td>{n.name}</td>
                      <td className="text-center">{n.is_active ? t('common.yes') : t('common.no')}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
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
                  <tr>
                    <th>{t('admin.features.type')}</th>
                    <th>{t('admin.features.value')}</th>
                    <th className="text-center">{t('admin.products.is_active')}</th>
                  </tr>
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
                      <td>{f.feature_name}</td>
                      <td>{f.value}</td>
                      <td className="text-center">{f.is_active ? t('common.yes') : t('common.no')}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
