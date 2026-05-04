import React, { useState, useEffect } from 'react';
import { useTranslation } from 'react-i18next';
import { IconChevronUp } from './icons';

export default function ScrollToTop() {
  const { t } = useTranslation();
  const [visible, setVisible] = useState(false);

  useEffect(() => {
    const handleScroll = () => setVisible(window.scrollY > 300);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  if (!visible) return null;

  return (
    <button
      type="button"
      onClick={scrollToTop}
      className="btn btn-circle fixed bottom-6 right-6 z-50 min-h-11 min-w-11 border-0 bg-gradient-to-br from-primary to-secondary text-primary-content shadow-lg shadow-primary/25 ring-1 ring-inset ring-white/15 transition-[filter,box-shadow,transform] duration-200 hover:brightness-110 hover:shadow-xl hover:shadow-primary/30 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary/40 motion-safe:active:scale-95"
      aria-label={t('shop.scroll_top')}
    >
      <IconChevronUp className="h-6 w-6 shrink-0 drop-shadow-sm" aria-hidden="true" />
    </button>
  );
}
