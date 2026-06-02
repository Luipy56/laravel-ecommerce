import { useEffect, useState } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { useToast } from '../contexts/ToastContext';

const OAUTH_ERROR_KEYS = {
  email_not_verified: 'auth.google_oauth_email_not_verified',
  sub_conflict: 'auth.google_oauth_sub_conflict',
  session_expired: 'auth.google_oauth_session_expired',
  schema_outdated: 'auth.google_oauth_schema_outdated',
  provider_error: 'auth.google_oauth_provider_error',
};

export function useGoogleOAuthReturn() {
  const [searchParams] = useSearchParams();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const { refreshUser } = useAuth();
  const { mergeCart } = useCart();
  const { showToast } = useToast();
  const [profileModalOpen, setProfileModalOpen] = useState(false);
  const [pendingNext, setPendingNext] = useState('/');

  useEffect(() => {
    const oauth = searchParams.get('oauth');
    if (!oauth) {
      return;
    }

    const next = searchParams.get('next');
    const safeNext =
      next && next.startsWith('/') && !next.startsWith('//') ? next : '/';
    const profileIncomplete = searchParams.get('profile_incomplete') === '1';
    setPendingNext(safeNext);

    const clearOAuthParams = () => {
      const params = new URLSearchParams(searchParams);
      params.delete('oauth');
      params.delete('code');
      params.delete('next');
      params.delete('profile_incomplete');
      const qs = params.toString();
      navigate({ search: qs ? `?${qs}` : '' }, { replace: true });
    };

    if (oauth === 'error') {
      const code = searchParams.get('code') ?? 'provider_error';
      const key = OAUTH_ERROR_KEYS[code] ?? OAUTH_ERROR_KEYS.provider_error;
      showToast({ message: t(key), type: 'error' });
      clearOAuthParams();
      return;
    }

    if (oauth === 'success') {
      (async () => {
        await refreshUser();
        await mergeCart();
        showToast({ message: t('auth.google_oauth_success'), type: 'success' });
        clearOAuthParams();
        if (profileIncomplete) {
          setProfileModalOpen(true);
        } else {
          navigate(safeNext, { replace: true });
        }
      })();
    }
  }, []); // eslint-disable-line react-hooks/exhaustive-deps

  const closeProfileModal = () => {
    setProfileModalOpen(false);
    navigate(pendingNext, { replace: true });
  };

  return { profileModalOpen, closeProfileModal };
}
