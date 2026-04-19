import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';
import PayPalInlineButtons from '../components/payments/PayPalInlineButtons';
import { emitAppToast } from '../toastEvents';
import { checkoutFormSchema, parseWithZod } from '../validation';
import { openPayPalApprovalInNewTab } from '../payments/openPayPalApprovalInNewTab';

const INITIAL_FORM = {
  payment_method: 'card',
  shipping_street: '',
  shipping_city: '',
  shipping_province: '',
  shipping_postal_code: '',
  shipping_note: '',
  installation_street: '',
  installation_city: '',
  installation_postal_code: '',
  installation_note: '',
};

export default function CheckoutPage() {
  const { t } = useTranslation();
  const { user, loading: authLoading } = useAuth();
  const { cart, fetchCart } = useCart();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [form, setForm] = useState(INITIAL_FORM);
  const [activeCheckout, setActiveCheckout] = useState(null);
  const [stripeUiError, setStripeUiError] = useState('');
  const [payMethods, setPayMethods] = useState(null);
  const [payConfigLoadError, setPayConfigLoadError] = useState(false);
  const [payConfigMeta, setPayConfigMeta] = useState({
    simulated: false,
    local_checkout_needs_debug: false,
    paypal_missing_credentials: false,
    stripe_missing_credentials: false,
    paypal_mode: undefined,
  });
  const [checkoutFormError, setCheckoutFormError] = useState('');
  const [paypalApprovalFallbackUrl, setPaypalApprovalFallbackUrl] = useState(null);
  const [paypalCancelWarning, setPaypalCancelWarning] = useState('');
  const wantsInstallation = !!cart.installation_requested;
  const installationQuoteRequired = !!(wantsInstallation && cart.installation_quote_required);
  /** Must choose a PSP method (not used when awaiting a manual installation quote only). */
  const paymentRequired = !wantsInstallation || !installationQuoteRequired;
  const checkoutSchemaOpts = { wantsInstallation, installationQuoteRequired };

  useEffect(() => {
    api
      .get('payments/config')
      .then((r) => {
        const d = r.data.data;
        setPayConfigLoadError(false);
        if (r.data.success && d?.methods) {
          setPayMethods(d.methods);
          setPayConfigMeta({
            simulated: !!d.simulated,
            local_checkout_needs_debug: !!d.local_checkout_needs_debug,
            paypal_missing_credentials: !!d.paypal_missing_credentials,
            stripe_missing_credentials: !!d.stripe_missing_credentials,
            paypal_mode: d.paypal_mode === 'live' ? 'live' : d.paypal_mode === 'sandbox' ? 'sandbox' : undefined,
          });
        } else {
          setPayMethods({ card: false, paypal: false });
          setPayConfigMeta({
            simulated: false,
            local_checkout_needs_debug: false,
            paypal_missing_credentials: false,
            stripe_missing_credentials: false,
            paypal_mode: undefined,
          });
        }
      })
      .catch(() => {
        setPayConfigLoadError(true);
        setPayMethods({ card: false, paypal: false });
        setPayConfigMeta({
          simulated: false,
          local_checkout_needs_debug: false,
          paypal_missing_credentials: false,
          stripe_missing_credentials: false,
          paypal_mode: undefined,
        });
      });
  }, []);

  useEffect(() => {
    if (payMethods === null || !paymentRequired) return;
    setForm((f) => {
      if (payMethods[f.payment_method]) return f;
      const first = ['card', 'paypal'].find((k) => payMethods[k]);
      return first ? { ...f, payment_method: first } : f;
    });
  }, [payMethods, paymentRequired]);

  useEffect(() => {
    if (!user) return;
    api.get('profile').then(({ data }) => {
      if (data.success && data.data?.address) {
        const addr = data.data.address;
        setForm((f) => ({
          ...f,
          shipping_street: addr.street ?? '',
          shipping_city: addr.city ?? '',
          shipping_province: addr.province ?? '',
          shipping_postal_code: addr.postal_code ?? '',
          installation_street: addr.street ?? '',
          installation_city: addr.city ?? '',
          installation_postal_code: addr.postal_code ?? '',
        }));
      }
    });
  }, [user?.id]);

  const handleChange = (e) => {
    setCheckoutFormError('');
    setForm((f) => ({ ...f, [e.target.name]: e.target.value }));
  };

  const doCheckout = useCallback(async () => {
    setConfirmOpen(false);
    setLoading(true);
    setActiveCheckout(null);
    setPaypalApprovalFallbackUrl(null);
    setPaypalCancelWarning('');
    setStripeUiError('');
    try {
      const toValidate = installationQuoteRequired ? { ...form, payment_method: null } : form;
      const parsed = parseWithZod(checkoutFormSchema(checkoutSchemaOpts), toValidate, t);
      if (!parsed.ok) {
        emitAppToast(parsed.firstError, 'error');
        setCheckoutFormError(parsed.firstError);
        return;
      }
      const payload = installationQuoteRequired ? { ...parsed.data, payment_method: null } : parsed.data;
      const { data } = await api.post('orders/checkout', payload);
      if (!data.success) return;

      await fetchCart();
      const d = data.data;

      if (d.awaiting_installation_quote) {
        navigate('/orders/' + d.id);
        return;
      }

      if (d.payment_error) {
        navigate('/orders/' + d.id, { state: { paymentErrorFromCheckout: d.payment_error } });
        return;
      }

      if (d.has_payment) {
        navigate('/orders/' + d.id);
        return;
      }

      const c = d.payment_checkout;
      if (c?.gateway === 'stripe' && c.checkout_url) {
        window.location.href = c.checkout_url;
        return;
      }
      if (c?.gateway === 'paypal' && c.approval_url) {
        const opened = openPayPalApprovalInNewTab(c.approval_url);
        if (!opened) setPaypalApprovalFallbackUrl(c.approval_url);
        navigate('/orders/' + d.id, { state: { paypalHostedWindow: true } });
        return;
      }
      if (c?.gateway === 'paypal' && c.client_id && c.paypal_order_id && c.payment_id) {
        setActiveCheckout({
          orderId: d.id,
          paypal: {
            client_id: c.client_id,
            paypal_order_id: c.paypal_order_id,
            payment_id: c.payment_id,
            paypal_mode: c.paypal_mode === 'live' ? 'live' : 'sandbox',
          },
        });
        return;
      }

      navigate('/orders/' + d.id);
    } catch (err) {
      const d = err.response?.data;
      const msg =
        d?.code === 'payment_method_not_configured'
          ? t('shop.payment.method_unavailable')
          : d?.message || t('common.error');
      emitAppToast(msg, 'error');
    } finally {
      setLoading(false);
    }
  }, [form, navigate, fetchCart, wantsInstallation, installationQuoteRequired, t]);

  const handleSubmit = (e) => {
    e.preventDefault();
    setCheckoutFormError('');
    const toValidate = installationQuoteRequired ? { ...form, payment_method: null } : form;
    const parsed = parseWithZod(checkoutFormSchema(checkoutSchemaOpts), toValidate, t);
    if (!parsed.ok) {
      setCheckoutFormError(parsed.firstError);
      return;
    }
    setConfirmOpen(true);
  };

  if (authLoading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }
  if (!user) {
    return (
      <div className="text-center py-8">
        <p className="mb-4">{t('shop.checkout.requires_login')}</p>
        <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>
      </div>
    );
  }

  const payMethodsReady = payMethods !== null;
  const anyPaymentMethod = payMethodsReady && Object.values(payMethods).some(Boolean);
  const shipFlat = Number(cart.shipping_flat_eur ?? 9);
  const installationFeeAmount =
    wantsInstallation && cart.installation_fee_eur != null ? Number(cart.installation_fee_eur) : 0;
  const grandTotalCheckout = Number(cart.total) + shipFlat + installationFeeAmount;

  if (!cart.lines?.length) {
    return (
      <div className="text-center py-8">
        <p className="mb-4">{t('shop.cart.empty')}</p>
        <Link to="/cart" className="btn btn-primary">{t('shop.cart')}</Link>
      </div>
    );
  }

  return (
    <div className="mx-auto w-full min-w-0 max-w-2xl">
      <PageTitle>{t('shop.checkout')}</PageTitle>
      <ConfirmModal
        open={confirmOpen}
        onClose={() => setConfirmOpen(false)}
        onConfirm={doCheckout}
        title={t('shop.checkout')}
        message={t('checkout.confirm_message')}
        loading={loading}
      />
      <form onSubmit={handleSubmit} className="card bg-base-100 shadow min-w-0">
        <div className="card-body space-y-5">
          {checkoutFormError ? (
            <div role="alert" className="alert alert-error text-sm">
              {checkoutFormError}
            </div>
          ) : null}
          {paypalApprovalFallbackUrl ? (
            <div role="status" className="alert alert-warning text-sm">
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
          ) : null}
          <h2 className="font-semibold text-base-content">{t('checkout.shipping_address')}</h2>
          <label className="form-field w-full">
            <span className="form-label">{t('checkout.street')}</span>
            <input name="shipping_street" className="input input-bordered w-full" value={form.shipping_street} onChange={handleChange} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('profile.city')}</span>
            <input name="shipping_city" className="input input-bordered w-full" value={form.shipping_city} onChange={handleChange} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('profile.postal_code')} *</span>
            <input name="shipping_postal_code" className="input input-bordered w-full" value={form.shipping_postal_code} onChange={handleChange} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('checkout.note')}</span>
            <textarea name="shipping_note" className="textarea textarea-bordered w-full" rows={2} value={form.shipping_note} onChange={handleChange} />
          </label>

          {wantsInstallation && (
            <>
              <h2 className="font-semibold text-base-content mt-6">{t('checkout.installation_address')}</h2>
              <label className="form-field w-full">
                <span className="form-label">{t('checkout.street')}</span>
                <input name="installation_street" className="input input-bordered w-full" value={form.installation_street} onChange={handleChange} required />
              </label>
              <label className="form-field w-full">
                <span className="form-label">{t('profile.city')}</span>
                <input name="installation_city" className="input input-bordered w-full" value={form.installation_city} onChange={handleChange} required />
              </label>
              <label className="form-field w-full">
                <span className="form-label">{t('profile.postal_code')} *</span>
                <input name="installation_postal_code" className="input input-bordered w-full" value={form.installation_postal_code} onChange={handleChange} required />
              </label>
              <label className="form-field w-full">
                <span className="form-label">{t('checkout.note')}</span>
                <textarea name="installation_note" className="textarea textarea-bordered w-full" rows={2} value={form.installation_note} onChange={handleChange} />
              </label>
              {installationQuoteRequired ? (
                <div className="space-y-2 border-l-2 border-base-300 pl-3">
                  <p className="m-0 text-sm text-base-content/70">{t('checkout.payment_after_quote')}</p>
                  <p className="m-0 text-sm text-base-content/70">{t('checkout.installation_custom_quote_notice')}</p>
                </div>
              ) : (
                <p className="m-0 text-sm text-base-content/70 border-l-2 border-base-300 pl-3">
                  {t('checkout.installation_tier_notice')}
                </p>
              )}
            </>
          )}

          {paymentRequired && (
            <>
              <h2 className="font-semibold text-base-content mt-6">{t('checkout.payment')}</h2>
              {payMethodsReady && payConfigMeta.simulated && anyPaymentMethod && (
                <p className="m-0 text-xs text-base-content/60">{t('checkout.payment.simulated_mode_notice')}</p>
              )}
              {payMethodsReady && payConfigLoadError && (
                <div role="alert" className="alert alert-warning text-sm">
                  <p className="m-0">{t('checkout.payment.config_load_error')}</p>
                </div>
              )}
              {payMethodsReady && !payConfigLoadError && !anyPaymentMethod && (
                <div role="alert" className="alert alert-warning text-sm space-y-2">
                  <p className="m-0">{t('checkout.payment.no_methods')}</p>
                  {payConfigMeta.local_checkout_needs_debug ? (
                    <p className="m-0 text-base-content/90">{t('checkout.payment.local_debug_hint')}</p>
                  ) : null}
                </div>
              )}
              {payMethodsReady && !payConfigLoadError && payConfigMeta.paypal_missing_credentials && (
                <p className="m-0 text-xs text-base-content/60">{t('checkout.payment.paypal_missing_credentials_hint')}</p>
              )}
              {payMethodsReady && !payConfigLoadError && payConfigMeta.stripe_missing_credentials && (
                <p className="m-0 text-xs text-base-content/60">{t('checkout.payment.stripe_missing_credentials_hint')}</p>
              )}
              <label className="form-field w-full">
                <span className="form-label">{t('checkout.payment_method')}</span>
                <select
                  name="payment_method"
                  className="select select-bordered w-full"
                  value={payMethodsReady ? form.payment_method : ''}
                  onChange={handleChange}
                  disabled={!payMethodsReady || (payMethodsReady && !anyPaymentMethod) || payConfigLoadError}
                >
                  {!payMethodsReady ? (
                    <option value="">{t('common.loading')}</option>
                  ) : (
                    ['card', 'paypal']
                      .filter((value) => payMethods[value])
                      .map((value) => (
                        <option key={value} value={value}>
                          {t(`checkout.payment.${value}`)}
                        </option>
                      ))
                  )}
                </select>
              </label>
            </>
          )}

          <div className="flex flex-wrap items-end justify-between gap-4 mt-4">
            <div className="text-sm space-y-1">
              <p className="m-0 text-base-content/80">
                <span className="font-medium text-base-content">{t('shop.subtotal')}:</span>{' '}
                <span className="tabular-nums">{Number(cart.total).toFixed(2)} €</span>
              </p>
              <p className="m-0 text-base-content/80">
                <span className="font-medium text-base-content">{t('shop.shipping_flat')}:</span>{' '}
                <span className="tabular-nums">{shipFlat.toFixed(2)} €</span>
              </p>
              {installationFeeAmount > 0 ? (
                <p className="m-0 text-base-content/80">
                  <span className="font-medium text-base-content">{t('shop.order.installation_fee')}:</span>{' '}
                  <span className="tabular-nums">{installationFeeAmount.toFixed(2)} €</span>
                </p>
              ) : null}
              <p className="m-0 text-lg font-bold text-primary">
                <span className="font-semibold text-base-content">
                  {installationFeeAmount > 0 ? t('checkout.total_due_estimate') : t('shop.total_with_shipping')}
                  :
                </span>{' '}
                <span className="tabular-nums">
                  {(installationFeeAmount > 0 ? grandTotalCheckout : Number(cart.total_with_shipping ?? cart.total + shipFlat)).toFixed(2)} €
                </span>
              </p>
            </div>
            <button
              type="submit"
              className="btn btn-primary shrink-0"
              disabled={
                loading ||
                !!activeCheckout?.paypal ||
                (paymentRequired && (!payMethodsReady || !anyPaymentMethod || payConfigLoadError))
              }
            >
              {loading ? t('common.loading') : t('shop.checkout')}
            </button>
          </div>
        </div>
      </form>

      {activeCheckout?.paypal && (
        <div className="card bg-base-100 shadow border border-base-300 mt-6">
          <div className="card-body space-y-3">
            <h2 className="card-title text-base">{t('checkout.payment.complete_paypal')}</h2>
            <p className="text-sm text-base-content/70">{t('checkout.payment.paypal_help')}</p>
            <PayPalInlineButtons
              clientId={activeCheckout.paypal.client_id}
              paypalOrderId={activeCheckout.paypal.paypal_order_id}
              paymentId={activeCheckout.paypal.payment_id}
              paypalMode={activeCheckout.paypal.paypal_mode ?? payConfigMeta.paypal_mode}
              onSuccess={() => navigate(`/orders/${activeCheckout.orderId}`)}
              onError={(msg) => setStripeUiError(msg)}
              onCancel={() => {
                const msg = t('shop.payment.paypal_not_completed');
                setPaypalCancelWarning(msg);
                setStripeUiError('');
                emitAppToast(msg, 'warning');
              }}
            />
            {paypalCancelWarning ? (
              <div role="status" className="alert alert-warning text-sm mt-2">
                {paypalCancelWarning}
              </div>
            ) : null}
            {stripeUiError ? <div role="alert" className="alert alert-error text-sm mt-2">{stripeUiError}</div> : null}
            <div className="flex flex-wrap gap-2 pt-2">
              <Link to={`/orders/${activeCheckout.orderId}`} className="btn btn-ghost btn-sm">
                {t('checkout.payment.view_order')}
              </Link>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
