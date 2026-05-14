import React, { useState, useEffect, useRef, useMemo } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import useApiPendingCount from '../hooks/useApiPendingCount';
import { useAuth } from '../contexts/AuthContext';
import { useStorefrontNavbarVisibility } from '../contexts/StorefrontNavbarVisibilityContext';
import { useCart } from '../contexts/CartContext';
import {
  IconCart,
  IconClipboardList,
  IconGamepad,
  IconHeart,
  IconLogOut,
  IconMenu,
  IconPackage,
  IconUser,
  IconX,
} from './icons';
import { STOREFRONT_LANGUAGE_OPTIONS } from '../lib/storefrontLanguageOptions';

const SCROLL_THRESHOLD = 10;   // px: below this, navbar is always visible
const SCROLL_DELTA = 5;        // px: min scroll movement to consider direction

function CartDropTarget({ to, className, children, ariaLabel, title }) {
  const { addLine } = useCart();
  const handleDragOver = (e) => { e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; };
  const handleDrop = (e) => {
    e.preventDefault();
    const productId = e.dataTransfer.getData('application/x-product-id');
    const packId = e.dataTransfer.getData('application/x-pack-id');
    if (productId) addLine(parseInt(productId, 10), null, 1);
    if (packId) addLine(null, parseInt(packId, 10), 1);
  };
  return (
    <Link
      to={to}
      className={className}
      onDragOver={handleDragOver}
      onDrop={handleDrop}
      aria-label={ariaLabel}
      title={title}
    >
      {children}
    </Link>
  );
}

export default function Navbar() {
  const { t, i18n } = useTranslation();
  const { user, logout, loading: authLoading } = useAuth();
  const apiPendingCount = useApiPendingCount();
  const navigate = useNavigate();
  const location = useLocation();
  const [locale, setLocale] = useState(i18n.language);
  const localeCode = (lng) => (lng === 'ca' ? 'CA' : lng === 'es' ? 'ES' : 'EN');
  const [searchQ, setSearchQ] = useState('');
  const { visible, setNavbarVisible } = useStorefrontNavbarVisibility();
  const lastScrollY = useRef(0);
  const debounceTimerRef = useRef(null);
  const hasUserEditedSearchRef = useRef(false);
  const [localeMenuOpen, setLocaleMenuOpen] = useState(false);
  const localeMenuRef = useRef(null);
  const localeTriggerRef = useRef(null);

  const packsOnlyInUrl = useMemo(
    () => new URLSearchParams(location.search).get('packs_only') === '1',
    [location.search]
  );
  const productsNavActive = location.pathname === '/products' && !packsOnlyInUrl;
  const packsNavActive = location.pathname === '/products' && packsOnlyInUrl;

  // Sync search input with URL when on product list (so clearing + Enter updates list)
  useEffect(() => {
    if (location.pathname === '/products') {
      const q = new URLSearchParams(location.search).get('search');
      hasUserEditedSearchRef.current = false;
      setSearchQ(q ?? '');
    }
  }, [location.pathname, location.search]);

  useEffect(() => {
    setLocale(i18n.language);
  }, [i18n.language]);

  /** daisyUI 5: :focus-within keeps panel open; .dropdown-close forces it closed — blur focus inside the control. */
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
    const handleScroll = () => {
      const y = window.scrollY;
      if (y <= SCROLL_THRESHOLD) {
        setNavbarVisible(true);
      } else {
        const delta = y - lastScrollY.current;
        if (delta > SCROLL_DELTA) setNavbarVisible(false);
        else if (delta < -SCROLL_DELTA) setNavbarVisible(true);
      }
      lastScrollY.current = y;
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, [setNavbarVisible]);

  const handleLocale = (lng) => {
    i18n.changeLanguage(lng);
    setLocale(lng);
    localStorage.setItem('locale', lng);
    setLocaleMenuOpen(false);
  };

  useEffect(() => {
    if (!localeMenuOpen) return;
    const onDocMouseDown = (e) => {
      if (localeMenuRef.current && !localeMenuRef.current.contains(e.target)) {
        setLocaleMenuOpen(false);
      }
    };
    const onKeyDown = (e) => {
      if (e.key === 'Escape') setLocaleMenuOpen(false);
    };
    document.addEventListener('mousedown', onDocMouseDown);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('mousedown', onDocMouseDown);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [localeMenuOpen]);

  const handleLogout = async () => {
    await logout();
    navigate('/');
  };

  const navigateCatalogSearch = (rawTerm) => {
    const term = rawTerm.trim();
    if (!term) {
      navigate('/products');
      return;
    }

    const currentParams = new URLSearchParams(location.search);
    const nextParams = new URLSearchParams();
    nextParams.set('search', term);

    if (location.pathname === '/products') {
      const categoryId = currentParams.get('category_id');
      if (categoryId) nextParams.set('category_id', categoryId);
      currentParams.getAll('feature_id').forEach((id) => nextParams.append('feature_id', id));
      navigate('/products?' + nextParams.toString());
      return;
    }

    const categoryProductsMatch = location.pathname.match(/^\/categories\/([^/]+)\/products$/);
    if (categoryProductsMatch) {
      currentParams.getAll('feature_id').forEach((id) => nextParams.append('feature_id', id));
      navigate(location.pathname + '?' + nextParams.toString());
      return;
    }

    navigate('/products?' + nextParams.toString());
  };

  useEffect(() => {
    if (!hasUserEditedSearchRef.current) return;
    if (debounceTimerRef.current) window.clearTimeout(debounceTimerRef.current);
    debounceTimerRef.current = window.setTimeout(() => {
      hasUserEditedSearchRef.current = false;
      navigateCatalogSearch(searchQ);
    }, 300);
    return () => {
      if (debounceTimerRef.current) window.clearTimeout(debounceTimerRef.current);
    };
  }, [searchQ]);

  const handleSearch = (e) => {
    e.preventDefault();
    if (debounceTimerRef.current) window.clearTimeout(debounceTimerRef.current);
    hasUserEditedSearchRef.current = false;
    navigateCatalogSearch(searchQ);
  };

  const handleSearchInputChange = (e) => {
    hasUserEditedSearchRef.current = true;
    setSearchQ(e.target.value);
  };

  return (
    <>
      {/* Spacer: mobile = main row + search row + gradient (~8rem); lg+ single row + gradient */}
      <div className="min-h-[8rem] shrink-0 lg:min-h-[4.25rem]" aria-hidden="true" />
      <header
        className="fixed top-0 left-0 right-0 z-50 w-full max-w-full min-w-0 transition-transform duration-300 ease-out bg-base-100 shadow-lg"
        style={{ transform: visible ? 'translateY(0)' : 'translateY(-100%)' }}
      >
        <div className="flex min-h-14 w-full min-w-0 max-w-full flex-nowrap items-center gap-1 px-2 py-1 sm:min-h-16 sm:gap-2 sm:px-4 sm:py-1">
          <div className="flex min-w-0 max-w-full flex-1 items-center gap-1 sm:gap-2">
            <label htmlFor="drawer-nav" className="btn btn-ghost btn-square btn-sm shrink-0 lg:hidden" aria-label={t('common.menu')}>
              <IconMenu className="h-6 w-6" />
            </label>
            <Link
              to="/"
              className="btn btn-ghost min-w-0 max-w-[min(12rem,calc(100vw-8.5rem))] shrink px-1 text-base normal-case sm:max-w-[14rem] sm:px-2 sm:text-xl md:max-w-none"
              title={t('shop.brand_name')}
            >
              <span className="truncate">{t('shop.brand_name')}</span>
            </Link>
            <Link
              to="/products"
              className={`btn btn-ghost hidden shrink-0 md:inline-flex${productsNavActive ? ' btn-active' : ''}`}
            >
              {t('shop.products')}
            </Link>
            <Link
              to="/products?packs_only=1"
              className={`btn btn-ghost hidden shrink-0 md:inline-flex${packsNavActive ? ' btn-active' : ''}`}
            >
              {t('shop.filters.packs_only')}
            </Link>
            <Link to="/custom-solution" className="btn btn-ghost hidden shrink-0 lg:inline-flex">
              {t('shop.custom_solution')}
            </Link>
            <Link to="/faq" className="btn btn-ghost hidden shrink-0 xl:inline-flex">
              {t('shop.faq.nav')}
            </Link>
            <form
              onSubmit={handleSearch}
              className="join ml-0 hidden min-w-0 flex-1 basis-0 lg:ml-1 lg:flex lg:max-w-md xl:max-w-lg"
            >
              <input
                type="search"
                className="input input-bordered join-item input-sm min-w-0 w-full flex-1 basis-0"
                placeholder={t('shop.search_placeholder')}
                value={searchQ}
                onChange={handleSearchInputChange}
                aria-label={t('shop.search_placeholder')}
              />
              <button type="submit" className="btn btn-primary join-item btn-sm shrink-0">
                {t('common.search')}
              </button>
            </form>
          </div>
          <div className="flex shrink-0 items-center gap-1 sm:gap-2">
            <div
              ref={localeMenuRef}
              className={`dropdown dropdown-end hidden lg:inline-block ${localeMenuOpen ? 'dropdown-open' : 'dropdown-close'}`}
            >
              <button
                ref={localeTriggerRef}
                type="button"
                className="btn btn-ghost btn-sm btn-square sm:btn-sm border border-transparent hover:border-base-300"
                aria-expanded={localeMenuOpen}
                aria-haspopup="listbox"
                aria-label={t('common.language')}
                onClick={() => setLocaleMenuOpen((o) => !o)}
              >
                <span className="font-semibold tracking-wide text-xs sm:text-sm">{localeCode(locale)}</span>
              </button>
              <div className="dropdown-content z-[60] mt-2 max-sm:right-0 max-sm:left-auto sm:right-0">
                <div className="card card-border w-[min(18rem,calc(100vw-1.5rem))] sm:w-52 border border-base-300 bg-base-100 shadow-xl">
                  <div className="flex items-center justify-between gap-3 border-b border-base-200 px-3 py-2.5">
                    <span className="text-sm font-semibold text-base-content">{t('common.language')}</span>
                    <button
                      type="button"
                      className="btn btn-ghost btn-sm btn-square shrink-0"
                      aria-label={t('common.close')}
                      onClick={() => setLocaleMenuOpen(false)}
                    >
                      <IconX className="h-5 w-5" aria-hidden="true" />
                    </button>
                  </div>
                  <ul className="menu menu-sm p-2 gap-0.5" role="listbox" aria-label={t('common.language')}>
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
                              <span className="text-primary text-xs font-bold tabular-nums" aria-hidden>
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
            <CartDropTarget
              to="/cart"
              className="btn btn-ghost btn-circle btn-sm indicator"
              ariaLabel={t('shop.cart')}
              title={t('shop.cart')}
            >
              <span className="indicator-item badge badge-primary badge-sm" id="cart-count">0</span>
              <IconCart className="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" />
            </CartDropTarget>
            {authLoading ? (
              <div
                className="btn btn-ghost btn-sm min-w-[6rem] pointer-events-none shrink-0 hidden lg:flex"
                aria-busy="true"
                aria-label={t('common.loading')}
              >
                <span className="loading loading-spinner loading-sm" />
              </div>
            ) : user ? (
              <div className="dropdown dropdown-end">
                <label
                  tabIndex={0}
                  className="btn btn-ghost btn-sm max-w-[6rem] gap-1.5 border border-transparent px-2 hover:border-base-300 normal-case sm:max-w-[8rem] lg:max-w-[10rem] xl:max-w-none"
                >
                  <span className="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                    <IconUser className="h-4 w-4" aria-hidden="true" />
                  </span>
                  <span className="truncate text-left">{user.name?.trim() || user.login_email}</span>
                </label>
                <div
                  tabIndex={0}
                  className="dropdown-content z-[60] mt-2 w-[min(18rem,calc(100vw-1.5rem))] sm:w-56 max-sm:right-0 max-sm:left-auto sm:right-0"
                >
                  <div className="card card-border border-base-300 bg-base-100 shadow-xl overflow-hidden">
                    <div className="flex items-center gap-2 border-b border-base-200 bg-gradient-to-r from-base-200/80 via-base-100 to-base-100 px-3 py-2.5">
                      <span className="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/15 text-primary ring-1 ring-primary/20">
                        <IconUser className="h-5 w-5" aria-hidden="true" />
                      </span>
                      <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-semibold text-base-content">
                          {user.name?.trim() || user.login_email}
                        </p>
                        {user.login_email && user.name?.trim() && user.login_email !== user.name.trim() ? (
                          <p className="truncate text-xs text-base-content/60">{user.login_email}</p>
                        ) : null}
                      </div>
                    </div>
                    <nav className="p-2" aria-label={t('shop.account')}>
                      <ul className="flex flex-col gap-0.5">
                        <li>
                          <Link
                            to="/profile"
                            className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                            onClick={() => document.activeElement?.blur()}
                          >
                            <IconUser className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                            {t('shop.profile')}
                          </Link>
                        </li>
                        <li>
                          <Link
                            to="/orders"
                            className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                            onClick={() => document.activeElement?.blur()}
                          >
                            <IconClipboardList className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                            {t('shop.orders')}
                          </Link>
                        </li>
                        {user.email_verified ? (
                          <li>
                            <Link
                              to="/favorites"
                              className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                              onClick={() => document.activeElement?.blur()}
                            >
                              <IconHeart className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                              {t('shop.favorites')}
                            </Link>
                          </li>
                        ) : null}
                        <li>
                          <Link
                            to="/purchases"
                            className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                            onClick={() => document.activeElement?.blur()}
                          >
                            <IconPackage className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                            {t('shop.purchases')}
                          </Link>
                        </li>
                        {user.email_verified ? (
                          <li>
                            <Link
                              to="/my-returns"
                              className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                              onClick={() => document.activeElement?.blur()}
                            >
                              <IconClipboardList className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                              {t('shop.returns.nav_link')}
                            </Link>
                          </li>
                        ) : null}
                        <li>
                          <Link
                            to="/games"
                            className="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-base-content transition-colors duration-200 hover:bg-base-200 active:bg-base-300"
                            onClick={() => document.activeElement?.blur()}
                          >
                            <IconGamepad className="h-5 w-5 shrink-0 text-primary" aria-hidden="true" />
                            {t('games.nav_link')}
                          </Link>
                        </li>
                      </ul>
                      <div className="mt-1 border-t border-base-200 pt-1">
                        <button
                          type="button"
                          className="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-error transition-colors duration-200 hover:bg-error/10 active:bg-error/15"
                          onClick={handleLogout}
                        >
                          <IconLogOut className="h-5 w-5 shrink-0" aria-hidden="true" />
                          {t('auth.logout')}
                        </button>
                      </div>
                    </nav>
                  </div>
                </div>
              </div>
            ) : (
              <Link to="/login" className="btn btn-primary btn-sm hidden shrink-0 lg:inline-flex">
                {t('auth.login')}
              </Link>
            )}
          </div>
        </div>
        {/* Mobile/tablet: search in its own row */}
        <div className="w-full min-w-0 max-w-full border-t border-base-200 px-2 py-2 sm:px-4 lg:hidden">
          <form onSubmit={handleSearch} className="flex w-full min-w-0 max-w-full gap-2">
            <input
              type="search"
              className="input input-bordered input-sm min-w-0 flex-1"
              placeholder={t('shop.search_placeholder')}
              value={searchQ}
              onChange={handleSearchInputChange}
              aria-label={t('shop.search_placeholder')}
            />
            <button type="submit" className="btn btn-primary btn-sm shrink-0">
              {t('common.search')}
            </button>
          </form>
        </div>
        <div
          className={`header-gradient-line h-1 w-full shrink-0${apiPendingCount > 0 ? ' header-gradient-line--loading' : ''}`}
          aria-hidden="true"
        />
      </header>
    </>
  );
}
