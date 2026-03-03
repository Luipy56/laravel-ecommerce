import React from 'react';

const viewBox = '0 0 24 24';

/**
 * Base SVG wrapper: stroke icons, 24x24 viewBox, currentColor.
 * Size via className (e.g. h-4 w-4, h-6 w-6). Use Icon* components from this file instead of inline SVGs.
 */
function IconWrapper({ children, className = '', strokeWidth = 2, ...rest }) {
  return (
    <svg
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox={viewBox}
      stroke="currentColor"
      strokeLinecap="round"
      strokeLinejoin="round"
      className={className}
      {...rest}
    >
      {children}
    </svg>
  );
}

/** Shopping cart / bag icon. */
export function IconCart({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"
      />
    </IconWrapper>
  );
}

/** Chevron pointing up (e.g. scroll to top). */
export function IconChevronUp({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M5 10l7-7m0 0l7 7m-7-7v18" />
    </IconWrapper>
  );
}

/** Chevron pointing down (e.g. expandable section when open). */
export function IconChevronDown({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M19 14l-7 7m0 0l-7-7m7 7V3" />
    </IconWrapper>
  );
}

/** Chevron left (e.g. gallery prev). */
export function IconChevronLeft({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M15 19l-7-7 7-7" />
    </IconWrapper>
  );
}

/** Chevron right (e.g. gallery next). */
export function IconChevronRight({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M9 5l7 7-7 7" />
    </IconWrapper>
  );
}

/** Hamburger menu (three horizontal lines). */
export function IconMenu({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M4 6h16M4 12h16M4 18h16" />
    </IconWrapper>
  );
}
