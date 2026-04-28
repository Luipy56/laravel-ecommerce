import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';
import { useToast } from '../contexts/ToastContext';
import { coercePostalCodeFieldValue } from '../lib/postalInput';
import { customSolutionFormSchema, parseWithZod } from '../validation';

export default function CustomSolutionPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showToast } = useToast();
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
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [confirmModalOpen, setConfirmModalOpen] = useState(false);
  const [publicSettingsLoaded, setPublicSettingsLoaded] = useState(false);
  const [acceptPersonalizedSolutions, setAcceptPersonalizedSolutions] = useState(true);
  const submitInFlightRef = useRef(false);
  const errorBannerRef = useRef(null);

  /** Defer so the error `<div>` is mounted after setState; user often stays scrolled to the bottom submit. */
  const scrollErrorBannerIntoView = useCallback(() => {
    queueMicrotask(() => {
      requestAnimationFrame(() => {
        errorBannerRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }, []);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const r = await api.get('shop/public-settings');
        if (!cancelled && r.data?.success && r.data?.data) {
          setAcceptPersonalizedSolutions(r.data.data.accept_personalized_solutions !== false);
        }
      } catch {
        if (!cancelled) setAcceptPersonalizedSolutions(true);
      } finally {
        if (!cancelled) setPublicSettingsLoaded(true);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, []);

  const newRequestsDisabled = publicSettingsLoaded && !acceptPersonalizedSolutions;

  const handleChange = (e) => {
    const { name, value } = e.target;
    const next = coercePostalCodeFieldValue(name, value);
    setForm((f) => ({ ...f, [name]: next }));
    if (fieldErrors[name]) {
      setFieldErrors((fe) => {
        const next = { ...fe };
        delete next[name];
        return next;
      });
    }
  };

  const handleFiles = (e) => {
    setForm((f) => ({ ...f, files: Array.from(e.target.files || []) }));
  };

  const submitForm = useCallback(async () => {
    if (newRequestsDisabled) return;
    if (submitInFlightRef.current) {
      return;
    }
    submitInFlightRef.current = true;
    setConfirmModalOpen(false);
    setError('');
    setFieldErrors({});
    const parsed = parseWithZod(customSolutionFormSchema, form, t);
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors);
      setError(parsed.firstError);
      scrollErrorBannerIntoView();
      submitInFlightRef.current = false;
      return;
    }
    setLoading(true);
    const formData = new FormData();
    formData.append('email', parsed.data.email);
    formData.append('phone', parsed.data.phone);
    formData.append('problem_description', parsed.data.problem_description);
    formData.append('address_street', parsed.data.address_street);
    formData.append('address_city', parsed.data.address_city);
    formData.append('address_province', parsed.data.address_province);
    formData.append('address_postal_code', parsed.data.address_postal_code);
    formData.append('address_note', parsed.data.address_note);
    form.files.forEach((file) => formData.append('attachments[]', file));
    try {
      const r = await api.post('personalized-solutions', formData, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
      if (r.data.success) {
        showToast({ message: t('shop.custom_solution.success'), type: 'success' });
        const portalPath = r.data.data?.client_portal_path;
        if (portalPath) {
          navigate(portalPath);
        }
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
      scrollErrorBannerIntoView();
    } finally {
      setLoading(false);
      submitInFlightRef.current = false;
    }
  }, [form, t, showToast, navigate, newRequestsDisabled, scrollErrorBannerIntoView]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (newRequestsDisabled) return;
    setError('');
    setFieldErrors({});
    const parsed = parseWithZod(customSolutionFormSchema, form, t);
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors);
      setError(parsed.firstError);
      scrollErrorBannerIntoView();
      return;
    }
    setConfirmModalOpen(true);
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-4xl">
      <PageTitle>{t('shop.custom_solution')}</PageTitle>
      {error ? (
        <div ref={errorBannerRef} className="alert alert-error mb-4" role="alert">
          {error}
        </div>
      ) : null}
      <ConfirmModal
        open={confirmModalOpen}
        onClose={() => setConfirmModalOpen(false)}
        onConfirm={submitForm}
        title={t('shop.custom_solution.submit')}
        message={t('shop.custom_solution.confirm_message')}
        loading={loading}
      />
      {newRequestsDisabled ? (
        <div role="alert" className="alert alert-warning mb-4">
          {t('shop.custom_solution.disabled_public')}
        </div>
      ) : null}
      <form onSubmit={handleSubmit} className="card bg-base-100 shadow">
        <div className="card-body space-y-6">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <label className="form-field w-full">
              <span className="form-label">{t('auth.email')} *</span>
              <input
                type="email"
                name="email"
                className={`input input-bordered w-full${fieldErrors.email ? ' input-error' : ''}`}
                value={form.email}
                onChange={handleChange}
                aria-invalid={!!fieldErrors.email}
                disabled={newRequestsDisabled}
              />
              {fieldErrors.email ? <p className="validator-hint text-error">{fieldErrors.email}</p> : null}
            </label>
            <label className="form-field w-full">
              <span className="form-label">{t('profile.phone')}</span>
              <input
                type="tel"
                name="phone"
                className={`input input-bordered w-full${fieldErrors.phone ? ' input-error' : ''}`}
                value={form.phone}
                onChange={handleChange}
                aria-invalid={!!fieldErrors.phone}
                disabled={newRequestsDisabled}
              />
              {fieldErrors.phone ? <p className="validator-hint text-error">{fieldErrors.phone}</p> : null}
            </label>
          </div>

          <label className="form-field w-full">
            <span className="form-label">{t('shop.custom_solution.description')} *</span>
            <textarea
              name="problem_description"
              className={`textarea textarea-bordered w-full${fieldErrors.problem_description ? ' textarea-error' : ''}`}
              rows={5}
              value={form.problem_description}
              onChange={handleChange}
              aria-invalid={!!fieldErrors.problem_description}
              disabled={newRequestsDisabled}
            />
            {fieldErrors.problem_description ? <p className="validator-hint text-error">{fieldErrors.problem_description}</p> : null}
          </label>

          <label className="form-field w-full">
            <span className="form-label">{t('shop.custom_solution.attachments')}</span>
            <input
              type="file"
              className="file-input file-input-bordered w-full"
              multiple
              accept="image/*,.pdf"
              onChange={handleFiles}
              disabled={newRequestsDisabled}
            />
          </label>

          <fieldset className="form-field space-y-5 border border-base-300 rounded-lg p-4">
            <legend className="form-label px-1">{t('shop.custom_solution.address_optional')}</legend>
            <label className="form-field w-full">
              <span className="form-label">{t('checkout.street')}</span>
              <input
                name="address_street"
                className="input input-bordered w-full"
                value={form.address_street}
                onChange={handleChange}
                disabled={newRequestsDisabled}
              />
            </label>
            <div className="flex gap-2">
              <label className="form-field flex-1">
                <span className="form-label">{t('profile.city')}</span>
                <input
                  name="address_city"
                  className="input input-bordered w-full"
                  value={form.address_city}
                  onChange={handleChange}
                  disabled={newRequestsDisabled}
                />
              </label>
              <label className="form-field w-28">
                <span className="form-label">{t('profile.postal_code')} *</span>
                <input
                  name="address_postal_code"
                  inputMode="numeric"
                  autoComplete="postal-code"
                  pattern="[0-9]*"
                  className={`input input-bordered w-full${fieldErrors.address_postal_code ? ' input-error' : ''}`}
                  value={form.address_postal_code}
                  onChange={handleChange}
                  aria-invalid={!!fieldErrors.address_postal_code}
                  disabled={newRequestsDisabled}
                />
                {fieldErrors.address_postal_code ? <p className="validator-hint text-error">{fieldErrors.address_postal_code}</p> : null}
              </label>
            </div>
            <label className="form-field w-full">
              <span className="form-label">{t('profile.province')}</span>
              <input
                name="address_province"
                className="input input-bordered w-full"
                value={form.address_province}
                onChange={handleChange}
                disabled={newRequestsDisabled}
              />
            </label>
            <label className="form-field w-full">
              <span className="form-label">{t('shop.custom_solution.address_note')}</span>
              <textarea
                name="address_note"
                className="textarea textarea-bordered w-full"
                rows={2}
                placeholder=""
                value={form.address_note}
                onChange={handleChange}
                disabled={newRequestsDisabled}
              />
            </label>
          </fieldset>

          <div className="flex justify-end">
            <button type="submit" className="btn btn-primary md:max-w-xs" disabled={loading || newRequestsDisabled}>
              {loading ? t('common.loading') : t('shop.custom_solution.submit')}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
