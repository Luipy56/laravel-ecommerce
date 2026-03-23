import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { useTranslation } from 'react-i18next';

const ToastContext = createContext(null);

let idSeq = 0;

/**
 * Global daisyUI toasts (bottom-end). Use showToast({ message, type }) from any component under ToastProvider.
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

function ToastViewport({ items, onDismiss }) {
  const { t } = useTranslation();
  if (items.length === 0) return null;

  return (
    <div className="toast toast-end toast-bottom z-[100]">
      {items.map((item) => {
        const color =
          item.type === 'success'
            ? 'alert-success'
            : item.type === 'error'
              ? 'alert-error'
              : item.type === 'warning'
                ? 'alert-warning'
                : 'alert-info';
        return (
          <div key={item.id} role="alert" className={`alert ${color} shadow-lg max-w-sm animate-slide-in-toast`}>
            <span className="text-sm">{item.message}</span>
            <button
              type="button"
              className="btn btn-ghost btn-xs shrink-0"
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
