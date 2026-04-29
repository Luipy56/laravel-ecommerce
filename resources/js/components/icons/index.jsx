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

/** Close / dismiss (X). */
export function IconX({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path strokeWidth={strokeWidth ?? 2} d="M18 6L6 18M6 6l12 12" />
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

/** Home (house). */
export function IconHome({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z M9 22V12h6v10"
      />
    </IconWrapper>
  );
}

/** Product grid / catalog. */
export function IconGrid({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"
      />
    </IconWrapper>
  );
}

/** Custom / tailored solution (sparkles). */
export function IconSparkles({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 00-2.456 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z"
      />
    </IconWrapper>
  );
}

/** FAQ / help (circle + question mark). */
export function IconHelpCircle({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z"
      />
    </IconWrapper>
  );
}
