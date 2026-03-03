import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import i18n from 'i18next';
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

  const statusKey = `shop.status.${order.status}`;
  const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : order.status;
  const shippingAddress = order.addresses?.find((a) => a.type === 'shipping');
  const installationAddress = order.addresses?.find((a) => a.type === 'installation');

  const formatAddress = (addr) => [addr.street, addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ');

  return (
    <div className="max-w-3xl mx-auto">
      <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
        <PageTitle className="mb-0">{t('shop.order')} #{order.id}</PageTitle>
        <Link to="/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <p className="text-base-content/80"><strong>{t('shop.order_status')}:</strong> {statusLabel}</p>
      <p className="text-base-content/80"><strong>{t('shop.order_date')}:</strong> {order.order_date ? new Date(order.order_date).toLocaleString() : ''}</p>

      {(shippingAddress || installationAddress) && (
        <div className="card bg-base-100 shadow border border-base-300 rounded-2xl mt-4">
          <div className="card-body">
            <h2 className="card-title text-base">{t('shop.order_addresses')}</h2>
            <div className="grid gap-4 sm:grid-cols-2">
              {shippingAddress && (
                <div>
                  <h3 className="text-sm font-semibold text-base-content/70">{t('shop.order_address_shipping')}</h3>
                  <p className="whitespace-pre-wrap">{formatAddress(shippingAddress)}</p>
                  {shippingAddress.note && <p className="text-sm text-base-content/70 mt-1">{shippingAddress.note}</p>}
                </div>
              )}
              {installationAddress && (
                <div>
                  <h3 className="text-sm font-semibold text-base-content/70">{t('shop.order_address_installation')}</h3>
                  <p className="whitespace-pre-wrap">{formatAddress(installationAddress)}</p>
                  {installationAddress.note && <p className="text-sm text-base-content/70 mt-1">{installationAddress.note}</p>}
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      <div className="card bg-base-100 shadow border border-base-300 overflow-hidden rounded-2xl mt-4">
        <div className="overflow-x-auto">
          <table className="table">
            <thead>
              <tr className="bg-base-100 border-b border-base-300">
                <th>{t('shop.order_product_pack')}</th>
                <th className="text-center whitespace-nowrap">{t('shop.quantity')}</th>
                <th className="text-center whitespace-nowrap">{t('shop.price')}</th>
                <th className="text-right whitespace-nowrap">{t('shop.total')}</th>
              </tr>
            </thead>
            <tbody>
              {order.lines?.map((l) => (
                <tr key={l.id}>
                  <td>{l.product?.name ?? l.pack?.name}</td>
                  <td className="text-center align-middle">{l.quantity}</td>
                  <td className="text-center align-middle">{Number(l.unit_price).toFixed(2)} €</td>
                  <td className="text-right align-middle">{Number(l.line_total).toFixed(2)} €</td>
                </tr>
              ))}
            </tbody>
            <tfoot>
              <tr className="bg-base-100 border-t border-base-300">
                <td colSpan={3} />
                <td className="text-right py-4">
                  <div className="flex flex-col sm:flex-row justify-end items-end sm:items-center gap-3">
                    <p className="text-xl font-bold text-primary m-0">{t('shop.total')}: {Number(order.total).toFixed(2)} €</p>
                    <a href={`/api/v1/orders/${order.id}/invoice?locale=${i18n.language || 'ca'}`} target="_blank" rel="noopener noreferrer" className="btn btn-primary btn-sm shrink-0">
                      {t('shop.invoice')}
                    </a>
                  </div>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  );
}
