import React, { useMemo, useState } from 'react';
import { loadStripe } from '@stripe/stripe-js';
import { Elements, PaymentElement, useElements, useStripe } from '@stripe/react-stripe-js';
import { useTranslation } from 'react-i18next';

const stripePromises = new Map();

function getStripePromise(publishableKey) {
  if (!publishableKey) return null;
  if (!stripePromises.has(publishableKey)) {
    stripePromises.set(publishableKey, loadStripe(publishableKey));
  }
  return stripePromises.get(publishableKey);
}

function InnerForm({ orderId, onSuccess, onError }) {
  const { t } = useTranslation();
  const stripe = useStripe();
  const elements = useElements();
  const [busy, setBusy] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    if (!stripe || !elements) return;
    setBusy(true);
    const returnUrl = `${window.location.origin}/orders/${orderId}`;
    const { error } = await stripe.confirmPayment({
      elements,
      confirmParams: { return_url: returnUrl },
      redirect: 'if_required',
    });
    setBusy(false);
    if (error) {
      onError(error.message ?? t('common.error'));
      return;
    }
    onSuccess();
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <PaymentElement />
      <button type="submit" className="btn btn-primary" disabled={!stripe || busy}>
        {busy ? t('common.loading') : t('checkout.payment.submit_card')}
      </button>
    </form>
  );
}

/**
 * Embedded Stripe Payment Element (card / wallets). Caller must ensure publishable key matches the account that created the PaymentIntent.
 */
export default function StripeInlinePayment({ publishableKey, clientSecret, orderId, onSuccess, onError }) {
  const stripePromise = useMemo(() => getStripePromise(publishableKey), [publishableKey]);
  const options = useMemo(() => ({ clientSecret }), [clientSecret]);

  if (!stripePromise || !clientSecret) {
    return null;
  }

  return (
    <Elements stripe={stripePromise} options={options}>
      <InnerForm orderId={orderId} onSuccess={onSuccess} onError={onError} />
    </Elements>
  );
}
