import React, { useState, useRef } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Embeds a local static game via iframe with loading and error states.
 * The game source must be hosted under /games/ in the public directory.
 */
export default function MiniGameEmbed({ src, title, className = '' }) {
  const { t } = useTranslation();
  const [status, setStatus] = useState('loading');
  const iframeRef = useRef(null);

  function handleLoad() {
    // contentDocument null means the iframe blocked loading (e.g., X-Frame-Options)
    try {
      const doc = iframeRef.current?.contentDocument;
      if (doc === null) {
        setStatus('error');
      } else {
        setStatus('ready');
      }
    } catch {
      setStatus('ready');
    }
  }

  function handleError() {
    setStatus('error');
  }

  return (
    <div className={`relative w-full overflow-hidden rounded-box border border-base-300 bg-base-200 ${className}`}
         style={{ height: 'calc(100dvh - 160px)', minHeight: '480px' }}>

      {status === 'loading' && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3">
          <span className="loading loading-spinner loading-lg text-primary" />
          <span className="text-sm text-base-content/60">{t('games.loading')}</span>
        </div>
      )}

      {status === 'error' && (
        <div className="absolute inset-0 flex flex-col items-center justify-center gap-3 p-6 text-center">
          <span className="text-4xl">🎮</span>
          <p className="font-semibold text-base-content">{t('games.unavailable')}</p>
          <p className="text-sm text-base-content/60">{t('games.unavailable_hint')}</p>
        </div>
      )}

      <iframe
        ref={iframeRef}
        src={src}
        title={title}
        className={`w-full h-full border-0 transition-opacity duration-300 ${status === 'ready' ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
        onLoad={handleLoad}
        onError={handleError}
        sandbox="allow-scripts allow-same-origin allow-forms"
      />
    </div>
  );
}
