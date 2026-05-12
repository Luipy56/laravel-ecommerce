import React from 'react';
import { useTranslation } from 'react-i18next';

const CLOSED_STATUSES = ['installation_confirmed', 'returned'];

/** Returns true when the order is in a terminal / closed state. */
export function isOrderClosed(status) {
  return CLOSED_STATUSES.includes(status);
}

/**
 * Computes the 0-based index of the current active step.
 * Steps 0…currentIndex are rendered with step-primary (orange).
 */
function currentStepIndex(status, withInstall) {
  if (withInstall) {
    switch (status) {
      case 'awaiting_installation_price': return 0;
      case 'awaiting_payment':            return 0;
      case 'pending':                     return 1;
      case 'in_transit':                  return 2;
      case 'sent':                        return 2;
      case 'installation_pending':        return 3;
      case 'installation_confirmed':      return 4;
      default:                            return 0;
    }
  } else {
    switch (status) {
      case 'awaiting_payment': return 0;
      case 'pending':          return 1;
      case 'in_transit':       return 2;
      case 'sent':             return 3;
      case 'installation_confirmed': return 4;
      default:                 return 0;
    }
  }
}

/**
 * Visual order-status tracker.
 *
 * Props:
 *   order  — object with { status, installation_requested, has_payment }
 *
 * Renders nothing if order is in a closed state.
 */
export default function OrderStatusTracker({ order }) {
  const { t } = useTranslation();

  if (!order || isOrderClosed(order.status)) return null;

  const withInstall = Boolean(order.installation_requested);

  const installStepLabel = order.status === 'installation_confirmed'
    ? t('shop.order.tracker.step_installation')
    : t('shop.order.tracker.step_installing');

  const steps = withInstall
    ? [
        t('shop.order.tracker.step_pending'),
        t('shop.order.tracker.step_received'),
        t('shop.order.tracker.step_transit'),
        installStepLabel,
        t('shop.order.tracker.step_done'),
      ]
    : [
        t('shop.order.tracker.step_received'),
        t('shop.order.tracker.step_payment'),
        t('shop.order.tracker.step_transit'),
        t('shop.order.tracker.step_sent'),
        t('shop.order.tracker.step_done'),
      ];

  const activeIndex = currentStepIndex(order.status, withInstall);

  return (
    <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl overflow-x-auto">
      <div className="card-body py-4 px-4 sm:px-6">
        <ul className="steps steps-vertical sm:steps-horizontal w-full min-w-0">
          {steps.map((label, i) => (
            <li
              key={label}
              className={`step text-xs sm:text-sm${i <= activeIndex ? ' step-primary' : ''}`}
            >
              {label}
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}
