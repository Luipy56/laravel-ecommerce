import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function groupLabel(products) {
  if (!products?.length) return '';
  const names = products.map((p) => p.name || p.code).filter(Boolean);
  if (names.length <= 2) return names.join(', ');
  return `${names[0]} (+${names.length - 1})`;
}

export default function AdminVariantGroupsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [groups, setGroups] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');

  const fetchGroups = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      const { data } = await api.get('admin/variant-groups', { params });
      if (data.success) setGroups(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setGroups([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce]);

  useEffect(() => {
    fetchGroups();
  }, [fetchGroups]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <PageTitle>{t('admin.variant_groups.title')}</PageTitle>
        <Link to="/admin/variant-groups/new" className="btn btn-primary btn-sm sm:btn-md shrink-0">
          {t('admin.variant_groups.add')}
        </Link>
      </div>

      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <input
          type="search"
          className="input input-bordered flex-1 max-w-xs"
          placeholder={t('admin.variant_groups.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.variant_groups.search_placeholder')}
        />
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
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th>{t('admin.variant_groups.group_label')}</th>
                  <th>{t('admin.variant_groups.products_in_group')}</th>
                </tr>
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
                    <td>#{g.id}</td>
                    <td>
                      {g.products_count === 0
                        ? '—'
                        : groupLabel(g.products) || `${g.products_count} ${t('admin.products.name').toLowerCase()}`}
                    </td>
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
