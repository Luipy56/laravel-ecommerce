(function () {
  var STORAGE_KEY = 'cookie_consent_v2';
  var cfg = window.__SERRA_ANALYTICS__ || {};
  var gtmInjected = false;

  function loadConsent() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY) || 'null');
    } catch (e) {
      return null;
    }
  }

  function saveConsent(analytics) {
    var payload = { essential: true, analytics: !!analytics, updated_at: new Date().toISOString() };
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    } catch (e) {}
    return payload;
  }

  function ensureGtm() {
    if (!cfg.enabled || !cfg.gtm_container_id) return;
    var consent = loadConsent();
    if (!consent || !consent.analytics) return;
    if (gtmInjected || document.getElementById('serra-gtm-script')) return;
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
    var s = document.createElement('script');
    s.id = 'serra-gtm-script';
    s.async = true;
    s.src = 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent(cfg.gtm_container_id);
    document.head.appendChild(s);
    gtmInjected = true;
  }

  function applyConsent(analytics) {
    if (typeof window.gtag === 'function') {
      window.gtag('consent', 'update', {
        analytics_storage: analytics ? 'granted' : 'denied',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied'
      });
    }
    window.dataLayer = window.dataLayer || [];
    window.dataLayer.push({
      event: analytics ? 'serra_consent_analytics_granted' : 'serra_consent_analytics_denied'
    });
    if (analytics) ensureGtm();
  }

  function hideBanner() {
    var el = document.getElementById('serra-cookie-banner');
    if (el) el.remove();
  }

  function showBanner() {
    if (document.getElementById('serra-cookie-banner')) return;
    var wrap = document.createElement('div');
    wrap.id = 'serra-cookie-banner';
    wrap.className = 'serra-cookie-banner';
    wrap.setAttribute('role', 'dialog');
    wrap.innerHTML =
      '<div class="serra-cookie-card">' +
      '<h2>Cookies</h2>' +
      '<p>Fem servir cookies essencials. Les analítiques (Google Analytics via Tag Manager) són opcionals.</p>' +
      '<label class="serra-cookie-row"><span>Essencials</span><input type="checkbox" checked disabled></label>' +
      '<label class="serra-cookie-row"><span>Analítiques (opcional)</span><input type="checkbox" id="serra-cookie-analytics"></label>' +
      '<div class="serra-cookie-actions">' +
      '<button type="button" class="serra-btn serra-btn-outline" data-act="reject">Rebutjar opcionals</button>' +
      '<button type="button" class="serra-btn serra-btn-ghost" data-act="selected">Acceptar seleccionades</button>' +
      '<button type="button" class="serra-btn serra-btn-primary" data-act="all">Acceptar-ho tot</button>' +
      '</div></div>';
    document.body.appendChild(wrap);
    wrap.addEventListener('click', function (ev) {
      var btn = ev.target.closest('[data-act]');
      if (!btn) return;
      var act = btn.getAttribute('data-act');
      var analytics = false;
      if (act === 'all') analytics = true;
      if (act === 'selected') {
        var box = document.getElementById('serra-cookie-analytics');
        analytics = !!(box && box.checked);
      }
      saveConsent(analytics);
      applyConsent(analytics);
      hideBanner();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var existing = loadConsent();
    if (existing && existing.analytics) {
      applyConsent(true);
    } else if (!existing) {
      showBanner();
    }
    document.querySelectorAll('[data-serra-cookie-settings]').forEach(function (el) {
      el.addEventListener('click', function (e) {
        e.preventDefault();
        showBanner();
      });
    });
  });
})();
