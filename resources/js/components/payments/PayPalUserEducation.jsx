import { useTranslation } from 'react-i18next';

/** Explains why PayPal login is not on the merchant form and what dashboard activity means. */
export default function PayPalUserEducation({ variant = 'full', context = 'checkout' }) {
  const { t } = useTranslation();
  if (variant === 'compact') {
    return (
      <div role="status" className="alert alert-info text-sm space-y-2">
        <p className="m-0">{t('checkout.payment.paypal_user_edu_compact')}</p>
      </div>
    );
  }
  return (
    <div role="status" className="alert alert-info text-sm space-y-2">
      <p className="m-0 font-medium text-base-content">{t('checkout.payment.paypal_user_edu_title')}</p>
      <p className="m-0">
        {t(
          context === 'order'
            ? 'checkout.payment.paypal_user_edu_after_pay_cta'
            : 'checkout.payment.paypal_after_order_hint',
        )}
      </p>
      <p className="m-0">{t('checkout.payment.paypal_user_edu_no_form')}</p>
      <p className="m-0">{t('checkout.payment.paypal_user_edu_session')}</p>
      <p className="m-0 text-base-content/85">{t('checkout.payment.paypal_user_edu_activity')}</p>
    </div>
  );
}
