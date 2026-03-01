import React from 'react';

/**
 * Consistent page title for all main content pages.
 * Use for the main h1 of the page (e.g. Cart, Orders, Checkout).
 * Same style across the app: bold, tracking-tight, with a bottom border.
 */
export default function PageTitle({ children, className = '' }) {
  return (
    <h1
      className={`text-2xl font-bold tracking-tight text-base-content pb-2 border-b border-base-300 mb-6 ${className}`.trim()}
    >
      {children}
    </h1>
  );
}
