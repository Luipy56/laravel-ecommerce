import React, { createContext, useContext, useMemo, useState } from 'react';

const StorefrontNavbarVisibilityContext = createContext(null);

export function StorefrontNavbarVisibilityProvider({ children }) {
  const [visible, setVisible] = useState(true);
  const value = useMemo(() => ({ visible, setNavbarVisible: setVisible }), [visible]);
  return (
    <StorefrontNavbarVisibilityContext.Provider value={value}>
      {children}
    </StorefrontNavbarVisibilityContext.Provider>
  );
}

export function useStorefrontNavbarVisibility() {
  const ctx = useContext(StorefrontNavbarVisibilityContext);
  if (ctx == null) {
    throw new Error('useStorefrontNavbarVisibility must be used within StorefrontNavbarVisibilityProvider');
  }
  return ctx;
}
