/**
 * Admin login page flags from Laravel (ADMINAUTOLOGIN env → config app.admin_auto_login).
 */
function resolveAdminAutoLoginEnabled() {
  if (typeof window !== 'undefined' && typeof window.__LARAVEL_ADMIN_AUTO_LOGIN__ === 'boolean') {
    return window.__LARAVEL_ADMIN_AUTO_LOGIN__;
  }
  return false;
}

export const ADMIN_AUTO_LOGIN_ENABLED = resolveAdminAutoLoginEnabled();
