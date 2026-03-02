import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminPacksPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [packs, setPacks] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [activeFilter, setActiveFilter] = useState('');
  const [trendingFilter, setTrendingFilter] = useState('');

  const fetchPacks = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      if (trendingFilter !== '') params.is_trending = trendingFilter === '1';
      const { data } = await api.get('admin/packs', { params });
      if (data.success) setPacks(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setPacks([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, activeFilter, trendingFilter]);

  useEffect(() => {
    fetchPacks();
  }, [fetchPacks]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <PageTitle>{t('admin.packs.title')}</PageTitle>
        <Link to="/admin/packs/new" className="btn btn-primary btn-sm sm:btn-md shrink-0">
          {t('admin.packs.add')}
        </Link>
      </div>

      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <input
          type="search"
          className="input input-bordered flex-1 max-w-xs"
          placeholder={t('admin.packs.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.packs.search_placeholder')}
        />
        <select
          className="select select-bordered w-full sm:w-40"
          value={activeFilter}
          onChange={(e) => setActiveFilter(e.target.value)}
          aria-label={t('admin.packs.filter_active')}
        >
          <option value="">{t('shop.categories.all')}</option>
          <option value="1">{t('common.yes')}</option>
          <option value="0">{t('common.no')}</option>
        </select>
        <select
          className="select select-bordered w-full sm:w-40"
          value={trendingFilter}
          onChange={(e) => setTrendingFilter(e.target.value)}
          aria-label={t('admin.packs.filter_trending')}
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
        ) : packs.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.packs.no_packs')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th>{t('admin.products.name')}</th>
                  <th>{t('admin.products.price')}</th>
                  <th>{t('admin.packs.products_in_pack')}</th>
                  <th>{t('admin.products.is_trending')}</th>
                  <th>{t('admin.products.is_active')}</th>
                </tr>
              </thead>
              <tbody>
                {packs.map((p) => (
                  <tr
                    key={p.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/packs/${p.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/packs/${p.id}`);
                      }
                    }}
                  >
                    <td>{p.name}</td>
                    <td>{p.price != null ? `${Number(p.price).toFixed(2)} €` : ''}</td>
                    <td>{p.pack_items_count ?? 0}</td>
                    <td>{p.is_trending ? t('common.yes') : t('common.no')}</td>
                    <td>{p.is_active ? t('common.yes') : t('common.no')}</td>
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
