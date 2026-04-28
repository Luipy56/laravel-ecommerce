import React, { useMemo, useState, useEffect } from 'react';
import { Link, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { IconMenu } from '../icons';
import { AdminToastProvider } from '../../contexts/AdminToastContext';
import { APP_VERSION } from '../../config/version';

const SECTION_NAV_KEYS = {
  about: 'admin.nav.about',
  'data-explorer': 'admin.nav.data_explorer',
  settings: 'admin.nav.settings',
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
  const localeCode = (lng) => (lng === 'ca' ? 'CA' : lng === 'es' ? 'ES' : 'EN');
  const languageAria = () => {
    if (locale === 'en') return 'English';
    if (locale === 'es') return 'Español';
    return 'Català';
  };

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

  const { primaryNavItems, secondaryNavItems } = useMemo(() => {
    const dashboard = { to: '/admin', labelKey: 'admin.nav.dashboard', alertKey: null };
    const primarySource = [
      { to: '/admin/admins', labelKey: 'admin.nav.admins', alertKey: null },
      { to: '/admin/categories', labelKey: 'admin.nav.categories', alertKey: null },
      { to: '/admin/products', labelKey: 'admin.nav.products', alertKey: null },
      { to: '/admin/variant-groups', labelKey: 'admin.nav.variant_groups', alertKey: null },
      { to: '/admin/clients', labelKey: 'admin.nav.clients', alertKey: null },
      { to: '/admin/orders', labelKey: 'admin.nav.orders', alertKey: 'orders' },
      { to: '/admin/personalized-solutions', labelKey: 'admin.nav.personalized_solutions', alertKey: 'personalized_solutions' },
      { to: '/admin/features', labelKey: 'admin.nav.features', alertKey: null },
      { to: '/admin/packs', labelKey: 'admin.nav.packs', alertKey: null },
    ];
    const primarySorted = [...primarySource].sort((a, b) => t(a.labelKey).localeCompare(t(b.labelKey)));
    const secondarySource = [
      { to: '/admin/settings', labelKey: 'admin.nav.settings', alertKey: null },
      { to: '/admin/data-explorer', labelKey: 'admin.nav.data_explorer', alertKey: null },
      { to: '/admin/about', labelKey: 'admin.nav.about', alertKey: null },
      { to: '/', labelKey: 'admin.back_to_shop', alertKey: null },
    ];
    return {
      primaryNavItems: [dashboard, ...primarySorted],
      secondaryNavItems: secondarySource,
    };
  }, [t]);

  const [navAlerts, setNavAlerts] = useState({
    orders: false,
    personalized_solutions: false,
  });

  useEffect(() => {
    if (!pathname.startsWith('/admin') || pathname.startsWith('/admin/login')) {
      return;
    }
    let cancelled = false;
    (async () => {
      try {
        const { data: body } = await api.get('admin/nav-alerts');
        if (cancelled || !body?.success || !body?.data) return;
        setNavAlerts({
          orders: Boolean(body.data.orders_need_attention),
          personalized_solutions: Boolean(body.data.personalized_solutions_need_attention),
        });
      } catch {
        if (!cancelled) {
          setNavAlerts({ orders: false, personalized_solutions: false });
        }
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [pathname]);

  const renderNavMenuItem = (item) => {
    const { to, labelKey, alertKey } = item;
    const showAttentionDot =
      alertKey === 'orders'
        ? navAlerts.orders
        : alertKey === 'personalized_solutions'
          ? navAlerts.personalized_solutions
          : false;
    const linkAria =
      showAttentionDot && alertKey
        ? `${t(labelKey)} · ${t(`admin.nav.alert_link_suffix_${alertKey}`)}`
        : undefined;
    return (
      <li key={to === '/' ? 'back-to-shop' : to}>
        <Link
          to={to}
          className={`rounded-lg flex items-center justify-between gap-2 ${isActive(to) ? 'bg-base-200/60 border-l-2 border-l-primary -ml-px' : ''}`}
          onClick={closeDrawer}
          aria-current={isActive(to) ? 'page' : undefined}
          aria-label={linkAria}
        >
          <span className="min-w-0 flex-1">{t(labelKey)}</span>
          {showAttentionDot ? (
            <span
              className="size-2 shrink-0 rounded-full bg-warning"
              aria-hidden="true"
              title={t(`admin.nav.alert_link_suffix_${alertKey}`)}
            />
          ) : null}
        </Link>
      </li>
    );
  };

  return (
    <div className="drawer lg:drawer-open min-h-screen bg-base-200">
      <input id="admin-drawer" type="checkbox" className="drawer-toggle" aria-label={t('common.menu')} />
      <div className="drawer-content flex min-w-0 flex-col">
        <header className="sticky top-0 z-10 bg-base-100 border-b border-base-200 shrink-0">
          <div className="container mx-auto px-4 py-3 lg:px-6 flex items-center justify-between gap-4 w-full">
            <div className="flex min-w-0 flex-1 items-center gap-2 lg:flex-initial">
              <label
                htmlFor="admin-drawer"
                className="btn btn-ghost btn-square drawer-button shrink-0 lg:hidden"
                aria-label={t('common.menu')}
              >
                <IconMenu className="h-6 w-6" />
              </label>
              <div className="min-w-0 flex-1 overflow-x-auto overscroll-x-contain [-webkit-overflow-scrolling:touch]">
                <nav className="breadcrumbs text-sm whitespace-nowrap" aria-label="Breadcrumb">
                  <ul className="!flex-nowrap">
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
            </div>
            <div className="dropdown dropdown-end shrink-0">
              <label tabIndex={0} className="btn btn-ghost btn-sm" aria-label={languageAria()}>
                {localeCode(locale)}
              </label>
              <ul tabIndex={0} className="dropdown-content menu bg-base-100 rounded-box z-10 w-36 p-2 shadow border border-base-200">
                <li><button type="button" onClick={() => handleLocale('ca')}>Català</button></li>
                <li><button type="button" onClick={() => handleLocale('es')}>Español</button></li>
                <li><button type="button" onClick={() => handleLocale('en')}>English</button></li>
              </ul>
            </div>
          </div>
        </header>
        <main className="container mx-auto min-w-0 max-w-full flex-1 px-4 pb-6 lg:px-6">
          <AdminToastProvider>
            <Outlet />
          </AdminToastProvider>
        </main>
      </div>
      <div className="drawer-side z-30">
        <label htmlFor="admin-drawer" aria-label={t('common.close')} className="drawer-overlay" />
        <aside className="bg-base-100 w-64 max-w-[min(100vw,16rem)] min-h-full flex flex-col border-r border-base-200">
          <div className="p-4 border-b border-base-200">
            <span className="font-bold text-lg text-base-content">{t('home.hero.title')}</span>
            <span className="block text-sm text-base-content/70">
              {t('admin.sidebar.subtitle', { version: APP_VERSION })}
            </span>
          </div>
          <ul className="menu p-4 flex-1">
            {primaryNavItems.map(renderNavMenuItem)}
            <li className="pointer-events-none px-1 py-2" aria-hidden="true">
              <div className="h-px w-full bg-base-300" role="separator" />
            </li>
            {secondaryNavItems.map(renderNavMenuItem)}
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
