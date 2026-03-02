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
      <div className="h-16 flex-shrink-0" aria-hidden="true" />
      <header
        className="fixed top-0 left-0 right-0 z-50 transition-transform duration-300 ease-out"
        style={{ transform: visible ? 'translateY(0)' : 'translateY(-100%)' }}
      >
        <div className="navbar bg-base-100 shadow-lg">
      <div className="navbar-start">
        <label htmlFor="drawer-nav" className="btn btn-ghost btn-square drawer-button lg:hidden" aria-label={t('common.menu')}>
          <IconMenu className="h-6 w-6" />
        </label>
        <Link to="/" className="btn btn-ghost text-xl">Serralleria Solidària</Link>
        <Link to="/products" className="btn btn-ghost hidden sm:inline-flex">{t('shop.products')}</Link>
        <Link to="/custom-solution" className="btn btn-ghost hidden sm:inline-flex">{t('shop.custom_solution')}</Link>
      </div>
      <div className="navbar-center hidden lg:flex">
        <form onSubmit={handleSearch} className="join">
          <input
            type="search"
            className="input input-bordered join-item w-64"
            placeholder={t('shop.search_placeholder')}
            value={searchQ}
            onChange={(e) => setSearchQ(e.target.value)}
            aria-label={t('shop.search_placeholder')}
          />
          <button type="submit" className="btn btn-primary join-item">
            {t('common.search')}
          </button>
        </form>
      </div>
      <div className="navbar-end gap-2">
        <div className="dropdown dropdown-end">
          <label tabIndex={0} className="btn btn-ghost btn-sm">
            {locale === 'ca' ? 'CA' : 'ES'}
          </label>
          <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-32 p-2 shadow">
            <li><button type="button" onClick={() => handleLocale('ca')}>Català</button></li>
            <li><button type="button" onClick={() => handleLocale('es')}>Español</button></li>
          </ul>
        </div>
        <CartDropTarget to="/cart" className="btn btn-ghost btn-circle indicator">
          <span className="indicator-item badge badge-primary" id="cart-count">0</span>
          <IconCart className="h-6 w-6" aria-hidden="true" />
        </CartDropTarget>
        {user ? (
          <div className="dropdown dropdown-end">
            <label tabIndex={0} className="btn btn-ghost">
              {[user.name, user.surname].filter(Boolean).join(' ') || user.login_email}
            </label>
            <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-52 p-2 shadow">
              <li><Link to="/profile">{t('shop.profile')}</Link></li>
              <li><Link to="/orders">{t('shop.orders')}</Link></li>
              <li><button type="button" onClick={handleLogout}>{t('auth.logout')}</button></li>
            </ul>
          </div>
        ) : (
          <Link to="/login" className="btn btn-primary btn-sm">
            {t('auth.login')}
          </Link>
        )}
      </div>
        </div>
      </header>
    </>
  );
}
