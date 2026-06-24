import React, { useId, useRef } from 'react';
import { useTranslation } from 'react-i18next';
import { IconChevronDown } from './icons';

function ColorSwatch({ color, size = 'md', none = false }) {
  const sizeClass = size === 'sm' ? 'h-5 w-5' : 'h-8 w-8';
  const textClass = size === 'sm' ? 'text-[10px]' : 'text-sm';

  if (none) {
    return (
      <span
        className={`${sizeClass} flex shrink-0 items-center justify-center rounded-full border-2 border-base-300 bg-base-100 font-semibold text-base-content/70 ${textClass}`}
        aria-hidden
      >
        ?
      </span>
    );
  }

  return (
    <span
      className={`${sizeClass} shrink-0 rounded-full border-2 border-base-300`}
      style={{ backgroundColor: color?.rgb_code }}
      aria-hidden
    />
  );
}

function colorLabel(color) {
  return color?.name || color?.rgb_code || '';
}

/**
 * Compact key color picker for cart rows: swatch trigger + popover option list.
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
  const popoverRef = useRef(null);
  const popoverId = useId().replace(/:/g, '');

  if (!colors.length) return null;

  const selectedId = value == null ? '' : String(value);
  const selectedColor = colors.find((color) => String(color.id) === selectedId);
  const selectedLabel = selectedColor ? colorLabel(selectedColor) : t('shop.product.key_color_none');

  const closePopover = () => {
    popoverRef.current?.hidePopover?.();
  };

  const handleSelect = (nextValue) => {
    onChange(nextValue);
    closePopover();
  };

  return (
    <div className={`inline-flex max-w-[9rem] ${className}`.trim()}>
      <button
        type="button"
        popoverTarget={popoverId}
        className="btn btn-outline btn-sm border-base-300 h-auto min-h-8 w-full max-w-[9rem] justify-start gap-1.5 px-2 py-1 font-normal"
        aria-label={`${t(labelKey)}: ${selectedLabel}`}
        aria-haspopup="listbox"
      >
        <ColorSwatch none={!selectedColor} color={selectedColor} />
        <span className="min-w-0 flex-1 truncate text-left text-sm">{selectedLabel}</span>
        <IconChevronDown className="h-4 w-4 shrink-0 opacity-70" aria-hidden />
      </button>
      <ul
        ref={popoverRef}
        popover="auto"
        id={popoverId}
        className="menu z-[70] m-0 w-[min(12rem,calc(100vw-1.5rem))] list-none rounded-box border border-base-300 bg-base-100 p-1 shadow-lg"
        role="listbox"
        aria-label={t(labelKey)}
      >
        <li role="none">
          <button
            type="button"
            role="option"
            aria-selected={selectedId === ''}
            className={`flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-base-200 ${selectedId === '' ? 'bg-base-200 font-medium' : ''}`}
            onClick={() => handleSelect(null)}
          >
            <ColorSwatch none size="sm" />
            <span className="truncate">{t('shop.product.key_color_none')}</span>
          </button>
        </li>
        {colors.map((color) => {
          const id = String(color.id);
          const label = colorLabel(color);
          const isSelected = selectedId === id;
          return (
            <li key={color.id} role="none">
              <button
                type="button"
                role="option"
                aria-selected={isSelected}
                className={`flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-base-200 ${isSelected ? 'bg-base-200 font-medium' : ''}`}
                onClick={() => handleSelect(color.id)}
                title={label}
              >
                <ColorSwatch color={color} size="sm" />
                <span className="truncate">{label}</span>
              </button>
            </li>
          );
        })}
      </ul>
      <input type="hidden" name={name} value={selectedId} />
    </div>
  );
}
