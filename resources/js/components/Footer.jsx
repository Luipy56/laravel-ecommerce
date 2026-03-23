import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { APP_VERSION } from '../config/version';

export default function Footer() {
  const { t } = useTranslation();
  const year = new Date().getFullYear();

  return (
    <footer className="footer w-full bg-base-100 text-base-content mt-auto border-t border-base-content/10">
      <div className="footer-top container mx-auto px-4 py-6">
        <div className="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
          {/* Brand + logo */}
          <aside className="flex flex-col items-start gap-3 lg:col-span-1">
            <Link to="/" className="flex items-center gap-2">
              <img
                src="/images/serraller_solidaria_logo.png"
                alt={t('shop.brand_logo_alt')}
                className="h-12 w-auto object-contain"
              />
            </Link>
            <p className="text-sm opacity-80 max-w-xs">{t('footer.tagline')}</p>
          </aside>

          {/* Contact */}
          <nav className="flex flex-col gap-2">
            <h6 className="footer-title text-base">{t('footer.contact')}</h6>
            <a href="tel:+34600500517" className="link link-hover text-sm">
              600 500 517
            </a>
            <a
              href="mailto:empresa@serralleriasolidaria.cat"
              className="link link-hover text-sm"
            >
              empresa@serralleriasolidaria.cat
            </a>
          </nav>

          {/* Explore */}
          <nav className="flex flex-col gap-2">
            <h6 className="footer-title text-base">{t('footer.explore')}</h6>
            <Link to="/" className="link link-hover text-sm">
              {t('shop.home')}
            </Link>
            <Link to="/products" className="link link-hover text-sm">
              {t('shop.products')}
            </Link>
            <Link to="/custom-solution" className="link link-hover text-sm">
              {t('shop.custom_solution')}
            </Link>
          </nav>

          {/* Legal */}
          <nav className="flex flex-col gap-2">
            <h6 className="footer-title text-base">{t('footer.legal')}</h6>
            <a
              href="https://www.serralleriasolidaria.cat/"
              target="_blank"
              rel="noopener noreferrer"
              className="link link-hover text-sm"
            >
              {t('footer.legal_link')}
            </a>
            <p className="text-xs opacity-70 mt-1">{t('footer.legal_disclaimer')}</p>
          </nav>
        </div>
      </div>

      <div className="footer-bottom header-gradient-line w-full border-t border-base-300 py-3 text-white relative">
        <span className="absolute left-4 top-1/2 -translate-y-1/2 text-xs opacity-60">
          {t('footer.version', { version: APP_VERSION })}
        </span>
        <div className="container mx-auto px-4 flex flex-wrap justify-center items-center gap-2">
          <p className="text-sm opacity-90">
            Developed by{' '}
            <a
              href="https://ldeluipy.es"
              target="_blank"
              rel="noopener noreferrer"
              className="link link-hover text-white/90 hover:text-white"
            >
              ldeluipy
            </a>
          </p>
          <p className="text-sm opacity-90">
            {t('footer.copyright', { year })}
          </p>
        </div>
      </div>
    </footer>
  );
}
