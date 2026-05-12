import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { useAdminToast } from '../../contexts/AdminToastContext';

export default function SendEmailModal({ recipientEmail, defaultSubject, isOpen, onClose }) {
  const { t } = useTranslation();
  const { showSuccess } = useAdminToast();

  const [subject, setSubject] = useState('');
  const [body, setBody] = useState('');
  const [sending, setSending] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isOpen) {
      setSubject(defaultSubject || '');
      setBody('');
      setError('');
      setSending(false);
    }
  }, [isOpen, defaultSubject]);

  const handleSend = async (e) => {
    e.preventDefault();
    if (!recipientEmail) return;
    setError('');
    setSending(true);
    try {
      const { data } = await api.post('admin/send-email', {
        to: recipientEmail,
        subject: subject.trim(),
        body: body.trim(),
      });
      if (data.success) {
        showSuccess(t('admin.send_email.success'));
        onClose();
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        Object.values(err.response?.data?.errors || {}).flat()[0] ||
        t('common.error');
      setError(msg);
    } finally {
      setSending(false);
    }
  };

  if (!isOpen) return null;

  return (
    <dialog className="modal modal-open" aria-label={t('admin.send_email.modal_title')}>
      <div className="modal-box max-w-lg">
        <h3 className="font-bold text-lg">{t('admin.send_email.modal_title')}</h3>
        <form onSubmit={handleSend} className="space-y-4 mt-4">
          {error && (
            <div role="alert" className="alert alert-error text-sm">{error}</div>
          )}
          <fieldset className="fieldset">
            <legend className="fieldset-legend">{t('admin.send_email.to_label')}</legend>
            <input
              type="email"
              className="input w-full bg-base-200"
              value={recipientEmail || ''}
              readOnly
              tabIndex={-1}
            />
          </fieldset>
          <fieldset className="fieldset">
            <legend className="fieldset-legend">{t('admin.send_email.subject_label')}</legend>
            <input
              type="text"
              className="input w-full"
              value={subject}
              onChange={(e) => setSubject(e.target.value)}
              maxLength={200}
              required
              autoFocus
            />
          </fieldset>
          <fieldset className="fieldset">
            <legend className="fieldset-legend">{t('admin.send_email.body_label')}</legend>
            <textarea
              className="textarea w-full min-h-40 text-base leading-relaxed"
              value={body}
              onChange={(e) => setBody(e.target.value)}
              placeholder={t('admin.send_email.body_placeholder')}
              maxLength={5000}
              required
            />
          </fieldset>
          <div className="modal-action">
            <button
              type="button"
              className="btn btn-ghost"
              onClick={onClose}
              disabled={sending}
            >
              {t('common.cancel')}
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={sending || !recipientEmail}
            >
              {sending ? <span className="loading loading-spinner loading-sm" /> : t('admin.send_email.send')}
            </button>
          </div>
        </form>
      </div>
      <div className="modal-backdrop" onClick={sending ? undefined : onClose} />
    </dialog>
  );
}
