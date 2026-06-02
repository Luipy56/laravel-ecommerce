import { useEffect } from 'react';
import { api } from '../api';
import { refreshCsrfCookie } from '../csrfRecovery';

const MIN_PING_INTERVAL_MS = 30_000;

/**
 * On tab focus / visibility, refresh CSRF cookies and keep the session warm.
 */
export default function SessionKeepAlive() {
  useEffect(() => {
    let lastPing = 0;

    const ping = () => {
      if (document.visibilityState !== 'visible') {
        return;
      }
      const now = Date.now();
      if (now - lastPing < MIN_PING_INTERVAL_MS) {
        return;
      }
      lastPing = now;
      refreshCsrfCookie().catch(() => {});
      api.get('csrf-ping').catch(() => {});
    };

    document.addEventListener('visibilitychange', ping);
    window.addEventListener('focus', ping);
    return () => {
      document.removeEventListener('visibilitychange', ping);
      window.removeEventListener('focus', ping);
    };
  }, []);

  return null;
}
