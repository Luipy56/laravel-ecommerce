import '../scss/main_shop.scss'
import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Navigate, useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import { emitAppToast } from '../toastEvents';

const POLL_MS = 4000;
const RESEND_COOLDOWN_SEC = 60;

function safeNextPath(raw) {
  if (!raw || typeof raw !== 'string') return '/';
  if (!raw.startsWith('/') || raw.startsWith('//')) return '/';
  return raw;
}

export default function EmailVerifyPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { user, loading, refreshUser } = useAuth();
  const [searchParams] = useSearchParams();
  const nextPath = safeNextPath(searchParams.get('next'));
  const [sending, setSending] = useState(false);
  const [cooldownLeft, setCooldownLeft] = useState(0);
  const cooldownTimerRef = useRef(null);

  const clearCooldownTimer = () => {
    if (cooldownTimerRef.current) {
      window.clearInterval(cooldownTimerRef.current);
      cooldownTimerRef.current = null;
    }
  };

  useEffect(() => () => clearCooldownTimer(), []);

  const startCooldown = useCallback(() => {
    clearCooldownTimer();
    setCooldownLeft(RESEND_COOLDOWN_SEC);
    cooldownTimerRef.current = window.setInterval(() => {
      setCooldownLeft((s) => {
        if (s <= 1) {
          clearCooldownTimer();
          return 0;
        }
        return s - 1;
      });
    }, 1000);
  }, []);

  useEffect(() => {
    if (!user || user.email_verified) return undefined;
    const id = window.setInterval(() => {
      refreshUser();
    }, POLL_MS);
    return () => window.clearInterval(id);
  }, [user, refreshUser]);

  useEffect(() => {
    if (user?.email_verified) {
      navigate(nextPath, { replace: true });
    }
  }, [user, navigate, nextPath]);

  if (loading) {
    return (
      <div className="flex justify-center py-16">
        <span className="loading loading-spinner loading-lg text-primary" aria-hidden />
      </div>
    );
  }

  if (!user) {
    return <Navigate to={`/login?next=${encodeURIComponent(`/verify-email?next=${encodeURIComponent(nextPath)}`)}`} replace />;
  }

  const onResend = async () => {
    if (cooldownLeft > 0 || sending) return;
    setSending(true);
    try {
      const { data } = await api.post('email/resend');
      emitAppToast(data.message || t('auth.verification_resent'), 'success');
      startCooldown();
      await refreshUser();
    } catch (err) {
      const msg = err.response?.data?.message || t('common.error');
      emitAppToast(msg, 'error');
    } finally {
      setSending(false);
    }
  };

  const resendDisabled = sending || cooldownLeft > 0;
  const resendLabel = sending
    ? t('common.loading')
    : cooldownLeft > 0
      ? t('auth.verify_email_cooldown', { seconds: cooldownLeft })
      : t('auth.verify_email_resend');

  return (
    <div className="mx-auto w-full min-w-0 max-w-lg">
      <PageTitle className="mb-4">{t('auth.verify_email_page_title')}</PageTitle>
      <div className="card bg-base-100 border border-base-200 shadow-xl">
        <div className="card-body gap-6">
          <p className="text-base text-base-content/90 leading-relaxed">
            {t('auth.verify_email_page_intro')}
          </p>
          <p className="text-sm text-base-content/70">{user.login_email}</p>
          <div className="flex justify-center pt-2">
            <button
              type="button"
              className="btn btn-primary border-0 bg-gradient-to-br from-primary to-secondary text-primary-content shadow-lg shadow-primary/20 min-h-12 px-8 disabled:opacity-60"
              disabled={resendDisabled}
              onClick={onResend}
            >
              {resendLabel}
            </button>
          </div>
          <p className="text-xs text-base-content/60">{t('auth.verify_email_auto_notice')}</p>
        </div>
      </div>
    </div>
  );
}
