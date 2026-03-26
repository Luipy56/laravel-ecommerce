import { useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';

/**
 * PayPal Smart Payment Buttons: order is already created server-side; createOrder returns its id.
 */
export default function PayPalInlineButtons({ clientId, paypalOrderId, paymentId, onSuccess, onError }) {
  const { t } = useTranslation();
  const containerRef = useRef(null);
  const onSuccessRef = useRef(onSuccess);
  const onErrorRef = useRef(onError);
  onSuccessRef.current = onSuccess;
  onErrorRef.current = onError;

  useEffect(() => {
    if (!clientId || !paypalOrderId || !paymentId) {
      return undefined;
    }

    let cancelled = false;
    let buttonsInstance = null;
    const container = containerRef.current;
    if (!container) {
      return undefined;
    }

    const scriptSrc = `https://www.paypal.com/sdk/js?client-id=${encodeURIComponent(clientId)}&currency=EUR`;
    const scriptDomId = 'paypal-sdk-inline-storefront';

    const ensureScript = () =>
      new Promise((resolve, reject) => {
        let script = document.getElementById(scriptDomId);
        if (script && script.src === scriptSrc && window.paypal) {
          resolve();
          return;
        }
        if (script && script.src !== scriptSrc) {
          script.remove();
          script = null;
        }
        if (!script) {
          script = document.createElement('script');
          script.id = scriptDomId;
          script.src = scriptSrc;
          script.async = true;
          script.onload = () => resolve();
          script.onerror = () => reject(new Error('PayPal script failed'));
          document.body.appendChild(script);
        } else if (window.paypal) {
          resolve();
        } else {
          script.onload = () => resolve();
          script.onerror = () => reject(new Error('PayPal script failed'));
        }
      });

    ensureScript()
      .then(() => {
        if (cancelled || !containerRef.current || !window.paypal) {
          return;
        }
        buttonsInstance = window.paypal.Buttons({
          createOrder: () => Promise.resolve(paypalOrderId),
          onApprove: async (data) => {
            try {
              const res = await api.post('payments/paypal/capture', {
                paypal_order_id: data.orderID,
                payment_id: paymentId,
              });
              if (res.data?.success) {
                onSuccessRef.current();
              } else {
                onErrorRef.current(res.data?.message || t('common.error'));
              }
            } catch (err) {
              onErrorRef.current(err.response?.data?.message || t('common.error'));
            }
          },
          onError: (err) => {
            onErrorRef.current(err?.message || t('common.error'));
          },
        });
        return buttonsInstance.render(containerRef.current);
      })
      .catch((e) => {
        onErrorRef.current(e?.message || t('common.error'));
      });

    return () => {
      cancelled = true;
      if (buttonsInstance && typeof buttonsInstance.close === 'function') {
        try {
          buttonsInstance.close();
        } catch {
          /* ignore */
        }
      }
      if (containerRef.current) {
        containerRef.current.innerHTML = '';
      }
    };
  }, [clientId, paypalOrderId, paymentId, t]);

  return <div ref={containerRef} className="min-h-[48px]" />;
}
