import React, { useState, useEffect, useRef } from 'react';
import { Link, NavLink, Outlet, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { STOREFRONT_LANGUAGE_OPTIONS } from '../lib/storefrontLanguageOptions';
import Navbar from './Navbar';
import CartWidget from './CartWidget';
import Footer from './Footer';
import ScrollToTop from './ScrollToTop';
import CookieConsentBanner from './CookieConsentBanner';
import {
  IconCart,
  IconChevronDown,
  IconClipboardList,
  IconGrid,
  IconHeart,
  IconHelpCircle,
  IconHome,
  IconLogIn,
  IconPackage,
  IconSparkles,
  IconUser,
  IconX,
} from './icons';

const STOREFRONT_DRAWER_ID = 'drawer-nav';

function closeStorefrontDrawer() {
  const el = document.getElementById(STOREFRONT_DRAWER_ID);
  if (el instanceof HTMLInputElement && el.checked) {
    el.click();
  }
}

function drawerNavClass(isActive) {
  return [
    'flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium transition-colors duration-200',
    isActive
      ? 'bg-primary text-primary-content shadow-md'
      : 'text-base-content hover:bg-base-200 active:bg-base-300',
  ].join(' ');
}

function drawerIconClass(isActive) {
  return ['h-5 w-5 shrink-0', isActive ? 'text-primary-content' : 'text-primary'].join(' ');
}

function localeCode(lng) {
  if (lng === 'ca') return 'CA';
  if (lng === 'es') return 'ES';
  return 'EN';
}

export default function Layout() {
  const { t, i18n } = useTranslation();
  const { pathname } = useLocation();
  const { user, loading: authLoading } = useAuth();
  const [locale, setLocale] = useState(i18n.language);
  const [localeMenuOpen, setLocaleMenuOpen] = useState(false);
  const localeMenuRef = useRef(null);
  const localeTriggerRef = useRef(null);

  useEffect(() => {
    setLocale(i18n.language);
  }, [i18n.language]);

  /** daisyUI 5 keeps the panel on :focus-within; .dropdown-close forces it shut — blur so focus does not stick inside. */
  useEffect(() => {
    if (localeMenuOpen) return;
    const root = localeMenuRef.current;
    const ae = document.activeElement;
    if (root && ae instanceof HTMLElement && root.contains(ae)) {
      ae.blur();
    } else {
      localeTriggerRef.current?.blur();
    }
  }, [localeMenuOpen]);

  useEffect(() => {
    if (!localeMenuOpen) return;
    /** Capture: run before target handlers so we only react to true “outside” taps (avoids fighting the close button). */
    const onPointerDownCapture = (e) => {
      if (!localeMenuRef.current || !(e.target instanceof Node)) return;
      if (!localeMenuRef.current.contains(e.target)) {
        setLocaleMenuOpen(false);
      }
    };
    const onKeyDown = (e) => {
      if (e.key === 'Escape') setLocaleMenuOpen(false);
    };
    document.addEventListener('pointerdown', onPointerDownCapture, true);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('pointerdown', onPointerDownCapture, true);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [localeMenuOpen]);

  const handleLocale = (lng) => {
    i18n.changeLanguage(lng);
    setLocale(lng);
    localStorage.setItem('locale', lng);
    setLocaleMenuOpen(false);
  };

  const isProductsArea =
    pathname === '/products' ||
    pathname.startsWith('/products/') ||
    /^\/categories\/[^/]+\/products$/.test(pathname);

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
        <aside className="flex min-h-full w-[min(100vw,20rem)] max-w-[min(100vw,20rem)] flex-col border-r border-base-300 bg-base-100 shadow-2xl">
          <div className="header-gradient-line h-1 shrink-0" aria-hidden="true" />
          <div className="flex items-center justify-between gap-2 border-b border-base-200 bg-gradient-to-r from-base-200/80 via-base-100 to-base-100 px-3 py-3">
            <span className="text-base font-semibold tracking-tight text-base-content">{t('common.menu')}</span>
            <label
              htmlFor={STOREFRONT_DRAWER_ID}
              className="btn btn-ghost btn-sm btn-circle shrink-0 text-base-content"
              aria-label={t('common.close')}
            >
              <IconX className="h-5 w-5" aria-hidden="true" />
            </label>
          </div>

          <nav className="flex flex-1 flex-col overflow-y-auto px-2 py-3" aria-label={t('common.menu')}>
            <ul className="flex flex-col gap-1">
              <li>
                <NavLink
                  to="/"
                  end
                  onClick={closeStorefrontDrawer}
                  className={({ isActive }) => drawerNavClass(isActive)}
                >
                  {({ isActive }) => (
                    <>
                      <IconHome className={drawerIconClass(isActive)} aria-hidden="true" />
                      {t('shop.home')}
                    </>
                  )}
                </NavLink>
              </li>
              <li>
                <Link
                  to="/products"
                  onClick={closeStorefrontDrawer}
                  className={drawerNavClass(isProductsArea)}
                  aria-current={isProductsArea ? 'page' : undefined}
                >
                  <IconGrid className={drawerIconClass(isProductsArea)} aria-hidden="true" />
                  {t('shop.products')}
                </Link>
              </li>
              <li>
                <NavLink
                  to="/custom-solution"
                  onClick={closeStorefrontDrawer}
                  className={({ isActive }) => drawerNavClass(isActive)}
                >
                  {({ isActive }) => (
                    <>
                      <IconSparkles className={drawerIconClass(isActive)} aria-hidden="true" />
                      {t('shop.custom_solution')}
                    </>
                  )}
                </NavLink>
              </li>
              <li>
                <NavLink
                  to="/faq"
                  onClick={closeStorefrontDrawer}
                  className={({ isActive }) => drawerNavClass(isActive)}
                >
                  {({ isActive }) => (
                    <>
                      <IconHelpCircle className={drawerIconClass(isActive)} aria-hidden="true" />
                      {t('shop.faq.nav')}
                    </>
                  )}
                </NavLink>
              </li>
              <li>
                <NavLink
                  to="/cart"
                  onClick={closeStorefrontDrawer}
                  className={({ isActive }) => drawerNavClass(isActive)}
                >
                  {({ isActive }) => (
                    <>
                      <IconCart className={drawerIconClass(isActive)} aria-hidden="true" />
                      {t('shop.cart')}
                    </>
                  )}
                </NavLink>
              </li>
            </ul>

            <hr className="my-3 shrink-0 border-0 border-t border-base-200" />

            {authLoading ? (
              <div className="flex justify-center py-2" aria-busy="true" aria-label={t('common.loading')}>
                <span className="loading loading-spinner loading-sm text-primary" />
              </div>
            ) : user ? (
              <ul className="flex flex-col gap-1" aria-label={t('shop.account')}>
                <li>
                  <NavLink
                    to="/profile"
                    end
                    onClick={closeStorefrontDrawer}
                    className={({ isActive }) => drawerNavClass(isActive)}
                  >
                    {({ isActive }) => (
                      <>
                        <IconUser className={drawerIconClass(isActive)} aria-hidden="true" />
                        {t('shop.profile')}
                      </>
                    )}
                  </NavLink>
                </li>
                <li>
                  <NavLink
                    to="/orders"
                    onClick={closeStorefrontDrawer}
                    className={({ isActive }) => drawerNavClass(isActive)}
                  >
                    {({ isActive }) => (
                      <>
                        <IconClipboardList className={drawerIconClass(isActive)} aria-hidden="true" />
                        {t('shop.orders')}
                      </>
                    )}
                  </NavLink>
                </li>
                {user.email_verified ? (
                  <li>
                    <NavLink
                      to="/favorites"
                      onClick={closeStorefrontDrawer}
                      className={({ isActive }) => drawerNavClass(isActive)}
                    >
                      {({ isActive }) => (
                        <>
                          <IconHeart className={drawerIconClass(isActive)} filled={isActive} aria-hidden="true" />
                          {t('shop.favorites')}
                        </>
                      )}
                    </NavLink>
                  </li>
                ) : null}
                <li>
                  <NavLink
                    to="/purchases"
                    end
                    onClick={closeStorefrontDrawer}
                    className={({ isActive }) => drawerNavClass(isActive)}
                  >
                    {({ isActive }) => (
                      <>
                        <IconPackage className={drawerIconClass(isActive)} aria-hidden="true" />
                        {t('shop.purchases')}
                      </>
                    )}
                  </NavLink>
                </li>
              </ul>
            ) : (
              <ul className="flex flex-col gap-1">
                <li>
                  <Link
                    to="/login"
                    onClick={closeStorefrontDrawer}
                    className={drawerNavClass(false)}
                  >
                    <IconLogIn className={drawerIconClass(false)} aria-hidden="true" />
                    {t('auth.login')}
                  </Link>
                </li>
              </ul>
            )}

            <div className="mt-auto border-t border-base-200 bg-gradient-to-b from-base-100 to-base-200/50 px-3 pb-4 pt-4">
              <div
                ref={localeMenuRef}
                className={`dropdown dropdown-bottom mb-4 w-full min-w-0 ${localeMenuOpen ? 'dropdown-open' : 'dropdown-close'}`}
              >
                <button
                  ref={localeTriggerRef}
                  type="button"
                  className="btn btn-outline btn-sm h-auto min-h-10 w-full shrink-0 justify-between gap-2 border-base-300 px-3 py-2 font-normal normal-case"
                  aria-expanded={localeMenuOpen}
                  aria-haspopup="listbox"
                  aria-label={t('common.language')}
                  onClick={() => setLocaleMenuOpen((o) => !o)}
                >
                  <span className="min-w-0 truncate text-left text-sm text-base-content">
                    {t('common.language')}
                    <span className="mx-1.5 text-base-content/40" aria-hidden>
                      ·
                    </span>
                    <span className="font-semibold tabular-nums text-primary">{localeCode(locale)}</span>
                  </span>
                  <IconChevronDown
                    className={`h-4 w-4 shrink-0 text-base-content/70 transition-transform duration-200 ${localeMenuOpen ? 'rotate-180' : ''}`}
                    aria-hidden="true"
                  />
                </button>
                <div className="dropdown-content z-[70] mt-1 w-full min-w-[12rem] max-w-full">
                  <div className="card card-border border border-base-300 bg-base-100 p-2 shadow-xl">
                    <ul className="menu menu-sm max-h-60 flex-nowrap overflow-y-auto p-0" role="listbox" aria-label={t('common.language')}>
                      {STOREFRONT_LANGUAGE_OPTIONS.map(({ code, label }) => {
                        const selected = locale === code;
                        return (
                          <li key={code} role="none">
                            <button
                              type="button"
                              role="option"
                              aria-selected={selected}
                              className={`flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left active:bg-base-200 ${
                                selected
                                  ? 'bg-gradient-to-r from-primary/15 to-secondary/10 font-medium text-primary'
                                  : 'hover:bg-base-200'
                              }`}
                              onClick={() => handleLocale(code)}
                            >
                              <span>{label}</span>
                              {selected ? (
                                <span className="text-xs font-bold text-primary tabular-nums" aria-hidden>
                                  ✓
                                </span>
                              ) : null}
                            </button>
                          </li>
                        );
                      })}
                    </ul>
                  </div>
                </div>
              </div>

              <p className="text-sm font-semibold leading-tight text-base-content">{t('shop.brand_name')}</p>
              <p className="mt-1 text-xs leading-snug text-base-content/65">{t('footer.tagline')}</p>
            </div>
          </nav>
        </aside>
      </div>
    </div>
  );
}
