import { Link, useLocation } from 'react-router-dom'
import { useContext, useState, useEffect } from 'react'
import { useTranslation } from 'react-i18next'
import { AuthContext } from '../contexts/AuthContext'
import { CartContext } from '../contexts/CartContext'
import {
  HiOutlineShoppingCart,
  HiOutlineUserCircle,
  HiOutlineBars3,
  HiOutlineMagnifyingGlass,
  HiXMark,
  HiOutlineHome,
  HiOutlineCube,
  HiOutlineShoppingBag,
  HiOutlineTag,
  HiOutlineWrenchScrewdriver,
  HiOutlineQuestionMarkCircle,
  HiArrowRightOnRectangle,
} from 'react-icons/hi2'

const DRAWER_ID = 'shop-drawer'

const navLinks = [
  { to: '/', labelKey: 'nav.home', icon: HiOutlineHome },
  { to: '/products', labelKey: 'nav.products', icon: HiOutlineCube },
  { to: '/cart', labelKey: 'nav.cart', icon: HiOutlineShoppingBag },
  { to: '/faq', labelKey: 'nav.faq', icon: HiOutlineQuestionMarkCircle },
  { to: '/custom-solution', labelKey: 'nav.custom_solution', icon: HiOutlineWrenchScrewdriver },
]

export default function Navbar() {
  const { t } = useTranslation()
  const { user, logout } = useContext(AuthContext)
  const cartContext = useContext(CartContext)
  const location = useLocation()

  const cartCount = cartContext?.itemCount ?? 0

  const closeDrawer = () => {
    const drawer = document.getElementById(DRAWER_ID)
    if (drawer) drawer.checked = false
  }

  const isActive = (path) => {
    if (path === '/') return location.pathname === '/'
    return location.pathname.startsWith(path)
  }

  return (
    <div className="drawer">
      <input id={DRAWER_ID} type="checkbox" className="drawer-toggle" tabIndex={-1} aria-hidden="true" />

      <div className="drawer-content">
        <div className="navbar bg-white/90 backdrop-blur-sm shadow-sm" role="banner">
          <div className="navbar-start flex items-center gap-2">
            <label
              htmlFor={DRAWER_ID}
              aria-label={t('nav.open_menu', 'Obre el menú')}
              className="btn btn-square btn-ghost xl:hidden"
            >
              <HiOutlineBars3 className="shop-tobar-end__icon" aria-hidden="true" />
            </label>
            <Link to="/" className="shop-topbar__logo text-primary ml-1 hidden xl:block font-bold text-lg">
              {t('app.name', 'La Botiga')}
            </Link>
          </div>

          <nav className="navbar-center hidden xl:flex" aria-label={t('nav.main_nav', 'Navegació principal')}>
            <ul className="menu menu-horizontal px-1">
              {navLinks.map(({ to, labelKey }) => (
                <li key={to}>
                  <Link
                    to={to}
                    className={`shop-topbar__menu-link${isActive(to) ? ' text-primary' : ''}`}
                  >
                    {t(labelKey, labelKey.split('.').pop())}
                  </Link>
                </li>
              ))}
            </ul>
          </nav>

          <div className="navbar-end flex items-center gap-2">
            {(!user || user.role !== 'admin') && (
              <div className="relative group">
                {cartCount > 0 && (
                  <span
                    className="absolute top-1 right-0 badge badge-primary w-3 h-3 min-w-0 p-0 z-10 transition-transform duration-150 ease-in-out group-hover:-translate-y-0.5"
                    aria-label={`${cartCount} productes al carret`}
                  />
                )}
                <Link to="/cart" className="btn btn-ghost btn-circle" aria-label={t('nav.cart', 'Carret')}>
                  <HiOutlineShoppingCart className="shop-tobar-end__icon" aria-hidden="true" />
                </Link>
              </div>
            )}

            {!user ? (
              <Link to="/login" className="btn btn-primary btn-sm">
                {t('auth.login', 'Inicia sessió')}
              </Link>
            ) : (
              <div className="dropdown dropdown-end">
                <button
                  tabIndex={0}
                  className="shop-tobar-end__dropdown-button btn btn-ghost btn-circle"
                  aria-label={t('nav.user_menu', "Menú d'usuari")}
                >
                  <HiOutlineUserCircle className="shop-tobar-end__icon" aria-hidden="true" />
                </button>
                <ul tabIndex={-1} className="dropdown-content menu bg-base-100 rounded-box z-50 w-52 p-2 shadow-sm gap-2">
                  {user.role === 'admin' || user.role === 1 ? (
                    <li>
                      <Link to="/admin" className="btn btn-primary">
                        {t('nav.admin_panel', 'Panell Admin')}
                      </Link>
                    </li>
                  ) : (
                    <li>
                      <Link to="/orders" className="btn btn-primary">
                        {t('nav.my_orders', 'Les meves comandes')}
                      </Link>
                    </li>
                  )}
                  <li>
                    <Link to="/profile" className="text-sm">
                      {t('nav.profile', 'El meu perfil')}
                    </Link>
                  </li>
                  <li
                    className="btn btn-error btn-sm"
                    onClick={logout}
                    aria-label={t('auth.logout', 'Tancar sessió')}
                  >
                    {t('auth.logout', 'Tancar sessió')}
                  </li>
                </ul>
              </div>
            )}
          </div>
        </div>
      </div>

      {/* Mobile drawer sidebar */}
      <div className="drawer-side" aria-label={t('nav.sidebar', 'Menú lateral')}>
        <label htmlFor={DRAWER_ID} aria-label={t('nav.close_menu', 'Tanca el menú')} className="drawer-overlay" />
        <div className="flex flex-col h-full w-72 bg-base-100 shadow-xl">
          <div className="flex items-center justify-between px-6 py-5 border-b border-base-300">
            <span className="text-lg font-bold text-primary">{t('nav.menu', 'Menú')}</span>
            <label htmlFor={DRAWER_ID} className="btn btn-sm btn-circle btn-ghost">
              <HiXMark className="size-5" />
            </label>
          </div>
          <nav className="flex-1 overflow-y-auto py-4">
            <ul className="space-y-1 px-3">
              {navLinks.map(({ to, labelKey, icon: Icon }) => (
                <li key={to}>
                  <Link
                    to={to}
                    onClick={closeDrawer}
                    className={`flex items-center gap-3 px-4 py-3 rounded-lg transition-colors duration-200 ${
                      isActive(to)
                        ? 'bg-primary text-primary-content'
                        : 'text-base-content hover:bg-primary hover:text-primary-content'
                    }`}
                  >
                    <Icon className="size-5 shrink-0" />
                    <span className="font-medium">{t(labelKey, labelKey.split('.').pop())}</span>
                  </Link>
                </li>
              ))}
            </ul>
          </nav>
          <div className="px-6 py-4 border-t border-base-300 space-y-2">
            {!user ? (
              <Link
                to="/login"
                onClick={closeDrawer}
                className="btn btn-primary btn-block"
              >
                {t('auth.login', 'Inicia sessió')}
              </Link>
            ) : (
              <>
                {user.role === 'admin' || user.role === 1 ? (
                  <Link to="/admin" onClick={closeDrawer} className="btn btn-primary btn-block btn-sm">
                    {t('nav.admin_panel', 'Panell Admin')}
                  </Link>
                ) : (
                  <Link to="/orders" onClick={closeDrawer} className="btn btn-primary btn-block btn-sm">
                    {t('nav.my_orders', 'Comandes')}
                  </Link>
                )}
                <button onClick={() => { closeDrawer(); logout() }} className="btn btn-error btn-block btn-sm">
                  <HiArrowRightOnRectangle className="size-4" />
                  {t('auth.logout', 'Tancar sessió')}
                </button>
              </>
            )}
            <p className="text-xs text-base-400 text-center pt-2">
              © {new Date().getFullYear()} La Botiga
            </p>
          </div>
        </div>
      </div>
    </div>
  )
}
