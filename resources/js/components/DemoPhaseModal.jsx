import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

const STORAGE_KEY = 'serra-demo-phase-seen';

function hasSeenDemoModal() {
  try {
    return window.localStorage.getItem(STORAGE_KEY) === '1';
  } catch {
    return false;
  }
}

function markDemoModalSeen() {
  try {
    window.localStorage.setItem(STORAGE_KEY, '1');
  } catch {
    /* ignore */
  }
}

export default function DemoPhaseModal() {
  const { t } = useTranslation();
  const [open, setOpen] = useState(false);

  useEffect(() => {
    if (typeof window === 'undefined') return;
    if (!hasSeenDemoModal()) {
      setOpen(true);
    }
  }, []);

  const handleClose = () => {
    markDemoModalSeen();
    setOpen(false);
  };

  if (!open) {
    return null;
  }

  return (
    <dialog className="modal modal-open" open aria-labelledby="demo-phase-modal-title">
      <div className="modal-box max-w-md">
        <h2 id="demo-phase-modal-title" className="font-bold text-lg">
          {t('demo.modal.title')}
        </h2>
        <p className="py-4 text-sm text-base-content/80">{t('demo.modal.body')}</p>
        <div className="modal-action">
          <button type="button" className="btn btn-primary" onClick={handleClose}>
            {t('demo.modal.understand')}
          </button>
        </div>
      </div>
      <form method="dialog" className="modal-backdrop">
        <button type="button" aria-label={t('common.close')} onClick={handleClose} />
      </form>
    </dialog>
  );
}
