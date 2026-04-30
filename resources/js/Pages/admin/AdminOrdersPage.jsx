import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';

const KINDS = ['cart', 'order', 'like'];
const STATUSES = ['pending', 'awaiting_payment', 'awaiting_installation_price', 'in_transit', 'sent', 'installation_pending', 'installation_confirmed'];

function installationStatusLabel(status, t) {
  if (status == null || status === '') return '';
  if (status === 'pending') return t('admin.orders.install_status_pending');
  if (status === 'priced') return t('admin.orders.install_status_priced');
  if (status === 'rejected') return t('admin.orders.install_status_rejected');

  return String(status);
}

function getStatusBadgeClass(status) {
  switch (status) {
    case 'pending': return 'badge-warning';
    case 'awaiting_payment': return 'badge-warning';
    case 'awaiting_installation_price': return 'badge-warning';
    case 'in_transit': return 'badge-success';
    case 'sent': return 'badge-success';
    case 'installation_pending': return 'badge-warning';
    case 'installation_confirmed': return 'badge-info text-base-content';
    default: return 'badge-ghost';
  }
}

export default function AdminOrdersPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { orderedVisibleColumnIds } = useAdminIndexColumnVisibility('orders');
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [kindFilter, setKindFilter] = useState('order');
  const [statusFilter, setStatusFilter] = useState('');

  const fetchOrders = useCallback(async () => {
    setLoading(true);
    try {
      const params = { page, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (kindFilter) params.kind = kindFilter;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('admin/orders', { params });
      if (data.success) {
        setOrders(data.data || []);
        setMeta(data.meta || meta);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setOrders([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, page, searchDebounce, kindFilter, statusFilter]);

  useEffect(() => {
    fetchOrders();
  }, [fetchOrders]);

  useEffect(() => {
    setPage(1);
  }, [searchDebounce, kindFilter, statusFilter]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  const orderHeaderCell = (colId) => {
    switch (colId) {
      case 'id':
        return <th key={colId}>{t('admin.orders.id')}</th>;
      case 'kind':
        return (
          <th key={colId} className="text-center">
            {t('admin.orders.kind')}
          </th>
        );
      case 'client':
        return <th key={colId}>{t('admin.orders.client')}</th>;
      case 'order_date':
        return <th key={colId} className="text-end">{t('admin.orders.order_date')}</th>;
      case 'lines_count':
        return (
          <th key={colId} className="text-center">
            {t('admin.orders.lines_count')}
          </th>
        );
      case 'total':
        return <th key={colId} className="text-end">{t('admin.orders.total')}</th>;
      case 'status':
        return (
          <th key={colId} className="text-center">
            {t('admin.orders.status')}
          </th>
        );
      case 'installation_requested':
        return (
          <th key={colId} className="text-center">
            {t('admin.orders.column_installation_requested')}
          </th>
        );
      case 'installation_status':
        return <th key={colId}>{t('admin.orders.installation_status_label')}</th>;
      case 'installation_price':
        return <th key={colId} className="text-end">{t('admin.orders.column_installation_price')}</th>;
      case 'shipping_date':
        return <th key={colId} className="text-end">{t('admin.orders.shipping_date')}</th>;
      case 'created_at':
        return <th key={colId} className="text-end">{t('admin.orders.created_at')}</th>;
      case 'updated_at':
        return <th key={colId} className="text-end">{t('admin.orders.updated_at')}</th>;
      default:
        return null;
    }
  };

  const orderBodyCell = (colId, o) => {
    switch (colId) {
      case 'id':
        return (
          <td key={colId} className="text-center font-mono">
            #{o.id}
          </td>
        );
      case 'kind':
        return (
          <td key={colId} className="text-center">
            <span className="badge badge-ghost">{t(`admin.orders.kind_${o.kind}`)}</span>
          </td>
        );
      case 'client':
        return <td key={colId}>{o.client_login_email ?? ''}</td>;
      case 'order_date':
        return (
          <td key={colId} className="text-end">
            {o.order_date ? new Date(o.order_date).toLocaleDateString() : ''}
          </td>
        );
      case 'lines_count':
        return (
          <td key={colId} className="text-center tabular-nums">
            {o.lines_count ?? 0}
          </td>
        );
      case 'total':
        return (
          <td key={colId} className="text-end font-medium tabular-nums">
            {o.total != null ? Number(o.total).toFixed(2) : '0.00'} €
          </td>
        );
      case 'status':
        return (
          <td key={colId} className="text-center">
            {o.kind === 'order' && o.status ? (
              <span className={`badge badge-sm ${getStatusBadgeClass(o.status)}`}>{t(`admin.orders.status_${o.status}`)}</span>
            ) : (
              ''
            )}
          </td>
        );
      case 'installation_requested':
        return (
          <td key={colId} className="text-center">
            {o.installation_requested ? t('common.yes') : t('common.no')}
          </td>
        );
      case 'installation_status':
        return <td key={colId}>{installationStatusLabel(o.installation_status, t)}</td>;
      case 'installation_price':
        return (
          <td key={colId} className="text-end tabular-nums">
            {o.installation_price != null && o.installation_price !== ''
              ? `${Number(o.installation_price).toFixed(2)} €`
              : ''}
          </td>
        );
      case 'shipping_date':
        return (
          <td key={colId} className="text-end">
            {o.shipping_date ? new Date(o.shipping_date).toLocaleDateString() : ''}
          </td>
        );
      case 'created_at':
        return (
          <td key={colId} className="text-end">
            {o.created_at ? new Date(o.created_at).toLocaleString() : ''}
          </td>
        );
      case 'updated_at':
        return (
          <td key={colId} className="text-end">
            {o.updated_at ? new Date(o.updated_at).toLocaleString() : ''}
          </td>
        );
      default:
        return null;
    }
  };

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
            disabled={kindFilter === 'cart' || kindFilter === 'like'}
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
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>{orderedVisibleColumnIds.map((colId) => orderHeaderCell(colId))}</tr>
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
                    {orderedVisibleColumnIds.map((colId) => orderBodyCell(colId, o))}
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
