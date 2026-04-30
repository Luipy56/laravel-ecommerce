import React, { useId } from 'react';

/**
 * Single collapsible block (daisyUI collapse with checkbox).
 * Do not use collapse-open here: in daisyUI 5 it forces the panel open and prevents closing.
 */
export default function AdminSettingsCollapseSection({ title, subtitle, defaultOpen = false, children }) {
  const inputId = `admin-settings-collapse-${useId().replace(/:/g, '')}`;
  const ariaLabel = subtitle ? `${title}. ${subtitle}` : String(title);

  return (
    <div className="collapse collapse-arrow border border-base-200 bg-base-100 rounded-box shadow min-w-0">
      <input id={inputId} type="checkbox" defaultChecked={defaultOpen} aria-label={ariaLabel} />
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
