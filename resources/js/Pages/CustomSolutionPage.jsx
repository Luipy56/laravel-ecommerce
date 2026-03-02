import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';

const TOAST_DURATION_MS = 3000;

export default function CustomSolutionPage() {
  const { t } = useTranslation();
  const [form, setForm] = useState({
    email: '',
    phone: '',
    problem_description: '',
    address_street: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
    address_note: '',
    files: [],
  });
  const [loading, setLoading] = useState(false);
  const [showSuccessToast, setShowSuccessToast] = useState(false);
  const [error, setError] = useState('');
  const [confirmModalOpen, setConfirmModalOpen] = useState(false);
  const toastTimeoutRef = useRef(null);
  const confirmModalRef = useRef(null);

  useEffect(() => {
    const el = confirmModalRef.current;
    if (!el) return;
    if (confirmModalOpen) el.showModal();
    else el.close();
  }, [confirmModalOpen]);

  useEffect(() => {
    const el = confirmModalRef.current;
    if (!el) return;
    const onClose = () => setConfirmModalOpen(false);
    el.addEventListener('close', onClose);
    return () => el.removeEventListener('close', onClose);
  }, []);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
  };

  const handleFiles = (e) => {
    setForm((f) => ({ ...f, files: Array.from(e.target.files || []) }));
  };

  const submitForm = useCallback(async () => {
    setConfirmModalOpen(false);
    setError('');
    setLoading(true);
    const formData = new FormData();
    formData.append('email', form.email);
    formData.append('phone', form.phone);
    formData.append('problem_description', form.problem_description);
    formData.append('address_street', form.address_street);
    formData.append('address_city', form.address_city);
    formData.append('address_province', form.address_province);
    formData.append('address_postal_code', form.address_postal_code);
    formData.append('address_note', form.address_note);
    form.files.forEach((file) => formData.append('attachments[]', file));
    try {
      const r = await api.post('personalized-solutions', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      if (r.data.success) {
        if (toastTimeoutRef.current) clearTimeout(toastTimeoutRef.current);
        setShowSuccessToast(true);
        toastTimeoutRef.current = setTimeout(() => {
          setShowSuccessToast(false);
          toastTimeoutRef.current = null;
        }, TOAST_DURATION_MS);
        setForm({
          email: '',
          phone: '',
          problem_description: '',
          address_street: '',
          address_city: '',
          address_province: '',
          address_postal_code: '',
          address_note: '',
          files: [],
        });
      }
    } catch (err) {
      setError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  }, [form, t]);

  const handleSubmit = (e) => {
    e.preventDefault();
    setConfirmModalOpen(true);
  };

  return (
    <div className="max-w-4xl mx-auto">
      <PageTitle>{t('shop.custom_solution')}</PageTitle>
      <p className="text-sm text-base-content/70 mb-4">{t('register.required_note')}</p>
      {error && <div className="alert alert-error mb-4">{error}</div>}
      {showSuccessToast && (
        <div
          className="toast toast-end toast-bottom z-50 p-4"
          role="status"
          aria-live="polite"
          aria-label={t('shop.custom_solution.success')}
        >
          <div className="alert alert-success shadow-lg">
            <span>{t('shop.custom_solution.success')}</span>
          </div>
        </div>
      )}
      <dialog ref={confirmModalRef} className="modal" aria-labelledby="custom-solution-confirm-title">
        <div className="modal-box">
          <h3 id="custom-solution-confirm-title" className="font-bold text-lg">{t('shop.custom_solution.submit')}</h3>
          <p className="py-2">{t('shop.custom_solution.confirm_message')}</p>
          <div className="modal-action">
            <button type="button" className="btn btn-ghost" onClick={() => setConfirmModalOpen(false)}>
              {t('common.cancel')}
            </button>
            <button
              type="button"
              className="btn btn-primary"
              disabled={loading}
              onClick={submitForm}
            >
              {loading ? t('common.loading') : t('common.confirm')}
            </button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop">
          <button type="submit">{t('common.close')}</button>
        </form>
      </dialog>
      <form onSubmit={handleSubmit} className="card bg-base-100 shadow">
        <div className="card-body space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <label className="form-field w-full">
              <span className="form-label">{t('auth.email')} *</span>
              <input type="email" name="email" className="input input-bordered w-full" value={form.email} onChange={handleChange} required />
            </label>
            <label className="form-field w-full">
              <span className="form-label">{t('profile.phone')}</span>
              <input type="tel" name="phone" className="input input-bordered w-full" value={form.phone} onChange={handleChange} />
            </label>
          </div>

          <label className="form-field w-full">
            <span className="form-label">{t('shop.custom_solution.description')} *</span>
            <textarea name="problem_description" className="textarea textarea-bordered w-full" rows={5} value={form.problem_description} onChange={handleChange} required />
          </label>

          <label className="form-field w-full">
            <span className="form-label">{t('shop.custom_solution.attachments')}</span>
            <input type="file" className="file-input file-input-bordered w-full" multiple accept="image/*,.pdf" onChange={handleFiles} />
          </label>

          <fieldset className="form-field space-y-5 border border-base-300 rounded-lg p-4">
            <legend className="form-label px-1">{t('shop.custom_solution.address_optional')}</legend>
            <label className="form-field w-full">
              <span className="form-label">{t('checkout.street')}</span>
              <input name="address_street" className="input input-bordered w-full" value={form.address_street} onChange={handleChange} />
            </label>
            <div className="flex gap-2">
              <label className="form-field flex-1">
                <span className="form-label">{t('profile.city')}</span>
                <input name="address_city" className="input input-bordered w-full" value={form.address_city} onChange={handleChange} />
              </label>
              <label className="form-field w-28">
                <span className="form-label">{t('profile.postal_code')}</span>
                <input name="address_postal_code" className="input input-bordered w-full" value={form.address_postal_code} onChange={handleChange} />
              </label>
            </div>
            <label className="form-field w-full">
              <span className="form-label">{t('profile.province')}</span>
              <input name="address_province" className="input input-bordered w-full" value={form.address_province} onChange={handleChange} />
            </label>
            <label className="form-field w-full">
              <span className="form-label">{t('shop.custom_solution.address_note')}</span>
              <textarea name="address_note" className="textarea textarea-bordered w-full" rows={2} placeholder="" value={form.address_note} onChange={handleChange} />
            </label>
          </fieldset>

          <div className="flex justify-end">
            <button type="submit" className="btn btn-primary md:max-w-xs" disabled={loading}>
              {loading ? t('common.loading') : t('shop.custom_solution.submit')}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
