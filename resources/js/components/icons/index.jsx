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
        d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1 -18 0 9 9 0 0 1 18 0zm-9 5.25h.008v.008H12v-.008z"
      />
    </IconWrapper>
  );
}

/** User / profile (silhouette in circle). */
export function IconUser({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M15.75 6a3.75 3.75 0 1 1 -7.5 0a3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632z"
      />
    </IconWrapper>
  );
}

/** Orders / clipboard list. */
export function IconClipboardList({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M9 5H7.25A2.25 2.25 0 005 7.25v12.5A2.25 2.25 0 007.25 22h9.5A2.25 2.25 0 0019 19.75V7.25A2.25 2.25 0 0016.75 5H15m-6 0V3.75A1.75 1.75 0 0110.75 2h2.5A1.75 1.75 0 0115 3.75V5M9 10h6M9 14h6M9 18h4"
      />
    </IconWrapper>
  );
}

/** Purchases / package. */
export function IconPackage({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M21 16.5V8.25a2.25 2.25 0 00-1.2-1.985l-7.5-4.125a2.25 2.25 0 00-2.1 0l-7.5 4.125A2.25 2.25 0 003 8.25v8.25a2.25 2.25 0 001.2 1.985l7.5 4.125a2.25 2.25 0 002.1 0l7.5-4.125A2.25 2.25 0 0021 16.5zM12 12.75L3.75 8.25 12 3.75l8.25 4.5L12 12.75zM12 12.75l8.25-4.5M12 12.75v8.25"
      />
    </IconWrapper>
  );
}

/** Heart (favorites / wishlist). Outline or filled. */
export function IconHeart({ className = '', filled = false, strokeWidth = 2, ...rest }) {
  if (filled) {
    return (
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox={viewBox}
        fill="currentColor"
        className={className}
        aria-hidden="true"
        {...rest}
      >
        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2 12.39 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.89-2.688 6.86-5.55 9.09-.12.08-.25.16-.383.218l-.022.012-.007.003-.002.001L12 21.173l-.645-.263z" />
      </svg>
    );
  }
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"
      />
    </IconWrapper>
  );
}

/** Log in (arrow into bracket). */
export function IconLogIn({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3"
      />
    </IconWrapper>
  );
}

/** Gamepad / game controller. */
export function IconGamepad({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M6 12h4m-2-2v4M15 13h.01M18 11h.01M4.929 4.929A10 10 0 1019.07 19.07 10 10 0 004.93 4.93zM8 8h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4a2 2 0 012-2z"
      />
    </IconWrapper>
  );
}

/** Log out (door + arrow). */
export function IconLogOut({ className, strokeWidth, ...rest }) {
  return (
    <IconWrapper className={className} strokeWidth={strokeWidth} {...rest}>
      <path
        strokeWidth={strokeWidth ?? 2}
        d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"
      />
    </IconWrapper>
  );
}
