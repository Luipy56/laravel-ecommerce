/**
 * Opens PayPal approval URL in a new tab without navigating the storefront away.
 * Uses noopener/noreferrer to avoid tab opener abuse.
 *
 * @param {string} url
 * @returns {boolean} true if a window reference was returned (popup likely opened)
 */
export function openPayPalApprovalInNewTab(url) {
  if (typeof window === 'undefined' || !url) return false;
  const w = window.open(url, '_blank', 'noopener,noreferrer');
  if (!w) return false;
  try {
    w.opener = null;
  } catch {
    // Cross-origin or strict mode: ignore
  }
  return true;
}
