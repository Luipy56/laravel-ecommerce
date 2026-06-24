import React, { useEffect, useId, useRef, useState } from 'react';
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
 * Compact key color picker for cart rows: swatch trigger + dropdown option list.
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
  const rootRef = useRef(null);
  const triggerRef = useRef(null);
  const menuRef = useRef(null);
  const [open, setOpen] = useState(false);
  const instanceId = useId().replace(/:/g, '');

  if (!colors.length) return null;

  const selectedId = value == null ? '' : String(value);
  const selectedColor = colors.find((color) => String(color.id) === selectedId);
  const selectedLabel = selectedColor ? colorLabel(selectedColor) : t('shop.product.key_color_none');

  const closeMenu = () => setOpen(false);

  const handleSelect = (nextValue) => {
    onChange(nextValue);
    closeMenu();
  };

  /** daisyUI 5: blur focus when forcing dropdown closed. */
  useEffect(() => {
    if (open) return;
    const root = rootRef.current;
    const active = document.activeElement;
    if (root && active instanceof HTMLElement && root.contains(active)) {
      active.blur();
    } else {
      triggerRef.current?.blur();
    }
  }, [open]);

  useEffect(() => {
    if (!open) return;
    const onDocMouseDown = (event) => {
      if (rootRef.current && !rootRef.current.contains(event.target)) {
        closeMenu();
      }
    };
    const onKeyDown = (event) => {
      if (event.key === 'Escape') closeMenu();
    };
    document.addEventListener('mousedown', onDocMouseDown);
    document.addEventListener('keydown', onKeyDown);
    return () => {
      document.removeEventListener('mousedown', onDocMouseDown);
      document.removeEventListener('keydown', onKeyDown);
    };
  }, [open]);

  useEffect(() => {
    if (!open || !menuRef.current || !triggerRef.current) return;

    const menu = menuRef.current;
    const trigger = triggerRef.current;

    const positionMenu = () => {
      const rect = trigger.getBoundingClientRect();
      const menuWidth = menu.offsetWidth;
      const menuHeight = menu.offsetHeight;
      const margin = 8;
      const viewportPadding = 12;

      let top = rect.bottom + margin;
      let left = rect.left + rect.width / 2 - menuWidth / 2;

      if (top + menuHeight > window.innerHeight - viewportPadding) {
        top = rect.top - menuHeight - margin;
      }
      if (left + menuWidth > window.innerWidth - viewportPadding) {
        left = window.innerWidth - menuWidth - viewportPadding;
      }
      if (left < viewportPadding) {
        left = viewportPadding;
      }

      menu.style.top = `${top}px`;
      menu.style.left = `${left}px`;
    };

    positionMenu();
    window.addEventListener('resize', positionMenu);
    window.addEventListener('scroll', positionMenu, true);
    return () => {
      window.removeEventListener('resize', positionMenu);
      window.removeEventListener('scroll', positionMenu, true);
    };
  }, [open]);

  return (
    <div
      ref={rootRef}
      className={`dropdown inline-flex ${open ? 'dropdown-open' : 'dropdown-close'} ${className}`.trim()}
    >
      <button
        ref={triggerRef}
        type="button"
        id={`key-color-trigger-${instanceId}`}
        className="btn btn-outline btn-sm border-base-300 h-8 min-h-8 gap-1 px-2 py-1 font-normal"
        aria-label={`${t(labelKey)}: ${selectedLabel}`}
        aria-haspopup="listbox"
        aria-expanded={open}
        aria-controls={`key-color-menu-${instanceId}`}
        onClick={() => setOpen((isOpen) => !isOpen)}
      >
        <ColorSwatch none={!selectedColor} color={selectedColor} size="sm" />
        <IconChevronDown className="h-3.5 w-3.5 shrink-0 opacity-70" aria-hidden />
      </button>
      {open ? (
        <ul
          ref={menuRef}
          id={`key-color-menu-${instanceId}`}
          className="menu fixed z-[70] m-0 w-[min(12rem,calc(100vw-1.5rem))] list-none rounded-box border border-base-300 bg-base-100 p-1 shadow-lg"
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
      ) : null}
      <input type="hidden" name={name} value={selectedId} />
    </div>
  );
}
