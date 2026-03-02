import React, { useState, useEffect, useRef } from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconMenu } from './icons';

const SCROLL_THRESHOLD = 10;   // px: below this, navbar is always visible
const SCROLL_DELTA = 5;        // px: min scroll movement to consider direction

function CartDropTarget({ to, className, children }) {
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
    <Link to={to} className={className} onDragOver={handleDragOver} onDrop={handleDrop}>
      {children}
    </Link>
  );
}

export default function Navbar() {
  const { t, i18n } = useTranslation();
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();
  const [locale, setLocale] = useState(i18n.language);
  const [searchQ, setSearchQ] = useState('');
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [visible, setVisible] = useState(true);
  const lastScrollY = useRef(0);

  // Sync search input with URL when on product list (so clearing + Enter updates list)
  useEffect(() => {
    if (location.pathname === '/products') {
      const q = new URLSearchParams(location.search).get('search');
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
    setDrawerOpen(false);
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const term = searchQ.trim();
    if (term) {
      navigate('/products?search=' + encodeURIComponent(term));
    } else {
      navigate('/products');
    }
    setDrawerOpen(false);
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
              title="Serralleria Solidària"
            >
              <span className="truncate">Serralleria Solidària</span>
            </Link>
            <Link to="/products" className="btn btn-ghost hidden sm:inline-flex shrink-0">{t('shop.products')}</Link>
            <Link to="/custom-solution" className="btn btn-ghost hidden sm:inline-flex shrink-0">{t('shop.custom_solution')}</Link>
            <form onSubmit={handleSearch} className="join hidden lg:flex shrink-0 min-w-0">
              <input
                type="search"
                className="input input-bordered join-item w-36 xl:w-48 input-sm min-w-0"
                placeholder={t('shop.search_placeholder')}
                value={searchQ}
                onChange={(e) => setSearchQ(e.target.value)}
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
            <CartDropTarget to="/cart" className="btn btn-ghost btn-circle btn-sm indicator">
              <span className="indicator-item badge badge-primary badge-sm" id="cart-count">0</span>
              <IconCart className="h-5 w-5 sm:h-6 sm:w-6" aria-hidden="true" />
            </CartDropTarget>
            {user ? (
              <div className="dropdown dropdown-end">
                <label tabIndex={0} className="btn btn-ghost btn-sm max-w-[7rem] sm:max-w-none truncate">
                  <span className="truncate">{[user.name, user.surname].filter(Boolean).join(' ') || user.login_email}</span>
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
              onChange={(e) => setSearchQ(e.target.value)}
              aria-label={t('shop.search_placeholder')}
            />
            <button type="submit" className="btn btn-primary btn-sm shrink-0">
              {t('common.search')}
            </button>
          </form>
        </div>
      </header>
    </>
  );
}
