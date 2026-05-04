import React from 'react';

/**
 * Display-only star rating using daisyUI rating classes.
 * @param {object} props
 * @param {number|null} props.value - Rating value (e.g. 4.2); rendered as filled stars up to rounded value.
 * @param {number} [props.max=5]
 * @param {'xs'|'sm'|'md'|'lg'} [props.size='sm']
 * @param {string} [props.className]
 */
export default function StarRating({ value, max = 5, size = 'sm', className = '' }) {
  const filled = value != null ? Math.round(Number(value)) : 0;

  return (
    <div
      className={`rating rating-${size} ${className}`}
      role="img"
      aria-label={value != null ? `${value} de ${max} estrelles` : 'Sense valoració'}
    >
      {Array.from({ length: max }, (_, i) => (
        <span
          key={i}
          className={`mask mask-star-2 ${i < filled ? 'bg-warning' : 'bg-base-300'}`}
          aria-hidden="true"
        />
      ))}
    </div>
  );
}
