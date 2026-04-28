import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

const ToastContext = createContext(null);

let idSeq = 0;

/**
 * Global daisyUI toast stack (top-end, below main nav). Use showToast({ message, type }) or emitAppToast from non-React code.
 * Types: success (orange), error, warning, info.
 */
export function ToastProvider({ children }) {
  const [items, setItems] = useState([]);

  const dismiss = useCallback((id) => {
    setItems((prev) => prev.filter((t) => t.id !== id));
  }, []);

  const showToast = useCallback(
    ({ message, type = 'info' }) => {
      const id = ++idSeq;
      setItems((prev) => [...prev, { id, message: String(message), type }]);
      window.setTimeout(() => dismiss(id), 6000);
    },
    [dismiss]
  );

  useEffect(() => {
    const onAppToast = (e) => {
      const d = e.detail;
      if (d?.message) showToast({ message: d.message, type: d.type ?? 'info' });
    };
    window.addEventListener('app:toast', onAppToast);
    return () => window.removeEventListener('app:toast', onAppToast);
  }, [showToast]);

  const value = useMemo(() => ({ showToast, dismiss }), [showToast, dismiss]);

  return (
    <ToastContext.Provider value={value}>
      {children}
      <ToastViewport items={items} onDismiss={dismiss} />
    </ToastContext.Provider>
  );
}

function toastPlacementClass(pathname) {
  if (pathname === '/admin/login') {
    return 'toast toast-end toast-top z-[100] fixed right-4 top-4 max-w-[100vw] sm:right-6';
  }
  const isAdminShell = pathname.startsWith('/admin');
  if (isAdminShell) {
    return 'toast toast-end toast-top z-[100] fixed right-4 top-[calc(3.75rem+0.25rem)] max-w-[100vw] sm:right-6';
  }
  return 'toast toast-end toast-top z-[100] fixed right-4 top-[calc(6rem+0.25rem)] max-w-[100vw] sm:right-6 lg:top-[calc(4rem+0.25rem)]';
}

function ToastViewport({ items, onDismiss }) {
  const { pathname } = useLocation();
  const { t } = useTranslation();
  if (items.length === 0) return null;

  const wrapClass = toastPlacementClass(pathname);

  return (
    <div className={wrapClass}>
      {items.map((item) => {
        const color =
          item.type === 'success'
            ? 'alert-app-success'
            : item.type === 'error'
              ? 'alert-error'
              : item.type === 'warning'
                ? 'alert-warning'
                : 'alert-info';
        return (
          <div key={item.id} role="alert" className={`alert ${color} shadow-lg max-w-sm animate-slide-in-toast`}>
            <span className="text-sm font-bold">{item.message}</span>
            <button
              type="button"
              className="btn btn-ghost btn-xs shrink-0 text-inherit opacity-90 hover:opacity-100"
              onClick={() => onDismiss(item.id)}
              aria-label={t('common.close')}
            >
              ×
            </button>
          </div>
        );
      })}
    </div>
  );
}

export function useToast() {
  const ctx = useContext(ToastContext);
  if (!ctx) {
    throw new Error('useToast must be used within ToastProvider');
  }
  return ctx;
}
