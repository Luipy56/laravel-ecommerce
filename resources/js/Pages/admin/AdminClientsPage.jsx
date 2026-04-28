import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';

function clientTypeLabel(type, t) {
  if (type === 'person') return t('admin.clients.type_person');
  if (type === 'company') return t('admin.clients.type_company');
  return type || '';
}

export default function AdminClientsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds } = useAdminIndexColumnVisibility('clients');
  const [clients, setClients] = useState([]);
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [activeFilter, setActiveFilter] = useState('1');

  const fetchClients = useCallback(async () => {
    setLoading(true);
    try {
      const params = { page, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (typeFilter) params.type = typeFilter;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/clients', { params });
      if (data.success) {
        setClients(data.data || []);
        setMeta(data.meta || meta);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setClients([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, page, searchDebounce, typeFilter, activeFilter]);

  useEffect(() => {
    fetchClients();
  }, [fetchClients]);

  useEffect(() => {
    setPage(1);
  }, [searchDebounce, typeFilter, activeFilter]);

  const clientHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return (
          <th key={colId} className="text-center tabular-nums">
            {t('admin.common.column_id')}
          </th>
        );
      case 'email':
        return <th key={colId}>{t('admin.clients.email')}</th>;
      case 'type':
        return <th key={colId}>{t('admin.clients.filter_type')}</th>;
      case 'identification':
        return <th key={colId}>{t('admin.clients.identification')}</th>;
      case 'primary_contact':
        return <th key={colId}>{t('admin.clients.primary_contact')}</th>;
      case 'contacts_count':
        return (
          <th key={colId} className="text-center">
            {t('admin.clients.contacts_count')}
          </th>
        );
      case 'addresses_count':
        return (
          <th key={colId} className="text-center">
            {t('admin.clients.addresses_count')}
          </th>
        );
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

  const clientBodyCell = (colId, c) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center tabular-nums">
            {c.id}
          </td>
        );
      case 'email':
        return <td key={colId}>{c.login_email}</td>;
      case 'type':
        return <td key={colId}>{clientTypeLabel(c.type, t)}</td>;
      case 'identification':
        return <td key={colId}>{c.identification}</td>;
      case 'primary_contact':
        return <td key={colId}>{c.primary_contact_name}</td>;
      case 'contacts_count':
        return (
          <td key={colId} className="text-center tabular-nums">
            {c.contacts_count ?? 0}
          </td>
        );
      case 'addresses_count':
        return (
          <td key={colId} className="text-center tabular-nums">
            {c.addresses_count ?? 0}
          </td>
        );
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

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.clients.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-md"
          placeholder={t('admin.clients.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.clients.search_placeholder')}
        />
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.clients.filter_type')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-40"
            value={typeFilter}
            onChange={(e) => setTypeFilter(e.target.value)}
            aria-label={t('admin.clients.filter_type')}
          >
            <option value="">{t('shop.categories.all')}</option>
            <option value="person">{t('admin.clients.type_person')}</option>
            <option value="company">{t('admin.clients.type_company')}</option>
          </select>
        </label>
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.clients.filter_active')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-40"
            value={activeFilter}
            onChange={(e) => setActiveFilter(e.target.value)}
            aria-label={t('admin.clients.filter_active')}
          >
            <option value="">{t('shop.categories.all')}</option>
            <option value="1">{t('common.yes')}</option>
            <option value="0">{t('common.no')}</option>
          </select>
        </label>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : clients.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.clients.no_clients')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>{orderedVisibleColumnIds.map((colId) => clientHeaderCell(colId))}</tr>
              </thead>
              <tbody>
                {clients.map((c) => (
                  <tr
                    key={c.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/clients/${c.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/clients/${c.id}`);
                      }
                    }}
                  >
                    {orderedVisibleColumnIds.map((colId) => clientBodyCell(colId, c))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {meta.last_page > 1 && (
        <div className="join flex justify-center">
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('shop.pagination.prev')}
          </button>
          <span className="join-item flex items-center justify-center px-4 py-2 h-8 text-sm text-base-content bg-base-100 border border-base-300">
            {t('shop.pagination.page')} {page} {t('shop.pagination.of')} {meta.last_page}
          </span>
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page >= meta.last_page}
            onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
          >
            {t('shop.pagination.next')}
          </button>
        </div>
      )}
    </div>
  );
}
