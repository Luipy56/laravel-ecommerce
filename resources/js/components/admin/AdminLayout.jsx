import React from 'react';
import { Link, Outlet, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { IconMenu } from '../icons';
import { AdminToastProvider } from '../../contexts/AdminToastContext';

export default function AdminLayout() {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      await api.post('admin/logout');
      navigate('/admin/login');
    } catch {
      navigate('/admin/login');
    }
  };

  return (
    <div className="drawer lg:drawer-open min-h-screen bg-base-200">
      <input id="admin-drawer" type="checkbox" className="drawer-toggle" aria-label={t('common.menu')} />
      <div className="drawer-content flex flex-col">
        <label
          htmlFor="admin-drawer"
          className="btn btn-ghost btn-square drawer-button fixed left-4 top-4 z-20 lg:hidden"
          aria-label={t('common.menu')}
        >
          <IconMenu className="h-6 w-6" />
        </label>
        <main className="flex-1 container mx-auto px-4 pt-14 pb-6 lg:pt-6 lg:px-6">
          <AdminToastProvider>
            <Outlet />
          </AdminToastProvider>
        </main>
      </div>
      <div className="drawer-side">
        <label htmlFor="admin-drawer" aria-label={t('common.close')} className="drawer-overlay" />
        <aside className="bg-base-100 w-64 min-h-full flex flex-col border-r border-base-200">
          <div className="p-4 border-b border-base-200">
            <span className="font-bold text-lg text-base-content">{t('home.hero.title')}</span>
            <span className="block text-sm text-base-content/70">Admin</span>
          </div>
          <ul className="menu p-4 flex-1">
            <li>
              <Link to="/admin" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.dashboard')}
              </Link>
            </li>
            <li>
              <Link to="/admin/admins" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.admins')}
              </Link>
            </li>
            <li>
              <Link to="/admin/products" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.products')}
              </Link>
            </li>
            <li>
              <Link to="/admin/variant-groups" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.variant_groups')}
              </Link>
            </li>
            <li>
              <Link to="/admin/clients" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.clients')}
              </Link>
            </li>
            <li>
              <Link to="/admin/orders" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.orders')}
              </Link>
            </li>
            <li>
              <Link to="/admin/personalized-solutions" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.personalized_solutions')}
              </Link>
            </li>
            <li>
              <Link to="/admin/features" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.features')}
              </Link>
            </li>
            <li>
              <Link to="/admin/feature-names" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.feature_types')}
              </Link>
            </li>
            <li>
              <Link to="/admin/packs" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
                {t('admin.nav.packs')}
              </Link>
            </li>
            <li>
              <Link to="/" className="rounded-lg" onClick={() => document.getElementById('admin-drawer')?.click()}>
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
