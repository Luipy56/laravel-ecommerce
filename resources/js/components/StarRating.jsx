import React from 'react';

/**
 * Display-only star rating using daisyUI rating classes.
 * @param {object} props
 * @param {number|null} props.value - Rating value (e.g. 4.2); rendered as filled stars up to rounded value.
 * @param {number} [props.max=5]
 * @param {'xs'|'sm'|'md'|'lg'} [props.size='sm']
 * @param {string} [props.className]
 */
const SIZE_CLASS = {
  xs: 'text-sm gap-px',
  sm: 'text-base gap-0.5',
  md: 'text-xl gap-0.5',
  lg: 'text-2xl gap-1',
};

export default function StarRating({ value, max = 5, size = 'sm', className = '' }) {
  const filled = value != null ? Math.round(Number(value)) : 0;
  const sizeClass = SIZE_CLASS[size] ?? SIZE_CLASS.sm;

  return (
    <span
      className={`inline-flex items-center ${sizeClass} ${className}`}
      role="img"
      aria-label={value != null ? `${value} de ${max} estrelles` : 'Sense valoració'}
    >
      {Array.from({ length: max }, (_, i) => (
        <span
          key={i}
          className={i < filled ? 'text-warning' : 'text-base-300'}
          aria-hidden="true"
        >
          ★
        </span>
      ))}
    </span>
  );
}
