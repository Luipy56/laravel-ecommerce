import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { APP_VERSION } from '../config/version';
import {
  IconFacebook,
  IconInstagram,
  IconLinkedIn,
  IconXTwitter,
  IconYouTube,
} from './icons';

const SOCIAL_LINKS = [
  {
    key: 'facebook',
    href: 'https://www.facebook.com/serralleriasolidaria',
    Icon: IconFacebook,
    label: 'Facebook',
  },
  {
    key: 'instagram',
    href: 'https://www.instagram.com/serralleriasolidaria',
    Icon: IconInstagram,
    label: 'Instagram',
  },
  {
    key: 'x',
    href: 'https://x.com/serralleriasolidaria',
    Icon: IconXTwitter,
    label: 'X (Twitter)',
  },
  {
    key: 'linkedin',
    href: 'https://www.linkedin.com/company/serralleria-solidaria',
    Icon: IconLinkedIn,
    label: 'LinkedIn',
  },
  {
    key: 'youtube',
    href: 'https://www.youtube.com/@serralleriasolidaria',
    Icon: IconYouTube,
    label: 'YouTube',
  },
];

export default function Footer() {
  const { t } = useTranslation();
  const year = new Date().getFullYear();

  return (
    <footer className="footer w-full bg-base-100 text-base-content mt-auto border-t border-base-content/10" role="contentinfo">
      <div className="container mx-auto px-4 py-10">
        <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">

          {/* Brand */}
          <aside className="flex flex-col gap-4 lg:col-span-1">
            <Link to="/" className="inline-flex items-center gap-2" aria-label={t('shop.brand_name')}>
              <img
                src="/images/serraller_solidaria_logo.png"
                alt={t('shop.brand_logo_alt')}
                className="h-12 w-auto object-contain"
              />
            </Link>
            <p className="text-sm leading-relaxed opacity-70 max-w-xs">{t('footer.tagline')}</p>
            <div className="flex flex-col gap-1 text-sm opacity-80">
              <span>Carrer Diputació, 426, 4rt 2ª</span>
              <span>08013 Barcelona</span>
            </div>
          </aside>

          {/* Navigation */}
          <nav className="flex flex-col gap-2" aria-label={t('footer.explore')}>
            <h6 className="footer-title text-base">{t('footer.explore')}</h6>
            <Link to="/" className="link link-hover text-sm">{t('shop.home')}</Link>
            <Link to="/products" className="link link-hover text-sm">{t('shop.products')}</Link>
            <Link to="/custom-solution" className="link link-hover text-sm">{t('shop.custom_solution')}</Link>
            <Link to="/faq" className="link link-hover text-sm">{t('shop.faq.nav')}</Link>
            <Link to="/privacy-policy" className="link link-hover text-sm">{t('footer.privacy_policy')}</Link>
            <Link to="/terms" className="link link-hover text-sm">{t('footer.terms')}</Link>
          </nav>

          {/* Contact */}
          <nav className="flex flex-col gap-2" aria-label={t('footer.contact')}>
            <h6 className="footer-title text-base">{t('footer.contact')}</h6>
            <a href="tel:+34600500517" className="link link-hover text-sm">
              +34 600 500 517
            </a>
            <a
              href="mailto:empresa@serralleriasolidaria.cat"
              className="link link-hover text-sm break-all"
            >
              empresa@serralleriasolidaria.cat
            </a>
            <p className="text-xs opacity-60 mt-1 max-w-xs">{t('footer.legal_disclaimer')}</p>
          </nav>

          {/* Social */}
          <div className="flex flex-col gap-4">
            <h6 className="footer-title text-base">{t('footer.social')}</h6>
            <div className="flex flex-wrap gap-3">
              {SOCIAL_LINKS.map(({ key, href, Icon, label }) => (
                <a
                  key={key}
                  href={href}
                  target="_blank"
                  rel="noopener noreferrer"
                  aria-label={label}
                  className="btn btn-circle btn-sm btn-ghost opacity-70 hover:opacity-100 hover:bg-base-200 transition-opacity"
                >
                  <Icon className="h-5 w-5" />
                </a>
              ))}
            </div>
            <div className="mt-2">
              <a
                href="https://www.serralleriasolidaria.cat/"
                target="_blank"
                rel="noopener noreferrer"
                className="link link-hover text-sm opacity-70"
              >
                {t('footer.legal_link')}
              </a>
            </div>
          </div>

        </div>
      </div>

      {/* Bottom bar */}
      <div className="header-gradient-line relative w-full border-t border-base-300 py-3 text-white">
        <Link
          to="/admin"
          className="absolute inset-y-0 left-0 z-10 w-16 max-w-[30%] min-w-10 cursor-default bg-transparent text-transparent select-none focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-white/55"
          aria-label={t('footer.admin_access_aria')}
        />
        <div className="container mx-auto px-4 flex flex-wrap items-center justify-between gap-2">
          <span className="text-xs opacity-60 shrink-0">
            {t('footer.version', { version: APP_VERSION })}
          </span>
          <p className="text-sm opacity-90 text-center flex-1">
            {t('footer.copyright', { year })}
          </p>
          <span className="text-xs opacity-60 shrink-0">
            Developed by{' '}
            <a
              href="https://ldeluipy.es"
              target="_blank"
              rel="noopener noreferrer"
              className="link link-hover text-white/90 hover:text-white"
            >
              ldeluipy
            </a>
          </span>
        </div>
      </div>
    </footer>
  );
}
