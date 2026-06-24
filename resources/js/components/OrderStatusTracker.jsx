import React, { useEffect, useId, useState } from 'react';
import { useTranslation } from 'react-i18next';
import './OrderStatusTracker.scss';

const CLOSED_STATUSES = ['installation_confirmed', 'returned'];

/** Returns true when the order is in a terminal / closed state. */
export function isOrderClosed(status) {
  return CLOSED_STATUSES.includes(status);
}

/**
 * Computes the 0-based index of the current active step.
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
  }
  switch (status) {
    case 'awaiting_payment': return 0;
    case 'pending':          return 1;
    case 'in_transit':       return 2;
    case 'sent':             return 3;
    case 'installation_confirmed': return 4;
    default:                 return 0;
  }
}

function stepState(index, activeIndex) {
  if (index < activeIndex) return 'done';
  if (index === activeIndex) return 'active';
  return 'pending';
}

/**
 * Visual order-status tracker with animated brand-gradient progress rail.
 *
 * Props:
 *   order     — { status, installation_requested, has_payment }
 *   embedded  — omit outer card when parent already provides a container
 */
export default function OrderStatusTracker({ order, embedded = false }) {
  const { t } = useTranslation();
  const labelId = useId();
  const [ready, setReady] = useState(false);

  useEffect(() => {
    const frame = requestAnimationFrame(() => setReady(true));
    return () => cancelAnimationFrame(frame);
  }, [order?.id, order?.status]);

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
  const progressPct = steps.length > 1 ? (activeIndex / (steps.length - 1)) * 100 : 0;

  const tracker = (
    <nav
      className={`order-tracker${ready ? ' order-tracker--ready' : ''}`}
      aria-labelledby={embedded ? undefined : labelId}
      aria-label={embedded ? t('shop.order.tracker.aria') : undefined}
    >
      {!embedded ? (
        <span id={labelId} className="sr-only">
          {t('shop.order.tracker.aria')}
        </span>
      ) : null}

      <div className="order-tracker__rail" aria-hidden="true">
        <div className="order-tracker__rail-track" />
        <div
          className="order-tracker__rail-fill"
          style={{ '--order-tracker-progress': `${progressPct}%` }}
        />
      </div>

      <ol className="order-tracker__steps">
        {steps.map((label, i) => {
          const state = stepState(i, activeIndex);
          return (
            <li
              key={`${i}-${label}`}
              className={`order-tracker__step order-tracker__step--${state}`}
              style={{ '--order-tracker-step-delay': `${0.08 + i * 0.11}s` }}
              aria-current={state === 'active' ? 'step' : undefined}
            >
              <span className="order-tracker__marker">
                {state === 'done' ? (
                  <svg viewBox="0 0 16 16" className="order-tracker__check" aria-hidden="true">
                    <path
                      d="M3 8.5 6.5 12 13 4"
                      fill="none"
                      stroke="currentColor"
                      strokeWidth="2"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                    />
                  </svg>
                ) : (
                  <span className="order-tracker__num">{i + 1}</span>
                )}
              </span>
              <span className="order-tracker__label">{label}</span>
            </li>
          );
        })}
      </ol>
    </nav>
  );

  if (embedded) return tracker;

  return (
    <div className="card bg-base-100 border border-base-200 shadow-sm rounded-2xl overflow-x-auto">
      <div className="card-body py-5 px-4 sm:px-6">{tracker}</div>
    </div>
  );
}
