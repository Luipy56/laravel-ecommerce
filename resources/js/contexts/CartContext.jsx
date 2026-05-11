import React, { createContext, useCallback, useContext, useEffect, useState } from 'react';
import i18n from '../i18n';
import { api } from '../api';
import { emitAppToast } from '../toastEvents';
import { useAuth } from './AuthContext';

/**
 * Cart state and actions. Guest cart is in session; logged-in cart is in DB.
 * After login, front-end should call mergeCart() so session lines are merged into DB.
 */
const CartContext = createContext(null);

/** @param {Record<string, unknown>} data */
function cartStateFromApi(data) {
  const total = Number(data.total ?? 0);
  const ship = Number(data.shipping_flat_eur ?? 9);

  return {
    id: data.id,
    lines: data.lines || [],
    total,
    installation_requested: !!data.installation_requested,
    installation_quote_required: !!data.installation_quote_required,
    installation_fee_eur: data.installation_fee_eur ?? null,
    shipping_flat_eur: ship,
    total_with_shipping: data.total_with_shipping ?? total + ship,
    payments: data.payments || [],
  };
}

export function CartProvider({ children }) {
  const { user } = useAuth();
  const [cart, setCart] = useState(() =>
    cartStateFromApi({
      lines: [],
      total: 0,
      installation_requested: false,
      installation_quote_required: false,
      installation_fee_eur: null,
      shipping_flat_eur: 9,
      total_with_shipping: 9,
      payments: [],
    }),
  );
  const [loading, setLoading] = useState(false);

  const fetchCart = useCallback(async (signal) => {
    setLoading(true);
    try {
      const { data } = await api.get('cart', signal ? { signal } : {});
      if (data.success && data.data) {
        setCart(cartStateFromApi(data.data));
      }
    } catch (err) {
      if (err.name !== 'AbortError') {
        setCart(
          cartStateFromApi({
            lines: [],
            total: 0,
            installation_requested: false,
            installation_quote_required: false,
            installation_fee_eur: null,
            shipping_flat_eur: 9,
            total_with_shipping: 9,
            payments: [],
          }),
        );
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
      setCart(cartStateFromApi(data.data));
      return { success: true };
    }
    return { success: false };
  }, []);

  const addLine = useCallback(async (productId, packId, quantity = 1) => {
    const payload = productId ? { product_id: productId, quantity } : { pack_id: packId, quantity };
    const { data } = await api.post('cart/lines', payload);
    if (data.success && data.data) {
      if (Array.isArray(data.data.lines)) {
        setCart((c) => cartStateFromApi({ ...data.data, id: data.data.id ?? c.id }));
      } else await fetchCart();
      emitAppToast(i18n.t('shop.cart.added'), 'success');
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
      setCart(cartStateFromApi(data.data));
      if (!Array.isArray(data.data.lines)) await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [fetchCart]);

  const removeLine = useCallback(async (lineId) => {
    const { data } = await api.delete(`cart/lines/${lineId}`);
    if (data.success && data.data) {
      setCart(cartStateFromApi(data.data));
      if (!Array.isArray(data.data.lines)) await fetchCart();
      return { success: true };
    }
    return { success: false };
  }, [fetchCart]);

  const itemCount = cart.lines.reduce((acc, l) => acc + (l.quantity || 0), 0);

  const value = {
    cart,
    itemCount,
    loading,
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
