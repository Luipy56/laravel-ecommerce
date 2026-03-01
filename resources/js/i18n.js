import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import ca from './locales/ca.json';
import es from './locales/es.json';

const saved = localStorage.getItem('locale') || navigator.language?.slice(0, 2) || 'ca';
const lng = saved === 'ca' || saved === 'es' ? saved : 'ca';

i18n.use(initReactI18next).init({
  resources: { ca: { translation: ca }, es: { translation: es } },
  lng,
  fallbackLng: 'ca',
  interpolation: { escapeValue: false },
});

export default i18n;
