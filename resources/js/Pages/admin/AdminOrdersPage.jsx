import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

const KINDS = ['cart', 'order'];
const STATUSES = ['pending', 'sent', 'installation_pending', 'installation_confirmed'];

export default function AdminOrdersPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [kindFilter, setKindFilter] = useState('');
  const [statusFilter, setStatusFilter] = useState('');

  const fetchOrders = useCallback(async () => {
    setLoading(true);
    try {
      const params = {};
      if (searchDebounce) params.search = searchDebounce;
      if (kindFilter) params.kind = kindFilter;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('admin/orders', { params });
      if (data.success) setOrders(data.data || []);
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setOrders([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, searchDebounce, kindFilter, statusFilter]);

  useEffect(() => {
    fetchOrders();
  }, [fetchOrders]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.orders.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
          placeholder={t('admin.orders.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.orders.search_placeholder')}
        />
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.orders.filter_kind')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-40"
            value={kindFilter}
            onChange={(e) => {
              setKindFilter(e.target.value);
              if (e.target.value !== 'order') setStatusFilter('');
            }}
            aria-label={t('admin.orders.filter_kind')}
          >
            <option value="">{t('admin.orders.kind_all')}</option>
            {KINDS.map((k) => (
              <option key={k} value={k}>{t(`admin.orders.kind_${k}`)}</option>
            ))}
          </select>
        </label>
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.orders.filter_status')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-48"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            aria-label={t('admin.orders.filter_status')}
            disabled={kindFilter === 'cart'}
          >
            <option value="">{t('admin.orders.status_all')}</option>
            {STATUSES.map((s) => (
              <option key={s} value={s}>{t(`admin.orders.status_${s}`)}</option>
            ))}
          </select>
        </label>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : orders.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.orders.no_orders')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap">
              <thead>
                <tr>
                  <th>{t('admin.orders.id')}</th>
                  <th>{t('admin.orders.kind')}</th>
                  <th>{t('admin.orders.client')}</th>
                  <th>{t('admin.orders.status')}</th>
                  <th>{t('admin.orders.order_date')}</th>
                  <th>{t('admin.orders.lines_count')}</th>
                  <th className="text-end">{t('admin.orders.total')}</th>
                  <th>{t('admin.orders.updated_at')}</th>
                </tr>
              </thead>
              <tbody>
                {orders.map((o) => (
                  <tr
                    key={o.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/orders/${o.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/orders/${o.id}`);
                      }
                    }}
                  >
                    <td className="font-mono">#{o.id}</td>
                    <td><span className={`badge ${o.kind === 'cart' ? 'badge-ghost' : 'badge-primary'}`}>{t(`admin.orders.kind_${o.kind}`)}</span></td>
                    <td>{o.client_login_email ?? ''}</td>
                    <td>{o.kind === 'order' && o.status ? t(`admin.orders.status_${o.status}`) : ''}</td>
                    <td>{o.order_date ? new Date(o.order_date).toLocaleDateString() : ''}</td>
                    <td>{o.lines_count ?? 0}</td>
                    <td className="text-end font-medium">{o.total != null ? Number(o.total).toFixed(2) : '0.00'} €</td>
                    <td className="text-base-content/70 text-sm">{o.updated_at ? new Date(o.updated_at).toLocaleString() : ''}</td>
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
