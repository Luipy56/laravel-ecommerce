import React from 'react';
import { Link, Outlet } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Navbar from './Navbar';
import CartWidget from './CartWidget';
import Footer from './Footer';
import ScrollToTop from './ScrollToTop';
import CookieConsentBanner from './CookieConsentBanner';

const STOREFRONT_DRAWER_ID = 'drawer-nav';

function closeStorefrontDrawer() {
  const el = document.getElementById(STOREFRONT_DRAWER_ID);
  if (el instanceof HTMLInputElement && el.checked) {
    el.click();
  }
}

export default function Layout() {
  const { t } = useTranslation();

  return (
    <div className="drawer">
      <input
        id={STOREFRONT_DRAWER_ID}
        type="checkbox"
        className="drawer-toggle"
        aria-hidden="true"
      />
      <div className="drawer-content flex min-h-screen flex-col bg-base-200">
        <Navbar />
        <main className="container mx-auto min-w-0 max-w-full flex-1 px-4 py-6">
          <Outlet />
        </main>
        <Footer />
        <CartWidget />
        <ScrollToTop />
        <CookieConsentBanner />
      </div>
      <div className="drawer-side z-[60] lg:hidden">
        <label htmlFor={STOREFRONT_DRAWER_ID} aria-label={t('common.close')} className="drawer-overlay" />
        <aside className="flex min-h-full w-72 max-w-[min(100vw,20rem)] flex-col border-r border-base-200 bg-base-100">
          <div className="border-b border-base-200 p-4">
            <span className="font-semibold text-base-content">{t('common.menu')}</span>
          </div>
          <ul className="menu p-4">
            <li>
              <Link to="/" onClick={closeStorefrontDrawer}>
                {t('shop.home')}
              </Link>
            </li>
            <li>
              <Link to="/products" onClick={closeStorefrontDrawer}>
                {t('shop.products')}
              </Link>
            </li>
            <li>
              <Link to="/custom-solution" onClick={closeStorefrontDrawer}>
                {t('shop.custom_solution')}
              </Link>
            </li>
            <li>
              <Link to="/cart" onClick={closeStorefrontDrawer}>
                {t('shop.cart')}
              </Link>
            </li>
          </ul>
        </aside>
      </div>
    </div>
  );
}
