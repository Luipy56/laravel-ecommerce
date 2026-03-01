import React, { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { api } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadUser = useCallback(async () => {
    try {
      const { data } = await api.get('user');
      if (data.success && data.data) setUser(data.data);
      else setUser(null);
    } catch {
      setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadUser();
  }, [loadUser]);

  const login = useCallback(async (loginEmail, password, remember = false) => {
    const { data } = await api.post('login', { login_email: loginEmail, password, remember });
    if (data.success) {
      setUser(data.data);
      return { success: true };
    }
    return { success: false, message: data.message, errors: data.errors };
  }, []);

  const register = useCallback(async (payload) => {
    const { data } = await api.post('register', payload);
    if (data.success) {
      setUser(data.data);
      return { success: true };
    }
    return { success: false, message: data.message, errors: data.errors };
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('logout');
    } finally {
      setUser(null);
    }
  }, []);

  const value = { user, loading, login, register, logout, refreshUser: loadUser };
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
