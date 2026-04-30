import React from 'react';

/**
 * Single collapsible block (daisyUI collapse). Multiple sections can stay open.
 */
export default function AdminSettingsCollapseSection({ title, subtitle, defaultOpen = false, children }) {
  return (
    <div
      tabIndex={0}
      className={`collapse collapse-arrow border border-base-200 bg-base-100 rounded-box shadow min-w-0${defaultOpen ? ' collapse-open' : ''}`}
    >
      <div className="collapse-title text-base sm:text-lg font-semibold min-w-0 pr-8">
        <span className="block">{title}</span>
        {subtitle ? <span className="block text-sm font-normal text-base-content/70 mt-0.5">{subtitle}</span> : null}
      </div>
      <div className="collapse-content text-left min-w-0">
        <div className="pt-2 border-t border-base-200 border-opacity-50">{children}</div>
      </div>
    </div>
  );
}
