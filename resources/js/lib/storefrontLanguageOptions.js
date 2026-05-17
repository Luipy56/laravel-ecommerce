/** Storefront locales (labels and flags shown in Navbar and mobile drawer). */
export const STOREFRONT_LANGUAGE_OPTIONS = [
  { code: 'ca', label: 'Català', flag: '/images/flags/ca.svg' },
  { code: 'es', label: 'Español', flag: '/images/flags/es.svg' },
  { code: 'en', label: 'English', flag: '/images/flags/gb.svg' },
];

/** @param {string} code */
export function storefrontLanguageFlag(code) {
  return STOREFRONT_LANGUAGE_OPTIONS.find((o) => o.code === code)?.flag ?? '/images/flags/gb.svg';
}
