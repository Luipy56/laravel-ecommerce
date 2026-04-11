import React, { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import i18n from 'i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';
import PayPalInlineButtons from '../components/payments/PayPalInlineButtons';

export default function OrderDetailPage() {
  const { id } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [payLoading, setPayLoading] = useState(false);
  const [payError, setPayError] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('card');
  const [inlineCheckout, setInlineCheckout] = useState(null);
  const [stripeUiError, setStripeUiError] = useState('');

  useEffect(() => {
    if (!user) return;
    setLoading(true);
    api.get(`orders/${id}`)
      .then((r) => { if (r.data.success) setOrder(r.data.data); })
      .catch(() => setOrder(null))
      .finally(() => setLoading(false));
  }, [user, id]);

  useEffect(() => {
    const msg = location.state?.paymentErrorFromCheckout;
    if (msg) {
      setPayError(String(msg));
      navigate(location.pathname, { replace: true, state: {} });
    }
  }, [location.state, location.pathname, navigate]);

  useEffect(() => {
    if (!order?.payment_methods_available) return;
    const m = order.payment_methods_available;
    setPaymentMethod((pm) => (m[pm] ? pm : ['card', 'paypal'].find((k) => m[k]) || 'card'));
  }, [order?.payment_methods_available, order?.id]);

  useEffect(() => {
    if (!user || !id) return;
    const sp = new URLSearchParams(window.location.search);
    const payment = sp.get('payment');
    const needsRefresh = payment != null || sp.has('payment_intent') || sp.has('redirect_status');
    if (!needsRefresh) return;
    if (payment === 'ko') setPayError(t('shop.order.payment_return_ko'));
    api.get(`orders/${id}`).then((r) => {
      if (r.data.success) setOrder(r.data.data);
    });
    navigate(`/orders/${id}`, { replace: true });
    // eslint-disable-next-line react-hooks/exhaustive-deps -- t() for ko message only; omit t to avoid effect loops
  }, [user, id, navigate]);

  const handlePay = async (e) => {
    e.preventDefault();
    setPayError('');
    setStripeUiError('');
    setInlineCheckout(null);
    setPayLoading(true);
    try {
      const { data } = await api.post(`orders/${id}/pay`, { payment_method: paymentMethod });
      if (!data.success) {
        setPayError(data.message || t('common.error'));
        return;
      }
      const d = data.data;
      if (d.has_payment) {
        const r = await api.get(`orders/${id}`);
        if (r.data.success) setOrder(r.data.data);
        return;
      }
      const c = d.payment_checkout;
      if (c?.gateway === 'stripe' && c.checkout_url) {
        window.location.href = c.checkout_url;
        return;
      }
      if (c?.gateway === 'paypal' && c.approval_url) {
        window.location.href = c.approval_url;
        return;
      }
      if (c?.gateway === 'paypal' && c.client_id && c.paypal_order_id && c.payment_id) {
        setInlineCheckout({
          orderId: Number(id),
          paypal: {
            client_id: c.client_id,
            paypal_order_id: c.paypal_order_id,
            payment_id: c.payment_id,
          },
        });
        return;
      }
      const r = await api.get(`orders/${id}`);
      if (r.data.success) setOrder(r.data.data);
    } catch (err) {
      const d = err.response?.data;
      const msg =
        d?.code === 'payment_method_not_configured'
          ? t('shop.payment.method_unavailable')
          : d?.message || t('common.error');
      setPayError(msg);
    } finally {
      setPayLoading(false);
    }
  };

  const handleWaiveInstallation = async () => {
    setPayError('');
    setPayLoading(true);
    try {
      const { data } = await api.post(`orders/${id}/waive-installation`);
      if (data.success) {
        const r = await api.get(`orders/${id}`);
        if (r.data.success) setOrder(r.data.data);
      } else setPayError(data.message || t('common.error'));
    } catch (err) {
      setPayError(err.response?.data?.message || t('common.error'));
    } finally {
      setPayLoading(false);
    }
  };

  if (authLoading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!user) return <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>;
  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  if (!order) return <p>{t('common.error')}</p>;

  const statusKey = `shop.status.${order.status}`;
  const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : order.status;
  const shippingAddress = order.addresses?.find((a) => a.type === 'shipping');
  const installationAddress = order.addresses?.find((a) => a.type === 'installation');

  const formatAddress = (addr) => [addr.street, addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ');

  const linesSubtotal = order.lines_subtotal ?? order.lines?.reduce((s, l) => s + Number(l.line_total), 0) ?? 0;
  const grandTotal = order.grand_total ?? order.total ?? linesSubtotal;
  const showInstallationRow = order.installation_requested && order.installation_status === 'priced' && order.installation_price != null;
  const awaitingQuote = order.installation_requested && order.installation_status === 'pending' && order.status === 'awaiting_installation_price';
  const canPay = order.can_pay && !order.has_payment;
  const payAvail = order.payment_methods_available ?? { card: false, paypal: false };
  const paymentsSimulated = !!order.payments_simulated;
  const anyPaymentMethod = Object.values(payAvail).some(Boolean);

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <div className="flex flex-wrap items-center justify-between gap-2 mb-4">
        <PageTitle className="mb-0">{t('shop.order')} #{order.id}</PageTitle>
        <Link to="/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <p className="text-base-content/80"><strong>{t('shop.order_status')}:</strong> {statusLabel}</p>
      <p className="text-base-content/80"><strong>{t('shop.order_date')}:</strong> {order.order_date ? new Date(order.order_date).toLocaleString() : ''}</p>

      {awaitingQuote && (
        <div role="status" className="alert alert-warning mt-4 text-sm">
          {t('shop.order.awaiting_installation_quote')}
        </div>
      )}

      {!awaitingQuote && order.status === 'awaiting_payment' && !order.has_payment && (
        <div role="status" className="alert alert-info mt-4 text-sm">
          {t('shop.order.awaiting_payment_notice')}
        </div>
      )}

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
              <tr className="border-t border-base-300">
                <td colSpan={3} className="text-end font-medium">{t('shop.order.lines_subtotal')}</td>
                <td className="text-right tabular-nums font-medium">{Number(linesSubtotal).toFixed(2)} €</td>
              </tr>
              <tr>
                <td colSpan={3} className="text-end font-medium">{t('shop.shipping_flat')}</td>
                <td className="text-right tabular-nums font-medium">{Number(order.shipping_flat_eur ?? 9).toFixed(2)} €</td>
              </tr>
              {showInstallationRow && (
                <tr>
                  <td colSpan={3} className="text-end font-medium">{t('shop.order.installation_fee')}</td>
                  <td className="text-right tabular-nums font-medium">{Number(order.installation_price).toFixed(2)} €</td>
                </tr>
              )}
              <tr className="bg-base-100 border-t border-base-300">
                <td colSpan={3} className="text-end font-bold">{t('shop.total')}</td>
                <td className="text-right py-4 text-xl font-bold text-primary tabular-nums">
                  {Number(grandTotal).toFixed(2)} €
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {payError && <div role="alert" className="alert alert-error mt-4 text-sm">{payError}</div>}

      {awaitingQuote && (
        <div className="mt-4">
          <p className="text-sm text-base-content/70 mb-2">{t('shop.order.waive_installation_hint')}</p>
          <button type="button" className="btn btn-outline btn-sm" disabled={payLoading} onClick={handleWaiveInstallation}>
            {t('shop.order.waive_installation')}
          </button>
        </div>
      )}

      {canPay && !anyPaymentMethod && !paymentsSimulated && (
        <div role="alert" className="alert alert-warning mt-4 text-sm space-y-2">
          <p className="m-0">{t('shop.order.payment_no_methods')}</p>
          <p className="m-0 text-base-content/80">{t('shop.order.payment_how_it_works')}</p>
          {order.local_checkout_needs_debug ? (
            <p className="m-0 text-base-content/90">{t('checkout.payment.local_debug_hint')}</p>
          ) : null}
        </div>
      )}

      {canPay && order.paypal_missing_credentials && (
        <div role="status" className="alert alert-info mt-4 text-sm">
          {t('checkout.payment.paypal_missing_credentials_hint')}
        </div>
      )}
      {canPay && order.stripe_missing_credentials && (
        <div role="status" className="alert alert-info mt-4 text-sm">
          {t('checkout.payment.stripe_missing_credentials_hint')}
        </div>
      )}

      {canPay && (
        <form onSubmit={handlePay} className="card bg-base-100 shadow border border-base-300 rounded-2xl mt-4">
          <div className="card-body">
            <h2 className="card-title text-base">{t('shop.order.pay_now')}</h2>
            <label className="form-field w-full max-w-xs">
              <span className="form-label">{t('shop.order.payment_method')}</span>
              <select
                className="select select-bordered w-full"
                value={paymentMethod}
                onChange={(e) => setPaymentMethod(e.target.value)}
                disabled={
                  !!inlineCheckout?.paypal ||
                  (!anyPaymentMethod && !paymentsSimulated)
                }
              >
                {['card', 'paypal']
                  .filter((value) => payAvail[value])
                  .map((value) => (
                    <option key={value} value={value}>
                      {t(`checkout.payment.${value}`)}
                    </option>
                  ))}
              </select>
            </label>
            <button
              type="submit"
              className="btn btn-primary mt-2"
              disabled={
                payLoading ||
                !!inlineCheckout?.paypal ||
                (!anyPaymentMethod && !paymentsSimulated)
              }
            >
              {payLoading ? t('common.loading') : t('shop.order.pay_now')}
            </button>
            {inlineCheckout?.paypal && (
              <div className="mt-6 pt-6 border-t border-base-300 space-y-3">
                <p className="text-sm text-base-content/70">{t('checkout.payment.paypal_help')}</p>
                <PayPalInlineButtons
                  clientId={inlineCheckout.paypal.client_id}
                  paypalOrderId={inlineCheckout.paypal.paypal_order_id}
                  paymentId={inlineCheckout.paypal.payment_id}
                  onSuccess={async () => {
                    const r = await api.get(`orders/${id}`);
                    if (r.data.success) setOrder(r.data.data);
                    setInlineCheckout(null);
                  }}
                  onError={(msg) => setStripeUiError(msg)}
                />
                {stripeUiError ? <div role="alert" className="alert alert-error text-sm">{stripeUiError}</div> : null}
              </div>
            )}
          </div>
        </form>
      )}

      {!canPay && order.has_payment && (
        <div className="mt-4 flex justify-end">
          <a href={`/api/v1/orders/${order.id}/invoice?locale=${i18n.language || 'ca'}`} target="_blank" rel="noopener noreferrer" className="btn btn-primary btn-sm">
            {t('shop.invoice')}
          </a>
        </div>
      )}
    </div>
  );
}
