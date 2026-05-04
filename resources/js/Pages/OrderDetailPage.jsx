import React, { useEffect, useState } from 'react';
import { Link, useLocation, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';
import { CHECKOUT_PAYMENT_METHOD_ORDER } from '../validation';
import PayPalInlineButtons from '../components/payments/PayPalInlineButtons';
import { openPayPalApprovalInNewTab } from '../payments/openPayPalApprovalInNewTab';
import { emitAppToast } from '../toastEvents';

/** Pack lines have `pack_id` or nested `pack`; everything else is treated as product lines. */
function partitionOrderLines(lines) {
  const list = lines || [];
  const packs = list.filter((l) => l.pack_id || l.pack);
  const products = list.filter((l) => !(l.pack_id || l.pack));
  return { products, packs };
}

function storefrontOrderStatusBadgeClass(status) {
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

function rmaStatusBadgeClass(status) {
  switch (status) {
    case 'pending_review': return 'badge-warning';
    case 'approved': return 'badge-info';
    case 'refunded': return 'badge-success';
    case 'rejected': return 'badge-error';
    case 'cancelled': return 'badge-neutral';
    default: return 'badge-neutral';
  }
}

function StorefrontOrderLinesTable({ lines, nameHeaderKey, t }) {
  if (lines.length === 0) return null;
  return (
    <div className="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
      <table className="table table-sm w-full [&_th]:text-base-content/80 [&_td]:align-middle">
        <thead>
          <tr className="bg-base-200/60 border-b border-base-300">
            <th className="font-semibold">{t(nameHeaderKey)}</th>
            <th className="text-center whitespace-nowrap font-semibold">{t('shop.quantity')}</th>
            <th className="text-center whitespace-nowrap font-semibold">{t('shop.price')}</th>
            <th className="text-end whitespace-nowrap font-semibold">{t('shop.total')}</th>
          </tr>
        </thead>
        <tbody>
          {lines.map((l) => (
            <tr key={l.id} className="border-b border-base-200 last:border-b-0">
              <td className="min-w-0 max-w-[min(100vw,22rem)] sm:max-w-none">{l.product?.name ?? l.pack?.name}</td>
              <td className="text-center tabular-nums">{l.quantity}</td>
              <td className="text-center tabular-nums">{Number(l.unit_price).toFixed(2)} €</td>
              <td className="text-end tabular-nums font-medium">{Number(l.line_total).toFixed(2)} €</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default function OrderDetailPage() {
  const { id } = useParams();
  const location = useLocation();
  const navigate = useNavigate();
  const { t, i18n } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const [order, setOrder] = useState(null);
  const [loading, setLoading] = useState(true);
  const [payLoading, setPayLoading] = useState(false);
  const [payError, setPayError] = useState('');
  const [paymentMethod, setPaymentMethod] = useState('card');
  const [inlineCheckout, setInlineCheckout] = useState(null);
  const [stripeUiError, setStripeUiError] = useState('');
  const [paypalApprovalFallbackUrl, setPaypalApprovalFallbackUrl] = useState(null);
  const [paypalReturnInfo, setPaypalReturnInfo] = useState('');
  const [payWarning, setPayWarning] = useState('');
  const [returnModalOpen, setReturnModalOpen] = useState(false);
  const [returnReason, setReturnReason] = useState('');
  const [returnLoading, setReturnLoading] = useState(false);
  const [returnError, setReturnError] = useState('');
  const [existingRma, setExistingRma] = useState(null);
  useEffect(() => {
    if (!user) return;
    setLoading(true);
    api.get(`orders/${id}`)
      .then((r) => { if (r.data.success) setOrder(r.data.data); })
      .catch(() => setOrder(null))
      .finally(() => setLoading(false));
  }, [user, id]);

  useEffect(() => {
    if (!user || !id) return;
    api.get('return-requests')
      .then((r) => {
        if (r.data.success) {
          const match = (r.data.data || []).find((rma) => rma.order_id === Number(id));
          setExistingRma(match || null);
        }
      })
      .catch(() => {});
  }, [user, id]);

  useEffect(() => {
    const msg = location.state?.paymentErrorFromCheckout;
    if (msg) {
      setPayError(String(msg));
      navigate(location.pathname, { replace: true, state: {} });
    }
  }, [location.state, location.pathname, navigate]);

  useEffect(() => {
    if (!location.state?.paypalHostedWindow) return;
    emitAppToast(t('shop.order.paypal_window_hint'), 'info');
    navigate(location.pathname, { replace: true, state: {} });
  }, [location.state, location.pathname, navigate, t]);

  // Handoff from CheckoutPage when PayPal is selected: render the same inline buttons block here.
  useEffect(() => {
    const inline = location.state?.paypalInlineCheckout;
    if (!inline) return;
    setInlineCheckout({
      orderId: Number(id),
      paypal: {
        client_id: inline.client_id,
        paypal_order_id: inline.paypal_order_id,
        payment_id: inline.payment_id,
        paypal_mode: inline.paypal_mode === 'live' ? 'live' : 'sandbox',
      },
    });
    navigate(location.pathname, { replace: true, state: {} });
  }, [location.state, location.pathname, navigate, id]);

  useEffect(() => {
    if (!order?.payment_methods_available) return;
    const m = order.payment_methods_available;
    setPaymentMethod((pm) => (m[pm] ? pm : CHECKOUT_PAYMENT_METHOD_ORDER.find((k) => m[k]) || 'card'));
  }, [order?.payment_methods_available, order?.id]);

  useEffect(() => {
    if (!user || !id) return;
    const sp = new URLSearchParams(window.location.search);
    const payment = sp.get('payment');
    const sessionId = sp.get('session_id');
    const needsStripeConfirm = payment === 'ok' && !!sessionId;
    const needsRefresh =
      payment != null || sp.has('payment_intent') || sp.has('redirect_status') || needsStripeConfirm;
    if (!needsRefresh) return;

    let cancelled = false;

    (async () => {
      if (payment === 'ko') setPayError(t('shop.order.payment_return_ko'));
      if (payment === 'paypal_return') {
        setPaypalReturnInfo(t('shop.order.paypal_return_check'));
        // PayPal redirected back after approval: token = PayPal order ID, capture server-side.
        const paypalOrderId = sp.get('token');
        if (paypalOrderId) {
          try {
            const orderRes = await api.get(`orders/${id}`);
            const pendingPayment = orderRes.data?.data?.payments?.find(
              (p) => p.gateway === 'paypal' && p.gateway_reference === paypalOrderId,
            );
            if (pendingPayment && !cancelled) {
              const captureRes = await api.post('payments/paypal/capture', {
                paypal_order_id: paypalOrderId,
                payment_id: pendingPayment.id,
              });
              if (!cancelled && captureRes.data?.success) {
                emitAppToast(t('shop.order.stripe_confirm_ok'), 'success');
              }
            }
          } catch {
            // capture error is non-fatal; order reload below will reflect real status
          }
        }
      }
      if (needsStripeConfirm) {
        try {
          const { data } = await api.post('payments/stripe/checkout/confirm', { session_id: sessionId });
          if (!cancelled && data.success && data.data?.has_payment) {
            emitAppToast(t('shop.order.stripe_confirm_ok'), 'success');
          } else if (!cancelled && data?.message) {
            emitAppToast(data.message, 'warning');
          }
        } catch (err) {
          if (!cancelled) {
            const msg = err.response?.data?.message || t('common.error');
            emitAppToast(msg, 'error');
          }
        }
      }
      if (!cancelled) {
        const r = await api.get(`orders/${id}`);
        if (r.data.success) setOrder(r.data.data);
        navigate(`/orders/${id}`, { replace: true });
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [user, id, navigate, t]);

  const handlePay = async (e) => {
    e.preventDefault();
    setPayError('');
    setPayWarning('');
    setStripeUiError('');
    setPaypalApprovalFallbackUrl(null);
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
      if (c?.gateway === 'paypal' && c.client_id && c.paypal_order_id && c.payment_id) {
        setInlineCheckout({
          orderId: Number(id),
          paypal: {
            client_id: c.client_id,
            paypal_order_id: c.paypal_order_id,
            payment_id: c.payment_id,
            paypal_mode: c.paypal_mode === 'live' ? 'live' : 'sandbox',
          },
        });
        return;
      }
      if (c?.gateway === 'paypal' && c.approval_url) {
        const opened = openPayPalApprovalInNewTab(c.approval_url);
        if (!opened) setPaypalApprovalFallbackUrl(c.approval_url);
        navigate(`/orders/${id}`, { state: { paypalHostedWindow: true } });
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

  const handleRequestReturn = async (e) => {
    e.preventDefault();
    setReturnError('');
    setReturnLoading(true);
    try {
      const { data } = await api.post(`orders/${id}/return-requests`, { reason: returnReason });
      if (!data.success) {
        setReturnError(data.message || t('common.error'));
        return;
      }
      setExistingRma(data.data);
      setReturnModalOpen(false);
      setReturnReason('');
      emitAppToast(t('shop.returns.success'), 'success');
    } catch (err) {
      setReturnError(err.response?.data?.message || t('common.error'));
    } finally {
      setReturnLoading(false);
    }
  };

  if (authLoading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!user) return <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>;
  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  if (!order) return <p>{t('common.error')}</p>;

  const RETURNABLE_STATUSES = ['sent', 'installation_confirmed'];
  const canRequestReturn =
    RETURNABLE_STATUSES.includes(order.status) &&
    order.has_payment &&
    !existingRma;

  const statusKey = `shop.status.${order.status}`;
  const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : order.status;
  const { products: productLines, packs: packLines } = partitionOrderLines(order.lines);
  const shippingAddress = order.addresses?.find((a) => a.type === 'shipping');
  const installationAddress = order.addresses?.find((a) => a.type === 'installation');

  const formatAddress = (addr) => [addr.street, addr.city, addr.province, addr.postal_code].filter(Boolean).join(', ');

  const timelineLabel = (row) => {
    if (row.step === 'current' && row.status_code) {
      const sk = `shop.status.${row.status_code}`;
      return t(sk) !== sk ? t(sk) : row.status_code;
    }
    const lk = `shop.order.timeline.${row.step}`;
    return t(lk) !== lk ? t(lk) : row.step;
  };

  const linesSubtotal = order.lines_subtotal ?? order.lines?.reduce((s, l) => s + Number(l.line_total), 0) ?? 0;
  const grandTotal = order.grand_total ?? order.total ?? linesSubtotal;
  const showInstallationRow = order.installation_requested && order.installation_status === 'priced' && order.installation_price != null;
  const awaitingQuote = order.installation_requested && order.installation_status === 'pending' && order.status === 'awaiting_installation_price';
  const canPay = order.can_pay && !order.has_payment;
  const payAvail = order.payment_methods_available ?? {
    card: false,
    paypal: false,
  };
  const paymentsSimulated = !!order.payments_simulated;
  const anyPaymentMethod = Object.values(payAvail).some(Boolean);

  const displayTimeline = (order.status_timeline || []).filter((row) => row.step !== 'current');

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl">
      <div className="flex flex-wrap items-center justify-between gap-2 mb-3">
        <PageTitle className="mb-0">{t('shop.order')} #{order.id}</PageTitle>
        <Link to="/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>

      <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl mb-4">
        <div className="card-body py-4 px-4 sm:px-5">
          <dl className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
            <div className="min-w-0">
              <dt className="text-xs font-semibold uppercase tracking-wide text-base-content/60">{t('shop.order_status')}</dt>
              <dd className="mt-2">
                <span className={`badge ${storefrontOrderStatusBadgeClass(order.status)} badge-lg`}>{statusLabel}</span>
              </dd>
            </div>
            <div className="min-w-0">
              <dt className="text-xs font-semibold uppercase tracking-wide text-base-content/60">{t('shop.order_date')}</dt>
              <dd className="mt-2 text-base text-base-content tabular-nums">
                {order.order_date
                  ? new Date(order.order_date).toLocaleString(i18n.language, {
                      dateStyle: 'long',
                      timeStyle: 'short',
                    })
                  : ''}
              </dd>
            </div>
          </dl>
        </div>
      </div>

      {displayTimeline.length > 0 && (
        <div id="order-timeline" className="card bg-base-100 shadow border border-base-300 rounded-2xl mt-4">
          <div className="card-body py-4 space-y-3">
            <h2 className="card-title text-base">{t('shop.order.status_timeline_title')}</h2>
            <ul className="space-y-3">
              {displayTimeline.map((row, idx) => (
                <li
                  key={`${row.step}-${idx}`}
                  className="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between border-b border-base-200 pb-3 last:border-0 last:pb-0"
                >
                  <span>{timelineLabel(row)}</span>
                  <span className="text-sm text-base-content/70 tabular-nums">
                    {row.at ? new Date(row.at).toLocaleString() : ''}
                  </span>
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}

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
        <div className="card-body px-4 py-5 sm:px-6">
          {productLines.length === 0 && packLines.length === 0 ? (
            <p className="text-base-content/70 text-sm py-1">{t('shop.order.no_lines')}</p>
          ) : (
            <div className="space-y-8">
              {productLines.length > 0 && (
                <section className="space-y-3" aria-labelledby="storefront-order-lines-products-heading">
                  <h3 id="storefront-order-lines-products-heading" className="text-base font-semibold text-base-content">
                    {t('shop.order.lines_section_products')}
                  </h3>
                  <StorefrontOrderLinesTable lines={productLines} nameHeaderKey="shop.order.th_line_product" t={t} />
                </section>
              )}
              {packLines.length > 0 && (
                <section
                  className={`space-y-3 ${productLines.length > 0 ? 'pt-6 border-t border-base-200' : ''}`}
                  aria-labelledby="storefront-order-lines-packs-heading"
                >
                  <h3 id="storefront-order-lines-packs-heading" className="text-base font-semibold text-base-content">
                    {t('shop.order.lines_section_packs')}
                  </h3>
                  <StorefrontOrderLinesTable lines={packLines} nameHeaderKey="shop.order.th_line_pack" t={t} />
                </section>
              )}
            </div>
          )}
        </div>
        <div className="border-t border-base-300 bg-base-200/40 px-4 py-4 sm:px-6">
          <div className="overflow-x-auto">
            <table className="table table-sm w-full max-w-md ml-auto">
              <tbody className="[&_td]:py-2">
                <tr>
                  <td className="text-end font-medium text-base-content/90">{t('shop.order.lines_subtotal')}</td>
                  <td className="text-end tabular-nums font-medium w-32">{Number(linesSubtotal).toFixed(2)} €</td>
                </tr>
                <tr>
                  <td className="text-end font-medium text-base-content/90">{t('shop.shipping_flat')}</td>
                  <td className="text-end tabular-nums font-medium">{Number(order.shipping_flat_eur ?? 9).toFixed(2)} €</td>
                </tr>
                {showInstallationRow && (
                  <tr>
                    <td className="text-end font-medium text-base-content/90">{t('shop.order.installation_fee')}</td>
                    <td className="text-end tabular-nums font-medium">{Number(order.installation_price).toFixed(2)} €</td>
                  </tr>
                )}
                <tr className="border-t border-base-300">
                  <td className="text-end font-bold pt-3 align-bottom">{t('shop.total')}</td>
                  <td className="text-end text-xl font-bold text-primary tabular-nums pt-3">{Number(grandTotal).toFixed(2)} €</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {paypalReturnInfo && (
        <div role="status" className="alert alert-info mt-4 text-sm">
          {paypalReturnInfo}
        </div>
      )}

      {payWarning && (
        <div role="status" className="alert alert-warning mt-4 text-sm">
          {payWarning}
        </div>
      )}

      {payError && <div role="alert" className="alert alert-error mt-4 text-sm">{payError}</div>}

      {paypalApprovalFallbackUrl && (
        <div role="status" className="alert alert-warning mt-4 text-sm space-y-2">
          <p className="m-0">{t('shop.payment.paypal_popup_blocked')}</p>
          <a
            href={paypalApprovalFallbackUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="link link-primary font-medium"
          >
            {t('shop.payment.paypal_open_link')}
          </a>
        </div>
      )}

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
                {CHECKOUT_PAYMENT_METHOD_ORDER.filter((value) => payAvail[value])
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
                  paypalMode={inlineCheckout.paypal.paypal_mode}
                  onSuccess={async () => {
                    const r = await api.get(`orders/${id}`);
                    if (r.data.success) setOrder(r.data.data);
                    setInlineCheckout(null);
                  }}
                  onError={(msg) => setStripeUiError(msg)}
                  onCancel={() => {
                    const msg = t('shop.payment.paypal_not_completed');
                    setStripeUiError('');
                    setPaypalReturnInfo('');
                    setPayError('');
                    emitAppToast(msg, 'warning');
                    setPayWarning(msg);
                  }}
                />
                {stripeUiError ? <div role="alert" className="alert alert-error text-sm">{stripeUiError}</div> : null}
              </div>
            )}
          </div>
        </form>
      )}

      {!canPay && order.has_payment && (
        <div className="mt-4 flex flex-wrap justify-end gap-2">
          <a href={`/api/v1/orders/${order.id}/delivery-note?locale=${i18n.language ?? 'ca'}`} target="_blank" rel="noopener noreferrer" className="btn btn-outline btn-primary btn-sm">
            {t('shop.delivery_note')}
          </a>
          <a href={`/api/v1/orders/${order.id}/invoice?locale=${i18n.language ?? 'ca'}`} target="_blank" rel="noopener noreferrer" className="btn btn-primary btn-sm">
            {t('shop.invoice')}
          </a>
        </div>
      )}

      {existingRma && (
        <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl mt-4">
          <div className="card-body py-4 px-4 sm:px-5">
            <h2 className="card-title text-base">{t('shop.returns.request_return')}</h2>
            <div className="flex flex-wrap items-center gap-3">
              <span className={`badge ${rmaStatusBadgeClass(existingRma.status)}`}>
                {t(`shop.returns.status_${existingRma.status}`) || existingRma.status}
              </span>
              {existingRma.refund_amount != null && (
                <span className="text-sm text-base-content/70">
                  {t('shop.returns.refund_amount')}: <strong>{Number(existingRma.refund_amount).toFixed(2)} €</strong>
                </span>
              )}
            </div>
          </div>
        </div>
      )}

      {canRequestReturn && (
        <div className="mt-4 flex justify-end">
          <button type="button" className="btn btn-outline btn-sm" onClick={() => setReturnModalOpen(true)}>
            {t('shop.returns.request_return')}
          </button>
        </div>
      )}

      {returnModalOpen && (
        <div className="modal modal-open">
          <div className="modal-box max-w-md">
            <h3 className="font-bold text-lg mb-1">{t('shop.returns.modal_title')}</h3>
            <p className="text-sm text-base-content/70 mb-4">
              {t('shop.returns.modal_body', { orderId: order.id })}
            </p>
            <form onSubmit={handleRequestReturn} className="space-y-4">
              <fieldset className="fieldset">
                <legend className="fieldset-legend">{t('shop.returns.reason_label')}</legend>
                <textarea
                  className="textarea w-full min-h-24"
                  placeholder={t('shop.returns.reason_placeholder')}
                  value={returnReason}
                  onChange={(e) => setReturnReason(e.target.value)}
                  required
                  minLength={10}
                  maxLength={2000}
                />
              </fieldset>
              {returnError && <div role="alert" className="alert alert-error text-sm">{returnError}</div>}
              <div className="modal-action">
                <button
                  type="button"
                  className="btn btn-ghost"
                  onClick={() => { setReturnModalOpen(false); setReturnReason(''); setReturnError(''); }}
                  disabled={returnLoading}
                >
                  {t('common.cancel')}
                </button>
                <button type="submit" className="btn btn-primary" disabled={returnLoading || returnReason.length < 10}>
                  {returnLoading ? <span className="loading loading-spinner loading-sm" /> : t('shop.returns.submit')}
                </button>
              </div>
            </form>
          </div>
          <div className="modal-backdrop" onClick={() => { setReturnModalOpen(false); setReturnReason(''); setReturnError(''); }} />
        </div>
      )}
    </div>
  );
}
