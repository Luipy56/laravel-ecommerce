import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { getMetaCsrfToken, refreshCsrfCookie } from '../csrfRecovery';

function GoogleLogoIcon({ className = 'h-5 w-5 shrink-0' }) {
  return (
    <svg className={className} viewBox="0 0 48 48" aria-hidden="true">
      <path
        fill="#EA4335"
        d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"
      />
      <path
        fill="#4285F4"
        d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.56 2.95-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"
      />
      <path
        fill="#FBBC05"
        d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"
      />
      <path
        fill="#34A853"
        d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"
      />
    </svg>
  );
}

/**
 * Storefront Google sign-in via full-page Laravel Socialite redirect (no GIS popup / FedCM).
 * GIS renderButton opens a second window and races OAuth state with the server redirect.
 */
export default function GoogleSignInSection({
  next = '/',
  acceptMarketing = false,
  showTopDivider = false,
  showBottomDivider = true,
}) {
  const { t } = useTranslation();
  const [config, setConfig] = useState({ enabled: false, client_id: null });
  const [loading, setLoading] = useState(true);
  const [csrfToken, setCsrfToken] = useState(getMetaCsrfToken);
  const formRef = useRef(null);

  const syncCsrfToken = useCallback(async () => {
    try {
      const token = await refreshCsrfCookie();
      setCsrfToken(token);
    } catch {
      setCsrfToken(getMetaCsrfToken());
    }
  }, []);

  useEffect(() => {
    let cancelled = false;
    api.get('auth/google-config')
      .then(({ data }) => {
        if (!cancelled && data?.success) {
          setConfig(data.data ?? { enabled: false, client_id: null });
        }
      })
      .catch(() => {
        if (!cancelled) setConfig({ enabled: false, client_id: null });
      })
      .finally(() => {
        if (!cancelled) setLoading(false);
      });
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!config.enabled) {
      return undefined;
    }
    syncCsrfToken();
    const onVisible = () => {
      if (document.visibilityState === 'visible') {
        syncCsrfToken();
      }
    };
    document.addEventListener('visibilitychange', onVisible);
    window.addEventListener('focus', onVisible);
    return () => {
      document.removeEventListener('visibilitychange', onVisible);
      window.removeEventListener('focus', onVisible);
    };
  }, [config.enabled, syncCsrfToken]);

  const handleSubmit = async (event) => {
    event.preventDefault();
    try {
      const token = await refreshCsrfCookie();
      setCsrfToken(token);
    } catch {
      // submit with last known token
    }
    formRef.current?.submit();
  };

  if (loading || !config.enabled) {
    return null;
  }

  return (
    <div className="space-y-3">
      {showTopDivider ? (
        <div className="divider text-sm text-base-content/60">{t('auth.or_divider')}</div>
      ) : null}
      <form
        ref={formRef}
        method="POST"
        action="/auth/google/redirect"
        className="flex flex-col items-stretch gap-3"
        onSubmit={handleSubmit}
      >
        <input type="hidden" name="_token" value={csrfToken} />
        <input type="hidden" name="accept_privacy" value="1" />
        <input type="hidden" name="accept_marketing" value={acceptMarketing ? '1' : '0'} />
        <input type="hidden" name="next" value={next} />
        <button
          type="submit"
          className="btn btn-lg w-full min-h-12 bg-base-100 border border-[#747775] text-base-content font-medium normal-case shadow-none hover:bg-base-200 hover:border-[#747775] gap-3"
        >
          <GoogleLogoIcon />
          <span>{t('auth.continue_with_google')}</span>
        </button>
      </form>
      <p className="text-xs text-base-content/60 text-center">
        {t('auth.google_privacy_notice_prefix')}{' '}
        <Link to="/privacy-policy" className="link link-primary">
          {t('footer.privacy_policy')}
        </Link>
        {t('auth.google_privacy_notice_suffix')}
      </p>
      {showBottomDivider ? (
        <div className="divider text-sm text-base-content/60">{t('auth.or_divider')}</div>
      ) : null}
    </div>
  );
}
