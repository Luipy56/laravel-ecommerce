export {
  CONSENT_STORAGE_KEY,
  loadConsent,
  saveConsent,
  hasAnalyticsConsent,
} from './consentStorage';
export { applyAnalyticsConsent, ensureGtmLoaded, getAnalyticsConfig, initConsentMode } from './gtmLoader';
export {
  trackPageView,
  trackViewItemList,
  trackSelectItem,
  trackViewItem,
  trackAddToCart,
  trackRemoveFromCart,
  trackViewCart,
  trackBeginCheckout,
  trackAddShippingInfo,
  trackAddPaymentInfo,
  trackPurchaseOnce,
  trackGenerateLead,
  trackViewSearchResults,
  toGa4Item,
  itemsFromCartLines,
} from './ecommerce';
