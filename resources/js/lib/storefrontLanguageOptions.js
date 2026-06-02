/** Shared Tailwind classes for locale flag thumbnails (circular crop). */
export const STOREFRONT_FLAG_IMG_CLASS = 'shrink-0 rounded-full object-cover';

/** Storefront locales (labels and flags shown in Navbar and mobile drawer). */
export const STOREFRONT_LANGUAGE_OPTIONS = [
  // Locale `ca` = Català; flag-icons `ca` is Canada — use `es-ct` (Senyera).
  { code: 'ca', label: 'Català', flag: '/images/flags/es-ct.svg' },
  { code: 'es', label: 'Español', flag: '/images/flags/es.svg' },
  { code: 'en', label: 'English', flag: '/images/flags/gb.svg' },
];

/** @param {string} code */
export function storefrontLanguageFlag(code) {
  return STOREFRONT_LANGUAGE_OPTIONS.find((o) => o.code === code)?.flag ?? '/images/flags/gb.svg';
}
