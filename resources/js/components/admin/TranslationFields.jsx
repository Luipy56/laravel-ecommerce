import React from 'react';
import { useTranslation } from 'react-i18next';

const LOCALES = [
  { code: 'ca', flag: '🇦🇩', label: 'Català' },
  { code: 'es', flag: '🇪🇸', label: 'Castellano' },
  { code: 'en', flag: '🇬🇧', label: 'English' },
];

/**
 * Renders a block of per-locale text inputs for a single translatable field.
 *
 * Props:
 *   field        – the translation field key, e.g. 'name' or 'value'
 *   values       – object { ca: '', es: '', en: '' }
 *   onChange     – fn(locale, newValue)
 *   label        – base label string (appended with locale badge)
 *   required     – if true, marks the CA input as required
 *   inputClass   – optional extra class for <input>
 */
export default function TranslationFields({ field, values = {}, onChange, label, required = false, inputClass = '' }) {
  const { t } = useTranslation();

  return (
    <fieldset className="fieldset border border-base-300 rounded-box p-4 gap-3">
      <legend className="fieldset-legend text-sm font-semibold text-base-content/70">
        {label ?? t('admin.translations.section')}
      </legend>
      {LOCALES.map(({ code, flag, label: localeName }) => (
        <label key={code} className="form-field">
          <span className="form-label flex items-center gap-1.5">
            <span>{flag}</span>
            <span>{localeName}</span>
            {code === 'ca' && required && <span className="text-error">*</span>}
          </span>
          <input
            type="text"
            className={`input input-bordered w-full ${inputClass}`}
            value={values[code] ?? ''}
            onChange={(e) => onChange(code, e.target.value)}
            required={code === 'ca' && required}
            lang={code}
          />
        </label>
      ))}
    </fieldset>
  );
}
