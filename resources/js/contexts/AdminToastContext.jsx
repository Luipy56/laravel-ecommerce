import React, { createContext, useCallback, useContext, useRef, useState } from 'react';

const TOAST_DURATION_MS = 3000;

const AdminToastContext = createContext(null);

export function AdminToastProvider({ children }) {
  const [message, setMessage] = useState(null);
  const timeoutRef = useRef(null);

  const showSuccess = useCallback((msg) => {
    if (timeoutRef.current) clearTimeout(timeoutRef.current);
    setMessage(msg);
    timeoutRef.current = setTimeout(() => {
      setMessage(null);
      timeoutRef.current = null;
    }, TOAST_DURATION_MS);
  }, []);

  return (
    <AdminToastContext.Provider value={{ showSuccess }}>
      {children}
      {message != null && (
        <div
          className="toast toast-end toast-bottom z-50 p-4"
          role="status"
          aria-live="polite"
          aria-label={message}
        >
          <div className="alert alert-success shadow-lg">
            <span>{message}</span>
          </div>
        </div>
      )}
    </AdminToastContext.Provider>
  );
}

export function useAdminToast() {
  const ctx = useContext(AdminToastContext);
  if (!ctx) return { showSuccess: () => {} };
  return ctx;
}
