import React from 'react';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';

/**
 * Shows a short-lived success toast when an item is added to the cart.
 */
export default function CartAddedToast() {
  const { t } = useTranslation();
  const { showAddedFeedback } = useCart();

  if (!showAddedFeedback) return null;

  return (
    <div
      className="toast toast-end toast-bottom z-50 p-4"
      role="status"
      aria-live="polite"
      aria-label={t('shop.cart.added')}
    >
      <div className="alert alert-success shadow-lg">
        <span>{t('shop.cart.added')}</span>
      </div>
    </div>
  );
}
