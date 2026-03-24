import React, { useMemo, useState, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { IconMenu } from '../icons';
import { AdminToastProvider } from '../../contexts/AdminToastContext';

const SECTION_NAV_KEYS = {
  admins: 'admin.nav.admins',
  categories: 'admin.nav.categories',
  products: 'admin.nav.products',
  'variant-groups': 'admin.nav.variant_groups',
  clients: 'admin.nav.clients',
  orders: 'admin.nav.orders',
  'personalized-solutions': 'admin.nav.personalized_solutions',
  features: 'admin.nav.features',
  'feature-names': 'admin.nav.feature_types',
  packs: 'admin.nav.packs',
};

const SECTION_NEW_KEYS = {
  admins: 'admin.admins.new',
  categories: 'admin.categories.new',
  products: 'admin.products.new',
  features: 'admin.features.new',
  'feature-names': 'admin.feature_types.new',
  packs: 'admin.packs.new',
  'variant-groups': 'admin.variant_groups.new',
};

function getBreadcrumbs(pathname, t) {
  const parts = pathname.replace(/^\/admin\/?/, '').split('/').filter(Boolean);
  const crumbs = [{ label: t('admin.breadcrumb.admin'), path: '/admin' }];
  if (parts.length === 0) {
    crumbs[0].label = t('admin.nav.dashboard');
    return crumbs;
  }
  const section = parts[0];
  const sectionLabel = SECTION_NAV_KEYS[section] ? t(SECTION_NAV_KEYS[section]) : section;
  crumbs.push({ label: sectionLabel, path: `/admin/${section}` });
  if (parts.length >= 2) {
    if (parts[1] === 'new') {
      const newKey = SECTION_NEW_KEYS[section];
      crumbs.push({ label: newKey ? t(newKey) : t('common.create'), path: null });
    } else if (parts.length >= 3 && parts[2] === 'edit') {
      crumbs.push({ label: t('admin.breadcrumb.detail'), path: `/admin/${section}/${parts[1]}` });
      crumbs.push({ label: t('common.edit'), path: null });
    } else {
      crumbs.push({ label: t('admin.breadcrumb.detail'), path: null });
    }
  }
  return crumbs;
}

function closeDrawer() {
  document.getElementById('admin-drawer')?.click();
}

export default function AdminLayout() {
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const pathname = location.pathname;
  const [locale, setLocale] = useState(i18n.language);

  useEffect(() => {
    setLocale(i18n.language);
  }, [i18n.language]);

  const handleLocale = (lng) => {
    i18n.changeLanguage(lng);
    setLocale(lng);
    localStorage.setItem('locale', lng);
  };

  const breadcrumbs = useMemo(() => getBreadcrumbs(pathname, t), [pathname, t]);

  const isActive = (path) => {
    if (path === '/admin') return pathname === '/admin' || pathname === '/admin/';
    return pathname === path || pathname.startsWith(path + '/');
  };

  const handleLogout = async () => {
    try {
      await api.post('admin/logout');
      navigate('/admin/login');
    } catch {
      navigate('/admin/login');
    }
  };

  const navItems = useMemo(() => {
    const dashboard = { to: '/admin', labelKey: 'admin.nav.dashboard' };
    const mainItems = [
      { to: '/admin/admins', labelKey: 'admin.nav.admins' },
      { to: '/admin/categories', labelKey: 'admin.nav.categories' },
      { to: '/admin/products', labelKey: 'admin.nav.products' },
      { to: '/admin/variant-groups', labelKey: 'admin.nav.variant_groups' },
      { to: '/admin/clients', labelKey: 'admin.nav.clients' },
      { to: '/admin/orders', labelKey: 'admin.nav.orders' },
      { to: '/admin/personalized-solutions', labelKey: 'admin.nav.personalized_solutions' },
      { to: '/admin/features', labelKey: 'admin.nav.features' },
      { to: '/admin/packs', labelKey: 'admin.nav.packs' },
    ];
    const sorted = [...mainItems].sort((a, b) => t(a.labelKey).localeCompare(t(b.labelKey)));
    return [dashboard, ...sorted];
  }, [t]);

  return (
    <div className="drawer lg:drawer-open min-h-screen bg-base-200">
      <input id="admin-drawer" type="checkbox" className="drawer-toggle" aria-label={t('common.menu')} />
      <div className="drawer-content flex flex-col">
        <header className="sticky top-0 z-10 bg-base-100 border-b border-base-200 shrink-0">
          <div className="container mx-auto px-4 py-3 lg:px-6 flex items-center justify-between gap-4 w-full">
            <div className="flex items-center gap-2 min-w-0 flex-1 lg:flex-initial">
              <label
                htmlFor="admin-drawer"
                className="btn btn-ghost btn-square drawer-button shrink-0 lg:hidden"
                aria-label={t('common.menu')}
              >
                <IconMenu className="h-6 w-6" />
              </label>
              <nav className="breadcrumbs text-sm min-w-0" aria-label="Breadcrumb">
                <ul>
                  {breadcrumbs.map((crumb, i) => (
                    <li key={i}>
                      {crumb.path ? (
                        <Link to={crumb.path} className="text-base-content/80 hover:text-base-content">
                          {crumb.label}
                        </Link>
                      ) : (
                        <span className="text-base-content font-medium" aria-current="page">{crumb.label}</span>
                      )}
                    </li>
                  ))}
                </ul>
              </nav>
            </div>
            <div className="dropdown dropdown-end shrink-0">
              <label tabIndex={0} className="btn btn-ghost btn-sm" aria-label={locale === 'ca' ? 'Català' : 'Español'}>
                {locale === 'ca' ? 'CA' : 'ES'}
              </label>
              <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-32 p-2 shadow border border-base-200">
                <li><button type="button" onClick={() => handleLocale('ca')}>Català</button></li>
                <li><button type="button" onClick={() => handleLocale('es')}>Español</button></li>
              </ul>
            </div>
          </div>
        </header>
        <main className="flex-1 container mx-auto px-4 pb-6 lg:px-6">
          <AdminToastProvider>
            <Outlet />
          </AdminToastProvider>
        </main>
      </div>
      <div className="drawer-side z-30">
        <label htmlFor="admin-drawer" aria-label={t('common.close')} className="drawer-overlay" />
        <aside className="bg-base-100 w-64 min-h-full flex flex-col border-r border-base-200">
          <div className="p-4 border-b border-base-200">
            <span className="font-bold text-lg text-base-content">{t('home.hero.title')}</span>
            <span className="block text-sm text-base-content/70">Admin</span>
          </div>
          <ul className="menu p-4 flex-1">
            {navItems.map(({ to, labelKey }) => (
              <li key={to}>
                <Link
                  to={to}
                  className={`rounded-lg ${isActive(to) ? 'bg-base-200/60 border-l-2 border-l-primary -ml-px' : ''}`}
                  onClick={closeDrawer}
                  aria-current={isActive(to) ? 'page' : undefined}
                >
                  {t(labelKey)}
                </Link>
              </li>
            ))}
            <li>
              <Link to="/" className="rounded-lg" onClick={closeDrawer}>
                {t('admin.back_to_shop')}
              </Link>
            </li>
          </ul>
          <div className="p-4 border-t border-base-200">
            <button type="button" className="btn btn-ghost btn-block justify-start" onClick={handleLogout}>
              {t('admin.nav.logout')}
            </button>
          </div>
        </aside>
      </div>
    </div>
  );
}
