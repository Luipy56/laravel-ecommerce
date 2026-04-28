import React, { useEffect, useRef } from 'react';
import { useTranslation } from 'react-i18next';

/**
 * Reusable confirmation modal. Use for submit confirmations (e.g. CustomSolution, Checkout)
 * and destructive actions (e.g. delete address, delete contact).
 *
 * @param {boolean} open - Whether the modal is visible
 * @param {function} onClose - Called when modal is closed (cancel, backdrop, Escape)
 * @param {function} onConfirm - Called when user clicks confirm
 * @param {string} title - Modal title
 * @param {string} message - Body text
 * @param {string} [confirmLabel] - Confirm button label (default: common.confirm)
 * @param {boolean} [loading] - Disables confirm button and shows loading state
 * @param {string} [confirmVariant] - 'primary' | 'error' for confirm button (default: primary)
 * @param {string} [id] - Optional id for aria-labelledby
 */
export default function ConfirmModal({
  open,
  onClose,
  onConfirm,
  title,
  message,
  confirmLabel,
  loading = false,
  confirmVariant = 'primary',
  id,
}) {
  const { t } = useTranslation();
  const ref = useRef(null);
  /** Stops a second "Confirm" click before the parent re-renders (avoids double POST, etc.) */
  const confirmSentRef = useRef(false);
  const prevLoadingRef = useRef(loading);

  useEffect(() => {
    if (open) {
      confirmSentRef.current = false;
    }
  }, [open]);

  useEffect(() => {
    if (prevLoadingRef.current && !loading) {
      confirmSentRef.current = false;
    }
    prevLoadingRef.current = loading;
  }, [loading]);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    if (open) el.showModal();
    else el.close();
  }, [open]);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const handleClose = () => onClose?.();
    el.addEventListener('close', handleClose);
    return () => el.removeEventListener('close', handleClose);
  }, [onClose]);

  const titleId = id ?? 'confirm-modal-title';

  return (
    <dialog ref={ref} className="modal" aria-labelledby={titleId}>
      <div className="modal-box">
        <h3 id={titleId} className="font-bold text-lg">
          {title}
        </h3>
        <p className="py-2">{message}</p>
        <div className="modal-action">
          <button type="button" className="btn btn-ghost" onClick={onClose}>
            {t('common.cancel')}
          </button>
          <button
            type="button"
            className={`btn ${confirmVariant === 'error' ? 'btn-error' : 'btn-primary'}`}
            disabled={loading}
            onClick={() => {
              if (loading || confirmSentRef.current) {
                return;
              }
              confirmSentRef.current = true;
              onConfirm?.();
            }}
          >
            {loading ? t('common.loading') : (confirmLabel ?? t('common.confirm'))}
          </button>
        </div>
      </div>
      <form method="dialog" className="modal-backdrop">
        <button type="submit">{t('common.close')}</button>
      </form>
    </dialog>
  );
}
