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
  const [cart, setCart] = useState({
    lines: [],
    total: 0,
    installation_requested: false,
    shipping_flat_eur: 9,
    total_with_shipping: 9,
  });
  const [loading, setLoading] = useState(false);
  const [showAddedFeedback, setShowAddedFeedback] = useState(false);
  const addedFeedbackTimeoutRef = useRef(null);

  const fetchCart = useCallback(async (signal) => {
    setLoading(true);
    try {
      const { data } = await api.get('cart', signal ? { signal } : {});
      if (data.success && data.data) {
        setCart({
          id: data.data.id,
          lines: data.data.lines || [],
          total: data.data.total ?? 0,
          installation_requested: !!data.data.installation_requested,
          shipping_flat_eur: data.data.shipping_flat_eur ?? 9,
          total_with_shipping: data.data.total_with_shipping ?? (Number(data.data.total ?? 0) + 9),
        });
      }
    } catch (err) {
      if (err.name !== 'AbortError') {
        setCart({
          lines: [],
          total: 0,
          installation_requested: false,
          shipping_flat_eur: 9,
          total_with_shipping: 9,
        });
      }
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

  const setInstallationRequested = useCallback(async (requested) => {
    const { data } = await api.put('cart/installation', { installation_requested: requested });
    if (data.success && data.data) {
      setCart({
        id: data.data.id,
        lines: data.data.lines || [],
        total: data.data.total ?? 0,
        installation_requested: !!data.data.installation_requested,
        shipping_flat_eur: data.data.shipping_flat_eur ?? 9,
        total_with_shipping: data.data.total_with_shipping ?? (Number(data.data.total ?? 0) + 9),
      });
      return { success: true };
    }
    return { success: false };
  }, []);

  const addLine = useCallback(async (productId, packId, quantity = 1) => {
    const payload = productId ? { product_id: productId, quantity } : { pack_id: packId, quantity };
    const { data } = await api.post('cart/lines', payload);
    if (data.success && data.data) {
      if (Array.isArray(data.data.lines)) {
        setCart((c) => ({
          id: data.data.id ?? c.id,
          lines: data.data.lines,
          total: data.data.total ?? 0,
          installation_requested: data.data.installation_requested ?? c.installation_requested,
          shipping_flat_eur: data.data.shipping_flat_eur ?? c.shipping_flat_eur,
          total_with_shipping: data.data.total_with_shipping ?? (Number(data.data.total ?? 0) + (data.data.shipping_flat_eur ?? c.shipping_flat_eur)),
        }));
      } else await fetchCart();
      if (addedFeedbackTimeoutRef.current) clearTimeout(addedFeedbackTimeoutRef.current);
      setShowAddedFeedback(true);
      addedFeedbackTimeoutRef.current = setTimeout(() => {
        setShowAddedFeedback(false);
        addedFeedbackTimeoutRef.current = null;
      }, ADDED_FEEDBACK_DURATION_MS);
      return { success: true };
    }
    return { success: false };
  }, [fetchCart]);

  const updateLine = useCallback(async (lineId, payload) => {
    const body = typeof payload === 'number' ? { quantity: payload } : { quantity: payload.quantity };
    if (typeof payload !== 'number') {
      if (payload.included !== undefined) body.included = payload.included;
      if (payload.extra_keys_qty !== undefined) body.extra_keys_qty = payload.extra_keys_qty;
      if (payload.keys_all_same !== undefined) body.keys_all_same = payload.keys_all_same;
    }
    const { data } = await api.put(`cart/lines/${lineId}`, body);
    if (data.success && data.data) {
      setCart((c) => ({
        ...c,
        lines: data.data.lines ?? c.lines,
        total: data.data.total ?? c.total,
        installation_requested: data.data.installation_requested ?? c.installation_requested,
        shipping_flat_eur: data.data.shipping_flat_eur ?? c.shipping_flat_eur,
        total_with_shipping: data.data.total_with_shipping
          ?? (Number(data.data.total ?? c.total) + (data.data.shipping_flat_eur ?? c.shipping_flat_eur)),
      }));
      if (!Array.isArray(data.data.lines)) await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [fetchCart]);

  const removeLine = useCallback(async (lineId) => {
    const { data } = await api.delete(`cart/lines/${lineId}`);
    if (data.success && data.data) {
      setCart((c) => ({
        ...c,
        lines: data.data.lines ?? c.lines,
        total: data.data.total ?? c.total,
        installation_requested: data.data.installation_requested ?? c.installation_requested,
        shipping_flat_eur: data.data.shipping_flat_eur ?? c.shipping_flat_eur,
        total_with_shipping: data.data.total_with_shipping
          ?? (Number(data.data.total ?? c.total) + (data.data.shipping_flat_eur ?? c.shipping_flat_eur)),
      }));
      if (!Array.isArray(data.data.lines)) await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [fetchCart]);

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
    setInstallationRequested,
  };
  return <CartContext.Provider value={value}>{children}</CartContext.Provider>;
}

export function useCart() {
  const ctx = useContext(CartContext);
  if (!ctx) throw new Error('useCart must be used within CartProvider');
  return ctx;
}
