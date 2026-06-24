/**
 * Storefront demo-phase modal flag from Laravel (isDEMO env → config app.is_demo).
 */
function resolveIsDemoEnabled() {
  if (typeof window !== 'undefined' && window.__LARAVEL_IS_DEMO__ === true) {
    return true;
  }
  return false;
}

export const IS_DEMO_ENABLED = resolveIsDemoEnabled();
