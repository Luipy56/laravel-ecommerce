import React, { useEffect, useRef, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';

function getCsrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.getAttribute('content') ?? '';
}

export default function GoogleSignInSection({ next = '/', acceptPrivacy, acceptMarketing, onPrivacyRequired }) {
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
    if (!config.enabled || !config.client_id || !buttonRef.current || !acceptPrivacy) {
      return undefined;
    }

    const render = () => {
      if (!window.google?.accounts?.id?.renderButton || !buttonRef.current) {
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
          if (!acceptPrivacy) {
            onPrivacyRequired?.();
            return;
          }
          formRef.current?.requestSubmit();
        },
      });
    };

    if (window.google?.accounts?.id) {
      render();
      return undefined;
    }

    const existing = document.querySelector('script[data-google-gsi="1"]');
    if (existing) {
      existing.addEventListener('load', render);
      return () => existing.removeEventListener('load', render);
    }

    const script = document.createElement('script');
    script.src = 'https://accounts.google.com/gsi/client';
    script.async = true;
    script.defer = true;
    script.dataset.googleGsi = '1';
    script.onload = render;
    document.head.appendChild(script);

    return () => {
      script.removeEventListener('load', render);
    };
  }, [config.enabled, config.client_id, acceptPrivacy, onPrivacyRequired]);

  if (loading || !config.enabled) {
    return null;
  }

  return (
    <div className="space-y-4">
      <div className="divider text-sm text-base-content/60">{t('auth.or_divider')}</div>
      <form
        ref={formRef}
        method="POST"
        action="/auth/google/redirect"
        className="flex flex-col items-stretch gap-3"
      >
        <input type="hidden" name="_token" value={getCsrfToken()} />
        <input type="hidden" name="accept_privacy" value={acceptPrivacy ? '1' : '0'} />
        <input type="hidden" name="accept_marketing" value={acceptMarketing ? '1' : '0'} />
        <input type="hidden" name="next" value={next} />
        <div ref={buttonRef} className="flex justify-center min-h-[44px]" />
      </form>
      {!acceptPrivacy ? (
        <p className="text-xs text-base-content/60 text-center">{t('auth.google_privacy_required')}</p>
      ) : null}
    </div>
  );
}

export function GoogleOAuthConsentCheckboxes({ acceptPrivacy, setAcceptPrivacy, acceptMarketing, setAcceptMarketing }) {
  const { t } = useTranslation();

  return (
    <div className="flex flex-col gap-3">
      <label className="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          className="checkbox checkbox-primary shrink-0"
          checked={acceptPrivacy}
          onChange={(e) => setAcceptPrivacy(e.target.checked)}
        />
        <span className="text-sm">
          {t('gdpr.accept_privacy_prefix')}{' '}
          <Link to="/privacy-policy" className="link link-primary">
            {t('footer.privacy_policy')}
          </Link>
        </span>
      </label>
      <label className="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          className="checkbox checkbox-primary shrink-0"
          checked={acceptMarketing}
          onChange={(e) => setAcceptMarketing(e.target.checked)}
        />
        <span className="text-sm text-base-content/80">{t('gdpr.accept_marketing')}</span>
      </label>
    </div>
  );
}
