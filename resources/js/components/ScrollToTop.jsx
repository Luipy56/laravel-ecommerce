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
      className="btn btn-circle btn-primary fixed bottom-6 right-6 z-50 shadow-lg"
      aria-label={t('shop.scroll_top')}
    >
      <IconChevronUp className="h-6 w-6" />
    </button>
  );
}
