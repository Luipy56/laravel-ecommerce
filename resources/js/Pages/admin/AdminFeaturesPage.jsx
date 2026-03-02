import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminFeaturesPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [features, setFeatures] = useState([]);
  const [featureNames, setFeatureNames] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [typeId, setTypeId] = useState('');
  const [activeFilter, setActiveFilter] = useState(''); // '' = all, '1' = yes, '0' = no

  const fetchFeatureNames = useCallback(async () => {
    try {
      const { data } = await api.get('admin/feature-names');
      if (data.success) setFeatureNames(data.data || []);
    } catch {
      setFeatureNames([]);
    }
  }, []);

  const fetchFeatures = useCallback(async () => {
    setLoading(true);
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
      setLoading(false);
    }
  }, [navigate, searchDebounce, typeId, activeFilter]);

  useEffect(() => {
    fetchFeatureNames();
  }, [fetchFeatureNames]);

  useEffect(() => {
    fetchFeatures();
  }, [fetchFeatures]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <PageTitle>{t('admin.features.title')}</PageTitle>
        <Link to="/admin/features/new" className="btn btn-primary btn-sm sm:btn-md shrink-0">
          {t('admin.features.add')}
        </Link>
      </div>

      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <input
          type="search"
          className="input input-bordered flex-1 max-w-xs"
          placeholder={t('admin.features.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.features.search_placeholder')}
        />
        <select
          className="select select-bordered w-full sm:w-48"
          value={typeId}
          onChange={(e) => setTypeId(e.target.value)}
          aria-label={t('admin.features.filter_type')}
        >
          <option value="">{t('shop.categories.all')}</option>
          {featureNames.map((n) => (
            <option key={n.id} value={n.id}>
              {n.name}
            </option>
          ))}
        </select>
        <select
          className="select select-bordered w-full sm:w-40"
          value={activeFilter}
          onChange={(e) => setActiveFilter(e.target.value)}
          aria-label={t('admin.features.filter_active')}
        >
          <option value="">{t('shop.categories.all')}</option>
          <option value="1">{t('common.yes')}</option>
          <option value="0">{t('common.no')}</option>
        </select>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : features.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.features.no_features')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th>{t('admin.features.type')}</th>
                  <th>{t('admin.features.value')}</th>
                  <th>{t('admin.products.is_active')}</th>
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
                    <td>{f.is_active ? t('common.yes') : t('common.no')}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </div>
  );
}
