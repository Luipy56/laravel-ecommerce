import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { IconChevronUp } from './icons';

/**
 * Radio swatches for global key colors plus a "none/default" option.
 */
export default function KeyColorPicker({
  colors = [],
  value = null,
  onChange,
  name = 'key_color',
  compact = false,
}) {
  const { t } = useTranslation();
  const [expanded, setExpanded] = useState(compact);

  if (!colors.length) return null;

  const selectedId = value == null ? '' : String(value);

  const swatches = (
    <ul className={`flex flex-wrap gap-3 list-none p-0 m-0 ${compact ? '' : 'pt-2'}`} role="radiogroup" aria-label={t('shop.product.key_color')}>
      <li className="list-none">
        <label className="flex flex-col items-center gap-1 cursor-pointer max-w-[5rem]">
          <input
            type="radio"
            className="sr-only peer"
            name={name}
            value=""
            checked={selectedId === ''}
            onChange={() => onChange(null)}
          />
          <span
            className="w-10 h-10 rounded-full border-2 border-base-300 bg-base-100 flex items-center justify-center text-base font-semibold text-base-content/70 peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/30"
            aria-hidden
          >
            ?
          </span>
          <span className="text-xs text-center leading-tight">{t('shop.product.key_color_none')}</span>
        </label>
      </li>
      {colors.map((color) => {
        const id = String(color.id);
        const isSelected = selectedId === id;
        const label = color.name || color.rgb_code || '';
        return (
          <li key={color.id} className="list-none">
            <label className="flex flex-col items-center gap-1 cursor-pointer max-w-[5rem]">
              <input
                type="radio"
                className="sr-only peer"
                name={name}
                value={id}
                checked={isSelected}
                onChange={() => onChange(color.id)}
              />
              <span
                className="w-10 h-10 rounded-full border-2 border-base-300 peer-checked:border-primary peer-checked:ring-2 peer-checked:ring-primary/30"
                style={{ backgroundColor: color.rgb_code }}
                aria-hidden
              />
              <span className="text-xs text-center leading-tight truncate w-full" title={label}>
                {label}
              </span>
            </label>
          </li>
        );
      })}
    </ul>
  );

  if (compact) {
    return (
      <div role="group" aria-label={t('shop.product.key_color')} className="space-y-1">
        <p className="text-sm font-medium text-base-content/80">{t('shop.product.key_color')}</p>
        {swatches}
      </div>
    );
  }

  return (
    <div role="group" aria-label={t('shop.product.key_color')}>
      <div className={`collapse border border-base-300 rounded-lg bg-base-200/50 ${expanded ? 'collapse-open' : 'collapse-close'}`}>
        <div
          className="collapse-title min-h-0 py-2 pr-10 font-medium text-sm text-base-content/80 flex items-center gap-2"
          role="button"
          tabIndex={0}
          aria-expanded={expanded}
          onClick={() => setExpanded((v) => !v)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' || e.key === ' ') {
              e.preventDefault();
              setExpanded((v) => !v);
            }
          }}
        >
          <span>{t('shop.product.key_color')}</span>
          <span className="text-primary">
            · {expanded ? t('shop.product.hide_key_colors') : t('shop.product.show_key_colors')}
          </span>
          <IconChevronUp className={`h-4 w-4 ml-auto shrink-0 transition-transform ${expanded ? '' : 'rotate-180'}`} aria-hidden />
        </div>
        <div className="collapse-content">
          {swatches}
        </div>
      </div>
    </div>
  );
}
