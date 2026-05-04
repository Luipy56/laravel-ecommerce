import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';

export default function OrdersPage() {
  const { t } = useTranslation();
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

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <PageTitle>{t('shop.orders')}</PageTitle>
      {orders.length === 0 ? (
        <p>{t('shop.orders.empty')}</p>
      ) : (
        <ul className="space-y-4">
          {orders.map((o) => {
            const statusKey = `shop.status.${o.status}`;
            const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : o.status;
            const paymentDue = !o.has_payment && o.can_pay;
            return (
              <li key={o.id} className="card bg-base-100 shadow">
                <div className="card-body flex flex-col gap-4 min-w-0 sm:flex-row sm:justify-between sm:items-center">
                  <div className="min-w-0">
                    <p className="font-semibold flex flex-wrap items-center gap-2">
                      <span>{t('shop.order')} #{o.id}</span>
                      {paymentDue ? (
                        <span className="badge badge-warning badge-sm font-normal">{t('shop.order.list_payment_due')}</span>
                      ) : null}
                    </p>
                    <p className="text-sm text-base-content/70 break-words">
                      {o.order_date ? new Date(o.order_date).toLocaleDateString() : ''} · {statusLabel}
                    </p>
                  </div>
                  <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end sm:gap-2 shrink-0">
                    <span className="font-semibold tabular-nums sm:text-end">
                      {Number(o.grand_total ?? o.total ?? 0).toFixed(2)} €
                    </span>
                    <div className="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                      <Link to={`/orders/${o.id}`} className="btn btn-ghost btn-sm w-full sm:w-auto">
                        {t('common.detail')}
                      </Link>
                    </div>
                  </div>
                </div>
              </li>
            );
          })}
        </ul>
      )}
    </div>
  );
}
