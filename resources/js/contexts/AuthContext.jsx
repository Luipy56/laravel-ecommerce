import React, { createContext, useCallback, useContext, useEffect, useState } from 'react';
import { api } from '../api';

const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);

  const loadUser = useCallback(async (signal) => {
    try {
      const { data } = await api.get('user', signal ? { signal } : {});
      if (data.success && data.data) setUser(data.data);
      else setUser(null);
    } catch (err) {
      if (err.name !== 'AbortError') setUser(null);
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    const ac = new AbortController();
    loadUser(ac.signal);
    return () => ac.abort();
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

  const refreshUser = useCallback(() => loadUser(null), [loadUser]);
  const value = { user, loading, login, register, logout, refreshUser };
  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error('useAuth must be used within AuthProvider');
  return ctx;
}
