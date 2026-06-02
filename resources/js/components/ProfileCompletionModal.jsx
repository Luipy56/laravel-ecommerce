import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default function ProfileCompletionModal({ open, onClose }) {
  const { t } = useTranslation();

  if (!open) {
    return null;
  }

  return (
    <dialog className="modal modal-open" open>
      <div className="modal-box">
        <h2 className="font-bold text-lg">{t('auth.profile_complete_title')}</h2>
        <p className="py-4 text-sm text-base-content/80">{t('auth.profile_complete_body')}</p>
        <div className="modal-action">
          <button type="button" className="btn btn-ghost" onClick={onClose}>
            {t('common.close')}
          </button>
          <Link to="/profile" className="btn btn-primary" onClick={onClose}>
            {t('auth.profile_complete_cta')}
          </Link>
        </div>
      </div>
      <form method="dialog" className="modal-backdrop">
        <button type="button" aria-label={t('common.close')} onClick={onClose} />
      </form>
    </dialog>
  );
}
