import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import ca from './locales/ca.json';
import es from './locales/es.json';
import en from './locales/en.json';

const SUPPORTED = ['ca', 'es', 'en'];
const raw = localStorage.getItem('locale') || navigator.language?.slice(0, 2) || 'ca';
const lng = SUPPORTED.includes(raw) ? raw : 'ca';

i18n.use(initReactI18next).init({
  resources: { ca: { translation: ca }, es: { translation: es }, en: { translation: en } },
  lng,
  fallbackLng: 'ca',
  interpolation: { escapeValue: false },
});

i18n.on('languageChanged', (code) => {
  if (typeof document !== 'undefined' && SUPPORTED.includes(code)) {
    document.documentElement.setAttribute('lang', code);
  }
});
if (typeof document !== 'undefined') {
  document.documentElement.setAttribute('lang', SUPPORTED.includes(i18n.language) ? i18n.language : 'ca');
}

export default i18n;
