import React, { createContext, useCallback, useContext, useMemo } from 'react';
import { useToast } from './ToastContext';

const AdminToastContext = createContext(null);

/**
 * Admin success helper: delegates to the global ToastProvider (single toast stack).
 */
export function AdminToastProvider({ children }) {
  const { showToast } = useToast();

  const showSuccess = useCallback(
    (msg) => {
      showToast({ message: String(msg), type: 'success' });
    },
    [showToast]
  );

  const value = useMemo(() => ({ showSuccess }), [showSuccess]);

  return <AdminToastContext.Provider value={value}>{children}</AdminToastContext.Provider>;
}

export function useAdminToast() {
  const ctx = useContext(AdminToastContext);
  if (!ctx) return { showSuccess: () => {} };
  return ctx;
}
