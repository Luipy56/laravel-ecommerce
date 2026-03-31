import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconMenu } from './icons';

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
  const navigate = useNavigate();
  const location = useLocation();
  const [locale, setLocale] = useState(i18n.language);
  const [searchQ, setSearchQ] = useState('');
  const [visible, setVisible] = useState(true);
  const lastScrollY = useRef(0);
  const debounceTimerRef = useRef(null);
  const hasUserEditedSearchRef = useRef(false);

  // Sync search input with URL when on product list (so clearing + Enter updates list)
  useEffect(() => {
    if (location.pathname === '/products') {
      const q = new URLSearchParams(location.search).get('search');
      hasUserEditedSearchRef.current = false;
      setSearchQ(q ?? '');
    }
  }, [location.pathname, location.search]);

  useEffect(() => {
    const handleScroll = () => {
      const y = window.scrollY;
      if (y <= SCROLL_THRESHOLD) {
        setVisible(true);
      } else {
        const delta = y - lastScrollY.current;
        if (delta > SCROLL_DELTA) setVisible(false);
        else if (delta < -SCROLL_DELTA) setVisible(true);
      }
      lastScrollY.current = y;
    };
    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const handleLocale = (lng) => {
    i18n.changeLanguage(lng);
    setLocale(lng);
    localStorage.setItem('locale', lng);
  };

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
      {/* Spacer: taller on mobile due to search row */}
      <div className="h-24 lg:h-16 flex-shrink-0" aria-hidden="true" />
      <header
        className="fixed top-0 left-0 right-0 z-50 transition-transform duration-300 ease-out bg-base-100 shadow-lg"
        style={{ transform: visible ? 'translateY(0)' : 'translateY(-100%)' }}
      >
        {/* Main row: no overlap; start can shrink, end stays fixed */}
        <div className="navbar min-h-0 flex-nowrap gap-1 sm:gap-2 px-2 sm:px-4">
          <div className="navbar-start min-w-0 flex-1 flex items-center flex-nowrap gap-1 sm:gap-2">
            <label htmlFor="drawer-nav" className="btn btn-ghost btn-square btn-sm shrink-0 lg:hidden" aria-label={t('common.menu')}>
              <IconMenu className="h-6 w-6" />
            </label>
            <Link
              to="/"
              className="btn btn-ghost text-lg sm:text-xl min-w-0 px-1 sm:px-2 truncate shrink-0"
              title={t('shop.brand_name')}
            >
              <span className="truncate">{t('shop.brand_name')}</span>
            </Link>
            <Link to="/products" className="btn btn-ghost hidden sm:inline-flex shrink-0">{t('shop.products')}</Link>
            <Link to="/custom-solution" className="btn btn-ghost hidden sm:inline-flex shrink-0">{t('shop.custom_solution')}</Link>
            <form onSubmit={handleSearch} className="join hidden lg:flex shrink-0 min-w-0">
              <input
                type="search"
                className="input input-bordered join-item w-36 xl:w-48 input-sm min-w-0"
                placeholder={t('shop.search_placeholder')}
                value={searchQ}
                onChange={handleSearchInputChange}
                aria-label={t('shop.search_placeholder')}
              />
              <button type="submit" className="btn btn-primary join-item btn-sm">
                {t('common.search')}
              </button>
            </form>
          </div>
          <div className="navbar-end gap-1 sm:gap-2 shrink-0">
            <div className="dropdown dropdown-end">
              <label tabIndex={0} className="btn btn-ghost btn-sm btn-square sm:btn-sm">
                {locale === 'ca' ? 'CA' : 'ES'}
              </label>
              <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-32 p-2 shadow">
                <li><button type="button" onClick={() => handleLocale('ca')}>Català</button></li>
                <li><button type="button" onClick={() => handleLocale('es')}>Español</button></li>
              </ul>
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
                className="btn btn-ghost btn-sm min-w-[6rem] pointer-events-none shrink-0"
                aria-busy="true"
                aria-label={t('common.loading')}
              >
                <span className="loading loading-spinner loading-sm" />
              </div>
            ) : user ? (
              <div className="dropdown dropdown-end">
                <label tabIndex={0} className="btn btn-ghost btn-sm max-w-[7rem] sm:max-w-none truncate">
                  <span className="truncate">{user.name?.trim() || user.login_email}</span>
                </label>
                <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-52 p-2 shadow">
                  <li><Link to="/profile">{t('shop.profile')}</Link></li>
                  <li><Link to="/orders">{t('shop.orders')}</Link></li>
                  <li><button type="button" onClick={handleLogout}>{t('auth.logout')}</button></li>
                </ul>
              </div>
            ) : (
              <Link to="/login" className="btn btn-primary btn-sm shrink-0">
                {t('auth.login')}
              </Link>
            )}
          </div>
        </div>
        {/* Mobile/tablet: search in its own row */}
        <div className="lg:hidden border-t border-base-200 px-2 py-2 sm:px-4">
          <form onSubmit={handleSearch} className="flex gap-2 w-full">
            <input
              type="search"
              className="input input-bordered input-sm flex-1 min-w-0"
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
        <div className="header-gradient-line h-1 w-full shrink-0" aria-hidden="true" />
      </header>
    </>
  );
}
