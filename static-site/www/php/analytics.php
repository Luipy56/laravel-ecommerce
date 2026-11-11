<?php
/**
 * Marketing-site GTM bootstrap (Consent Mode v2 defaults).
 * GTM loads only after optional analytics consent (localStorage cookie_consent_v2).
 * Set STATIC_GTM_CONTAINER_ID / STATIC_ANALYTICS_ENABLED in the PHP-FPM env (.env.static).
 */
$serraAnalyticsEnabled = filter_var(getenv('STATIC_ANALYTICS_ENABLED') ?: '0', FILTER_VALIDATE_BOOLEAN);
$serraGtmId = trim((string) (getenv('STATIC_GTM_CONTAINER_ID') ?: ''));
$serraGa4Id = trim((string) (getenv('STATIC_GA4_MEASUREMENT_ID') ?: ''));
?>
<script>
window.__SERRA_ANALYTICS__ = {
  enabled: <?php echo $serraAnalyticsEnabled && $serraGtmId !== '' ? 'true' : 'false'; ?>,
  gtm_container_id: <?php echo json_encode($serraGtmId, JSON_UNESCAPED_SLASHES); ?>,
  ga4_measurement_id: <?php echo json_encode($serraGa4Id, JSON_UNESCAPED_SLASHES); ?>
};
window.dataLayer = window.dataLayer || [];
window.gtag = window.gtag || function () { window.dataLayer.push(arguments); };
window.gtag('consent', 'default', {
  analytics_storage: 'denied',
  ad_storage: 'denied',
  ad_user_data: 'denied',
  ad_personalization: 'denied',
  wait_for_update: 500
});
(function () {
  try {
    var c = JSON.parse(localStorage.getItem('cookie_consent_v2') || 'null');
    if (c && c.analytics) {
      window.gtag('consent', 'update', {
        analytics_storage: 'granted',
        ad_storage: 'denied',
        ad_user_data: 'denied',
        ad_personalization: 'denied'
      });
    }
  } catch (e) {}
})();
</script>
<script src="/web/js/serra-analytics.js" defer></script>
<link rel="stylesheet" href="/web/css/serra-consent.css">
