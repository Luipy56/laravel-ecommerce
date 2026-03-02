import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

function lineDisplayName(line) {
  if (line.product) return line.product.name + (line.product.code ? ` (${line.product.code})` : '');
  if (line.pack) return line.pack.name;
  return '—';
}

export default function AdminOrderShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

  const fetchOrder = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/orders/${id}`);
      if (data.success) setOrder(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchOrder();
  }, [fetchOrder]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/orders" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !order) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  const isOrder = order.kind === 'order';
  const shippingAddress = order.addresses?.find((a) => a.type === 'shipping');
  const installationAddress = order.addresses?.find((a) => a.type === 'installation');

  const [labelModalOpen, setLabelModalOpen] = useState(false);

  const handlePrintLabel = () => {
    if (!shippingAddress) return;
    const addressLines = [shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ');
    const linesSummary = (order.lines || []).map((l) => `${lineDisplayName(l)} × ${l.quantity}`).join('\n');
    const orderDateStr = order.order_date ? new Date(order.order_date).toLocaleDateString() : '';
    const doc = `
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>${t('admin.orders.shipping_label_title')} #${order.id}</title>
<style>
  body { font-family: system-ui, sans-serif; font-size: 14px; line-height: 1.4; padding: 16px; max-width: 400px; }
  .ref { font-size: 18px; font-weight: bold; margin-bottom: 12px; }
  .ship-to { margin-bottom: 12px; }
  .ship-to strong { display: block; font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 4px; }
  .items { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 12px; font-size: 12px; white-space: pre-line; }
  .date { margin-top: 8px; color: #666; font-size: 12px; }
</style></head>
<body>
  <div class="ref">${t('admin.orders.shipping_label_ref')} #${order.id}</div>
  <div class="ship-to">
    <strong>${t('admin.orders.shipping_label_ship_to')}</strong>
    ${order.client?.login_email ? `<div>${order.client.login_email}</div>` : ''}
    <div>${addressLines}</div>
    ${shippingAddress.note ? `<div style="margin-top:6px;font-size:12px;color:#666">${shippingAddress.note.replace(/</g, '&lt;')}</div>` : ''}
  </div>
  <div class="date">${t('admin.orders.order_date')}: ${orderDateStr}</div>
  <div class="items">${linesSummary || '—'}</div>
</body></html>`;
    const w = window.open('', '_blank', 'width=420,height=600');
    if (w) {
      w.document.write(doc);
      w.document.close();
      w.focus();
      setTimeout(() => w.print(), 150);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">
          {t('admin.orders.title')} #{order.id}
          <span className="ml-2 badge badge-ghost text-sm font-normal">{t(`admin.orders.kind_${order.kind}`)}</span>
        </PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          {isOrder && shippingAddress && (
            <button type="button" className="btn btn-outline btn-sm shrink-0" onClick={() => setLabelModalOpen(true)} aria-label={t('admin.orders.shipping_label_button')}>
              {t('admin.orders.shipping_label_button')}
            </button>
          )}
          {isOrder && (
            <Link to={`/admin/orders/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-base">{t('admin.orders.client_and_dates')}</h2>
            <dl className="grid grid-cols-1 gap-3">
              {order.client && (
                <div>
                  <dt className="text-sm text-base-content/70">{t('admin.orders.client')}</dt>
                  <dd><Link to={`/admin/clients/${order.client.id}`} className="link link-hover">{order.client.login_email}</Link></dd>
                  {order.client.identification && <dd className="text-sm text-base-content/70">{order.client.identification}</dd>}
                </div>
              )}
              <div><dt className="text-sm text-base-content/70">{t('admin.orders.kind')}</dt><dd>{t(`admin.orders.kind_${order.kind}`)}</dd></div>
              {isOrder && order.status && <div><dt className="text-sm text-base-content/70">{t('admin.orders.status')}</dt><dd><span className="badge badge-primary badge-sm">{t(`admin.orders.status_${order.status}`)}</span></dd></div>}
              {order.order_date && <div><dt className="text-sm text-base-content/70">{t('admin.orders.order_date')}</dt><dd>{new Date(order.order_date).toLocaleString()}</dd></div>}
              {order.shipping_date && <div><dt className="text-sm text-base-content/70">{t('admin.orders.shipping_date')}</dt><dd>{new Date(order.shipping_date).toLocaleString()}</dd></div>}
              {order.shipping_price != null && <div><dt className="text-sm text-base-content/70">{t('admin.orders.shipping_price')}</dt><dd>{Number(order.shipping_price).toFixed(2)} €</dd></div>}
            </dl>
          </div>
        </div>

        {(shippingAddress || installationAddress) && (
          <div className="card bg-base-100 shadow border border-base-200">
            <div className="card-body">
              <h2 className="card-title text-base">{t('admin.orders.addresses')}</h2>
              <div className="space-y-4">
                {shippingAddress && (
                  <div>
                    <h3 className="text-sm font-semibold text-base-content/70">{t('admin.orders.address_shipping')}</h3>
                    <p className="whitespace-pre-wrap">{[shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ')}</p>
                    {shippingAddress.note && <p className="text-sm text-base-content/70 mt-1">{shippingAddress.note}</p>}
                  </div>
                )}
                {installationAddress && (
                  <div>
                    <h3 className="text-sm font-semibold text-base-content/70">{t('admin.orders.address_installation')}</h3>
                    <p className="whitespace-pre-wrap">{[installationAddress.street, installationAddress.city, installationAddress.province, installationAddress.postal_code].filter(Boolean).join(', ')}</p>
                    {installationAddress.note && <p className="text-sm text-base-content/70 mt-1">{installationAddress.note}</p>}
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        <div className="card-body p-0">
          <h2 className="card-title text-base px-4 pt-4 pb-2">{t('admin.orders.lines')}</h2>
          <div className="overflow-x-auto">
            <table className="table table-zebra table-pin-rows">
              <thead>
                <tr>
                  <th>{t('admin.orders.line_product')}</th>
                  <th className="text-center">{t('admin.orders.line_quantity')}</th>
                  <th className="text-end">{t('admin.orders.line_unit_price')}</th>
                  <th className="text-end">{t('admin.orders.line_total')}</th>
                </tr>
              </thead>
              <tbody>
                {(order.lines || []).map((line) => (
                  <tr key={line.id}>
                    <td>
                      {lineDisplayName(line)}
                      {line.is_installation_requested && <span className="badge badge-sm badge-ghost ml-1">{t('admin.orders.installation')}</span>}
                      {line.extra_keys_qty > 0 && <span className="badge badge-sm badge-ghost ml-1">+{line.extra_keys_qty} {t('admin.orders.extra_keys')}</span>}
                    </td>
                    <td className="text-center">{line.quantity}</td>
                    <td className="text-end">{line.unit_price != null ? Number(line.unit_price).toFixed(2) : '—'} €</td>
                    <td className="text-end font-medium">{line.line_total != null ? Number(line.line_total).toFixed(2) : '0.00'} €</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          <div className="flex justify-end px-4 pb-4 pt-2 border-t border-base-200">
            <p className="text-lg font-semibold">
              {t('admin.orders.total')}: {order.total != null ? Number(order.total).toFixed(2) : '0.00'} €
            </p>
          </div>
        </div>
      </div>

      {order.payments && order.payments.length > 0 && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-base">{t('admin.orders.payments')}</h2>
            <div className="overflow-x-auto">
              <table className="table table-zebra table-sm">
                <thead>
                  <tr>
                    <th>{t('admin.orders.payment_method')}</th>
                    <th className="text-end">{t('admin.orders.payment_amount')}</th>
                    <th>{t('admin.orders.payment_paid_at')}</th>
                    <th>{t('admin.orders.payment_reference')}</th>
                  </tr>
                </thead>
                <tbody>
                  {order.payments.map((p) => (
                    <tr key={p.id}>
                      <td>{t(`admin.orders.payment_${p.payment_method}`)}</td>
                      <td className="text-end font-medium">{Number(p.amount).toFixed(2)} €</td>
                      <td>{p.paid_at ? new Date(p.paid_at).toLocaleString() : '—'}</td>
                      <td className="font-mono text-sm">{p.gateway_reference ?? ''}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      <div className="text-sm text-base-content/70">
        {t('admin.orders.created_at')}: {order.created_at ? new Date(order.created_at).toLocaleString() : '—'}
        {order.updated_at && <> · {t('admin.orders.updated_at')}: {new Date(order.updated_at).toLocaleString()}</>}
      </div>

      {/* Shipping label modal */}
      <dialog className={`modal ${labelModalOpen ? 'modal-open' : ''}`} aria-label={t('admin.orders.shipping_label_title')}>
        <div className="modal-box max-w-md">
          <h3 className="font-bold text-lg">{t('admin.orders.shipping_label_title')} #{order.id}</h3>
          {shippingAddress && (
            <div className="py-4">
              <div className="rounded-lg border border-base-300 bg-base-200 p-4 text-sm">
                <p className="font-semibold text-base-content/70 text-xs uppercase tracking-wide mb-1">{t('admin.orders.shipping_label_ship_to')}</p>
                {order.client?.login_email && <p>{order.client.login_email}</p>}
                <p className="mt-1">{[shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ')}</p>
                {shippingAddress.note && <p className="mt-2 text-base-content/70">{shippingAddress.note}</p>}
                <p className="mt-3 text-base-content/70">{t('admin.orders.order_date')}: {order.order_date ? new Date(order.order_date).toLocaleDateString() : '—'}</p>
                <p className="mt-1 font-mono text-xs">{(order.lines || []).map((l) => `${lineDisplayName(l)} × ${l.quantity}`).join(' · ') || '—'}</p>
              </div>
            </div>
          )}
          <div className="modal-action">
            <button type="button" className="btn btn-ghost" onClick={() => setLabelModalOpen(false)}>{t('common.close')}</button>
            <button type="button" className="btn btn-primary" onClick={() => { handlePrintLabel(); setLabelModalOpen(false); }}>{t('admin.orders.shipping_label_print')}</button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop" onSubmit={() => setLabelModalOpen(false)}>
          <button type="submit" aria-label={t('common.close')}>{t('common.close')}</button>
        </form>
      </dialog>
    </div>
  );
}
