import React, { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') ?? '';
}

let gsiScriptPromise = null;
let gsiInitializedClientId = null;

function loadGsiScript() {
  if (typeof window !== 'undefined' && window.google?.accounts?.id) {
    return Promise.resolve();
  }
  if (gsiScriptPromise) {
    return gsiScriptPromise;
  }

  gsiScriptPromise = new Promise((resolve, reject) => {
    const existing = document.querySelector('script[data-google-gsi="1"]');
    if (existing) {
      if (window.google?.accounts?.id) {
        resolve();
        return;
      }
      existing.addEventListener('load', () => resolve(), { once: true });
      existing.addEventListener('error', () => reject(new Error('GSI script failed')), { once: true });
      return;
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.dataset.googleGsi = '1';
    script.onload = () => resolve();
    script.onerror = () => reject(new Error('GSI script failed'));
    document.head.appendChild(script);
  });

  return gsiScriptPromise;
}

function ensureGsiInitialized(clientId) {
  if (!window.google?.accounts?.id) {
    return false;
  }
  if (gsiInitializedClientId !== clientId) {
    window.google.accounts.id.initialize({ client_id: clientId });
    gsiInitializedClientId = clientId;
  }
  return true;
}

export default function GoogleSignInSection({
  next = '/',
  acceptMarketing = false,
  showTopDivider = false,
  showBottomDivider = true,
}) {
  const { t } = useTranslation();
  const [config, setConfig] = useState({ enabled: false, client_id: null });
  const [loading, setLoading] = useState(true);
  const buttonRef = useRef(null);
  const formRef = useRef(null);

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
    if (!config.enabled || !config.client_id || !buttonRef.current) {
      return undefined;
    }

    let cancelled = false;

    const renderButton = () => {
      if (cancelled || !buttonRef.current || !config.client_id) {
        return;
      }
      if (!ensureGsiInitialized(config.client_id)) {
        return;
      }
      buttonRef.current.innerHTML = '';
      window.google.accounts.id.renderButton(buttonRef.current, {
        type: 'standard',
        theme: 'outline',
        size: 'large',
        text: 'continue_with',
        width: Math.min(400, buttonRef.current.offsetWidth || 320),
        click_listener: () => {
          formRef.current?.requestSubmit();
        },
      });
    };

    loadGsiScript()
      .then(renderButton)
      .catch(() => {
        /* Button stays empty; config still enabled — user can retry reload */
      });

    return () => {
      cancelled = true;
    };
  }, [config.enabled, config.client_id]);

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
      >
        <input type="hidden" name="_token" value={getCsrfToken()} />
        <input type="hidden" name="accept_privacy" value="1" />
        <input type="hidden" name="accept_marketing" value={acceptMarketing ? '1' : '0'} />
        <input type="hidden" name="next" value={next} />
        <div ref={buttonRef} className="flex justify-center min-h-[44px]" />
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
