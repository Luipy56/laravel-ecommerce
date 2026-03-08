import React, { createContext, useCallback, useContext, useEffect, useRef, useState } from 'react';
import { api } from '../api';
import { useAuth } from './AuthContext';

/**
 * Cart state and actions. Guest cart is in session; logged-in cart is in DB.
 * After login, front-end should call mergeCart() so session lines are merged into DB.
 */
const CartContext = createContext(null);

const ADDED_FEEDBACK_DURATION_MS = 2500;

export function CartProvider({ children }) {
  const { user } = useAuth();
  const [cart, setCart] = useState({ lines: [], total: 0 });
  const [loading, setLoading] = useState(false);
  const [showAddedFeedback, setShowAddedFeedback] = useState(false);
  const addedFeedbackTimeoutRef = useRef(null);

  const fetchCart = useCallback(async (signal) => {
    setLoading(true);
    try {
      const { data } = await api.get('cart', signal ? { signal } : {});
      if (data.success && data.data) {
        setCart({ id: data.data.id, lines: data.data.lines || [], total: data.data.total ?? 0 });
      }
    } catch (err) {
      if (err.name !== 'AbortError') setCart({ lines: [], total: 0 });
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    const ac = new AbortController();
    fetchCart(ac.signal);
    return () => ac.abort();
  }, [fetchCart, user?.id]);

  const mergeCart = useCallback(async () => {
    try {
      await api.post('cart/merge');
      await fetchCart();
    } catch {
      await fetchCart();
    }
  }, [fetchCart]);

  const addLine = useCallback(async (productId, packId, quantity = 1) => {
    const payload = productId ? { product_id: productId, quantity } : { pack_id: packId, quantity };
    const { data } = await api.post('cart/lines', payload);
    if (data.success && data.data) {
      setCart({
        id: data.data.id ?? cart.id,
        lines: data.data.lines ?? data.data.line ? [...cart.lines, data.data.line] : cart.lines,
        total: data.data.total ?? cart.total,
      });
      if (Array.isArray(data.data.lines)) setCart((c) => ({ ...c, lines: data.data.lines, total: data.data.total }));
      else await fetchCart();
      if (addedFeedbackTimeoutRef.current) clearTimeout(addedFeedbackTimeoutRef.current);
      setShowAddedFeedback(true);
      addedFeedbackTimeoutRef.current = setTimeout(() => {
        setShowAddedFeedback(false);
        addedFeedbackTimeoutRef.current = null;
      }, ADDED_FEEDBACK_DURATION_MS);
      return { success: true };
    }
    return { success: false };
  }, [cart.id, cart.lines, cart.total, fetchCart]);

  const updateLine = useCallback(async (lineId, payload) => {
    const body = typeof payload === 'number' ? { quantity: payload } : { quantity: payload.quantity };
    if (typeof payload !== 'number') {
      if (payload.included !== undefined) body.included = payload.included;
      if (payload.is_installation_requested !== undefined) body.is_installation_requested = payload.is_installation_requested;
      if (payload.extra_keys_qty !== undefined) body.extra_keys_qty = payload.extra_keys_qty;
      if (payload.keys_all_same !== undefined) body.keys_all_same = payload.keys_all_same;
    }
    const { data } = await api.put(`cart/lines/${lineId}`, body);
    if (data.success && data.data) {
      if (Array.isArray(data.data.lines)) setCart({ ...cart, lines: data.data.lines, total: data.data.total });
      else await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [cart, fetchCart]);

  const removeLine = useCallback(async (lineId) => {
    const { data } = await api.delete(`cart/lines/${lineId}`);
    if (data.success && data.data) {
      if (Array.isArray(data.data.lines)) setCart({ ...cart, lines: data.data.lines, total: data.data.total });
      else await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [cart, fetchCart]);

  const itemCount = cart.lines.reduce((acc, l) => acc + (l.quantity || 0), 0);

  useEffect(() => {
    return () => {
      if (addedFeedbackTimeoutRef.current) clearTimeout(addedFeedbackTimeoutRef.current);
    };
  }, []);

  const value = {
    cart,
    itemCount,
    loading,
    showAddedFeedback,
    fetchCart,
    mergeCart,
    addLine,
    updateLine,
    removeLine,
  };
  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within CartProvider');
  return ctx;
}
