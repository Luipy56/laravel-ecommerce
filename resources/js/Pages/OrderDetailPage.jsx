import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';

export default function OrderDetailPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const { user } = useAuth();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!user) return;
    api.get(`orders/${id}`)
      .then((r) => { if (r.data.success) setOrder(r.data.data); })
      .catch(() => setOrder(null))
      .finally(() => setLoading(false));
  }, [user, id]);

  if (!user) return <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>;
  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  if (!order) return <p>{t('common.error')}</p>;

  return (
    <div className="max-w-3xl mx-auto">
      <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
        <PageTitle className="mb-0">Comanda #{order.id}</PageTitle>
        <Link to="/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <p><strong>Estat:</strong> {order.status}</p>
      <p><strong>Data:</strong> {order.order_date ? new Date(order.order_date).toLocaleString() : ''}</p>
      <table className="table table-zebra mt-4">
        <thead>
          <tr>
            <th>Producte / Pack</th>
            <th className="text-right">{t('shop.quantity')}</th>
            <th className="text-right">{t('shop.price')}</th>
            <th className="text-right">{t('shop.total')}</th>
          </tr>
        </thead>
        <tbody>
          {order.lines?.map((l) => (
            <tr key={l.id}>
              <td>{l.product?.name ?? l.pack?.name}</td>
              <td className="text-right">{l.quantity}</td>
              <td className="text-right">{Number(l.unit_price).toFixed(2)} €</td>
              <td className="text-right">{Number(l.line_total).toFixed(2)} €</td>
            </tr>
          ))}
        </tbody>
      </table>
      <p className="text-xl font-semibold mt-4">{t('shop.total')}: {Number(order.total).toFixed(2)} €</p>
      <a href={`/api/v1/orders/${order.id}/invoice`} target="_blank" rel="noopener noreferrer" className="btn btn-primary mt-4">
        {t('shop.invoice')}
      </a>
    </div>
  );
}
