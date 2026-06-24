import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import TranslationFields from './TranslationFields';

function translationsToNames(translations, fallbackName = '') {
  return {
    ca: translations?.ca?.name ?? fallbackName,
    es: translations?.es?.name ?? '',
    en: translations?.en?.name ?? '',
  };
}

export default function AdminKeyColorForm({ color = null, onSubmit, loading = false, error = '' }) {
  const { t } = useTranslation();
  const [rgbCode, setRgbCode] = useState(color?.rgb_code ?? '#C0C0C0');
  const [sortOrder, setSortOrder] = useState(color?.sort_order ?? 0);
  const [isActive, setIsActive] = useState(color?.is_active ?? true);
  const [names, setNames] = useState(() => translationsToNames(color?.translations, color?.name ?? ''));

  const handleNamesChange = (locale, value) => setNames((prev) => ({ ...prev, [locale]: value }));

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({
      rgb_code: rgbCode.trim(),
      sort_order: Number(sortOrder) || 0,
      is_active: isActive,
      name: names.ca.trim(),
      translations: {
        es: { name: names.es.trim() },
        en: { name: names.en.trim() },
      },
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <div role="alert" className="alert alert-error text-sm">
          {error}
        </div>
      )}

      <label className="form-field">
        <span className="form-label">{t('admin.key_colors.rgb_code')} *</span>
        <div className="flex flex-wrap items-center gap-3">
          <input
            type="color"
            className="w-12 h-10 p-0 border border-base-300 rounded cursor-pointer"
            value={rgbCode.length === 7 ? rgbCode : '#C0C0C0'}
            onChange={(e) => setRgbCode(e.target.value.toUpperCase())}
            aria-label={t('admin.key_colors.rgb_code')}
          />
          <input
            type="text"
            className="input input-bordered w-full max-w-xs font-mono"
            value={rgbCode}
            onChange={(e) => setRgbCode(e.target.value.toUpperCase())}
            required
            pattern="#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?"
            aria-label={t('admin.key_colors.rgb_code')}
          />
        </div>
      </label>

      <TranslationFields
        field="name"
        values={names}
        onChange={handleNamesChange}
        label={t('admin.key_colors.name_translations')}
        required
      />

      <label className="form-field max-w-xs">
        <span className="form-label">{t('admin.key_colors.sort_order')}</span>
        <input
          type="number"
          min={0}
          className="input input-bordered w-full tabular-nums"
          value={sortOrder}
          onChange={(e) => setSortOrder(e.target.value)}
          aria-label={t('admin.key_colors.sort_order')}
        />
      </label>

      <label className="label cursor-pointer gap-2 justify-start">
        <input
          type="checkbox"
          className="checkbox checkbox-sm"
          checked={isActive}
          onChange={(e) => setIsActive(e.target.checked)}
        />
        <span className="label-text">{t('admin.products.is_active')}</span>
      </label>

      <div className="flex justify-between gap-2 pt-4">
        <Link to="/admin/key-colors" className="btn btn-ghost">
          {t('common.back')}
        </Link>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
