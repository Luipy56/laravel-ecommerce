import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { api } from '../api';
import { emitAppToast } from '../toastEvents';

export default function EmailVerificationBanner() {
  const { t } = useTranslation();
  const { user, refreshUser } = useAuth();
  const [sending, setSending] = useState(false);

  if (!user || user.email_verified) {
    return null;
  }

  const onResend = async () => {
    setSending(true);
    try {
      const { data } = await api.post('email/resend');
      emitAppToast(data.message || t('auth.verification_resent'), 'success');
      refreshUser();
    } catch (err) {
      const msg = err.response?.data?.message || t('common.error');
      emitAppToast(msg, 'error');
    } finally {
      setSending(false);
    }
  };

  return (
    <div className="alert alert-warning mb-4 shadow-sm">
      <div className="flex w-full flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <span className="text-sm">{t('auth.verify_banner')}</span>
        <button type="button" className="btn btn-sm btn-neutral shrink-0" disabled={sending} onClick={onResend}>
          {sending ? t('common.loading') : t('auth.resend_verification')}
        </button>
      </div>
    </div>
  );
}
