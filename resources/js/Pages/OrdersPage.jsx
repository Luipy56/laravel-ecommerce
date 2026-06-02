import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';
import OrderStatusTracker, { isOrderClosed } from '../components/OrderStatusTracker';

function statusBadgeClass(status) {
  switch (status) {
    case 'pending':
    case 'awaiting_payment':
    case 'awaiting_installation_price':
    case 'installation_pending':
      return 'badge-outline badge-warning';
    case 'in_transit':
    case 'sent':
      return 'badge-outline badge-success';
    case 'installation_confirmed':
      return 'badge-outline badge-info';
    case 'returned':
      return 'badge-outline badge-neutral';
    default:
      return 'badge-outline badge-neutral';
  }
}

export default function OrdersPage() {
  const { t, i18n } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    api.get('orders')
      .then((r) => { if (r.data.success) setOrders(r.data.data || []); })
      .catch(() => setOrders([]))
      .finally(() => setLoading(false));
  }, [user]);

  if (authLoading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!user) {
    return (
      <div className="text-center py-8">
        <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>
      </div>
    );
  }

  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;

  const lastOrder = orders.length > 0 ? orders[0] : null;
  const lastOrderIsClosed = lastOrder ? isOrderClosed(lastOrder.status) : false;

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <PageTitle>{t('shop.orders')}</PageTitle>

      {/* ── Active order tracker / invite banner ── */}
      {orders.length === 0 ? (
        <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl mb-6">
          <div className="card-body py-6 flex flex-col items-center gap-3 text-center">
            <p className="text-base-content/70 text-sm">{t('shop.orders.empty')}</p>
            <Link to="/" className="btn btn-primary btn-sm">{t('shop.order.tracker.browse_products')}</Link>
          </div>
        </div>
      ) : lastOrderIsClosed ? (
        <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl mb-6">
          <div className="card-body py-6 flex flex-col items-center gap-3 text-center">
            <span className="text-3xl">🎉</span>
            <p className="text-base-content font-medium">{t('shop.order.tracker.invite_browse')}</p>
            <Link to="/" className="btn btn-primary btn-sm">{t('shop.order.tracker.browse_products')}</Link>
          </div>
        </div>
      ) : (
        <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl mb-6">
          <div className="card-body py-4 px-4 sm:px-6 gap-3">
            <div className="flex flex-wrap items-center justify-between gap-2">
              <span className="text-sm font-semibold text-base-content/80">
                {t('shop.order.tracker.last_order_title')} · #{lastOrder.id}
              </span>
              <Link to={`/orders/${lastOrder.id}`} className="btn btn-ghost btn-xs shrink-0">
                {t('shop.order.tracker.view_order')}
              </Link>
            </div>
            <OrderStatusTracker order={lastOrder} />
          </div>
        </div>
      )}

      {/* ── Orders list ── */}
      {orders.length > 0 && (
        <ul className="space-y-3">
          {orders.map((o) => {
            const statusKey = `shop.status.${o.status}`;
            const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : o.status;
            const closed = isOrderClosed(o.status);
            const paymentDue = !closed && !o.has_payment && o.can_pay;

            return (
              <li key={o.id}>
                <Link
                  to={`/orders/${o.id}`}
                  className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl hover:border-primary/40 hover:shadow-md transition-all block"
                >
                  <div className="card-body py-4 px-4 sm:px-5 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between min-w-0">
                    {/* Left: order info */}
                    <div className="min-w-0 flex flex-col gap-1">
                      <div className="flex flex-wrap items-center gap-2">
                        <span className="font-bold text-base">
                          {t('shop.order')} <span className="text-primary">#{o.id}</span>
                        </span>
                        <span className={`badge badge-sm ${statusBadgeClass(o.status)}`}>
                          {statusLabel}
                        </span>
                        {paymentDue && (
                          <span className="badge badge-warning badge-sm">
                            {t('shop.order.list_payment_due')}
                          </span>
                        )}
                      </div>
                      <p className="text-xs text-base-content/60 tabular-nums">
                        {o.order_date
                          ? new Date(o.order_date).toLocaleDateString(i18n.language, {
                              year: 'numeric',
                              month: 'short',
                              day: 'numeric',
                            })
                          : ''}
                        {o.installation_requested && (
                          <span className="ml-2 badge badge-xs badge-outline">
                            {t('shop.order.installation_fee')}
                          </span>
                        )}
                      </p>
                    </div>

                    {/* Right: total + cta */}
                    <div className="flex items-center justify-between sm:justify-end gap-4 shrink-0">
                      <span className={`text-lg font-bold tabular-nums ${closed ? 'text-base-content/70' : 'text-primary'}`}>
                        {Number(o.grand_total ?? o.total ?? 0).toFixed(2)} €
                      </span>
                      <span className="btn btn-ghost btn-sm hidden sm:inline-flex">
                        {t('common.detail')}
                      </span>
                    </div>
                  </div>
                </Link>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
