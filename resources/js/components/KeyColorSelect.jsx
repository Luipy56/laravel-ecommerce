import React from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Compact dropdown for choosing a key color (cart / tables).
 */
export default function KeyColorSelect({
  colors = [],
  value = null,
  onChange,
  name = 'key_color',
  className = '',
  labelKey = 'shop.cart.key_color',
}) {
  const { t } = useTranslation();

  if (!colors.length) return null;

  const selectedId = value == null ? '' : String(value);

  return (
    <select
      className={`select select-bordered select-sm w-full max-w-[9rem] ${className}`.trim()}
      name={name}
      value={selectedId}
      onChange={(e) => {
        const raw = e.target.value;
        onChange(raw === '' ? null : Number(raw));
      }}
      aria-label={t(labelKey)}
    >
      <option value="">{t('shop.product.key_color_none')}</option>
      {colors.map((color) => {
        const label = color.name || color.rgb_code || '';
        return (
          <option key={color.id} value={String(color.id)}>
            {label}
          </option>
        );
      })}
    </select>
  );
}
