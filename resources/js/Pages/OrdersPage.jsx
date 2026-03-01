import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';

export default function OrdersPage() {
  const { t } = useTranslation();
  const { user } = useAuth();
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    api.get('orders')
      .then((r) => { if (r.data.success) setOrders(r.data.data || []); })
      .catch(() => setOrders([]))
      .finally(() => setLoading(false));
  }, [user]);

  if (!user) {
    return (
      <div className="text-center py-8">
        <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>
      </div>
    );
  }

  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;

  return (
    <div className="max-w-3xl mx-auto">
      <PageTitle>{t('shop.orders')}</PageTitle>
      {orders.length === 0 ? (
        <p>{t('shop.cart.empty')}</p>
      ) : (
        <ul className="space-y-4">
          {orders.map((o) => (
            <li key={o.id} className="card bg-base-100 shadow">
              <div className="card-body flex-row justify-between items-center">
                <div>
                  <p className="font-semibold">Comanda #{o.id}</p>
                  <p className="text-sm text-base-content/70">
                    {o.order_date ? new Date(o.order_date).toLocaleDateString() : ''} — {o.status}
                  </p>
                </div>
                <div className="flex items-center gap-2">
                  <span className="font-semibold">{Number(o.total).toFixed(2)} €</span>
                  <Link to={`/orders/${o.id}`} className="btn btn-ghost btn-sm">Detall</Link>
                  <a href={`/api/v1/orders/${o.id}/invoice`} target="_blank" rel="noopener noreferrer" className="btn btn-ghost btn-sm">
                    {t('shop.invoice')}
                  </a>
                </div>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
