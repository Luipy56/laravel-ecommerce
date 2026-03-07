import React, { useEffect } from 'react';
import { useCart } from '../contexts/CartContext';

export default function CartWidget() {
  const { itemCount } = useCart();

  useEffect(() => {
    const el = document.getElementById('cart-count');
    if (el) el.textContent = itemCount > 99 ? '99+' : String(itemCount);
  }, [itemCount]);

  return null;
}
