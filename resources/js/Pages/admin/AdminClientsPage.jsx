import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function clientTypeLabel(type, t) {
  if (type === 'person') return t('admin.clients.type_person');
  if (type === 'company') return t('admin.clients.type_company');
  return type || '';
}

export default function AdminClientsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [clients, setClients] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [typeFilter, setTypeFilter] = useState('');
  const [activeFilter, setActiveFilter] = useState('');

  const fetchClients = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (typeFilter) params.type = typeFilter;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/clients', { params });
      if (data.success) setClients(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setClients([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, typeFilter, activeFilter]);

  useEffect(() => {
    fetchClients();
  }, [fetchClients]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <PageTitle>{t('admin.clients.title')}</PageTitle>
      </div>

      <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:gap-4">
        <input
          type="search"
          className="input input-bordered flex-1 max-w-md"
          placeholder={t('admin.clients.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.clients.search_placeholder')}
        />
        <select
          className="select select-bordered w-full sm:w-40"
          value={typeFilter}
          onChange={(e) => setTypeFilter(e.target.value)}
          aria-label={t('admin.clients.filter_type')}
        >
          <option value="">{t('shop.categories.all')}</option>
          <option value="person">{t('admin.clients.type_person')}</option>
          <option value="company">{t('admin.clients.type_company')}</option>
        </select>
        <select
          className="select select-bordered w-full sm:w-40"
          value={activeFilter}
          onChange={(e) => setActiveFilter(e.target.value)}
          aria-label={t('admin.clients.filter_active')}
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
        ) : clients.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.clients.no_clients')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th>{t('admin.clients.email')}</th>
                  <th>{t('admin.clients.filter_type')}</th>
                  <th>{t('admin.clients.identification')}</th>
                  <th>{t('admin.clients.primary_contact')}</th>
                  <th>{t('admin.clients.contacts_count')}</th>
                  <th>{t('admin.clients.addresses_count')}</th>
                  <th>{t('admin.products.is_active')}</th>
                </tr>
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
                    <td>{c.login_email}</td>
                    <td>{clientTypeLabel(c.type, t)}</td>
                    <td>{c.identification}</td>
                    <td>{c.primary_contact_name}</td>
                    <td>{c.contacts_count ?? 0}</td>
                    <td>{c.addresses_count ?? 0}</td>
                    <td>{c.is_active ? t('common.yes') : t('common.no')}</td>
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
