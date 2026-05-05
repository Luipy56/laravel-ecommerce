import React, { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';
import { useAuth } from '../contexts/AuthContext';
import { useToast } from '../contexts/ToastContext';
import { fieldErrorsFromApiValidation, messageFromApiValidationError } from '../lib/apiValidationMessage';
import { scrollWindowToTopOnFormError } from '../lib/formScroll';
import { coercePostalCodeFieldValue, sanitizePostalCodeDigits } from '../lib/postalInput';
import { customSolutionFormSchema, parseWithZod } from '../validation';
import GdprNotice from '../components/GdprNotice';
import FieldHint from '../components/FieldHint';

export default function CustomSolutionPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { user, loading: authLoading } = useAuth();
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
  const [acceptPrivacy, setAcceptPrivacy] = useState(false);
  const [acceptMarketing, setAcceptMarketing] = useState(false);
  const submitInFlightRef = useRef(false);
  /** Fill email / phone / address from session once per login visit (avoid clobbering edits). */
  const prefilledFromSessionRef = useRef(false);
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

  useEffect(() => {
    if (!user) {
      prefilledFromSessionRef.current = false;
      return;
    }
    if (authLoading || prefilledFromSessionRef.current) {
      return;
    }
    prefilledFromSessionRef.current = true;
    const addr = user.address;
    const empty = (v) => (v == null ? true : String(v).trim() === '');
    setForm((prev) => ({
      ...prev,
      email: empty(prev.email) ? (user.login_email ?? '') : prev.email,
      phone: empty(prev.phone) ? (user.phone ?? '') : prev.phone,
      address_street: empty(prev.address_street) ? (addr?.street ?? '') : prev.address_street,
      address_city: empty(prev.address_city) ? (addr?.city ?? '') : prev.address_city,
      address_province: empty(prev.address_province) ? (addr?.province ?? '') : prev.address_province,
      address_postal_code: empty(prev.address_postal_code)
        ? (addr?.postal_code != null && String(addr.postal_code).trim() !== ''
          ? sanitizePostalCodeDigits(addr.postal_code)
          : '')
        : prev.address_postal_code,
    }));
  }, [authLoading, user]);

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
    if (fieldErrors.attachments) {
      setFieldErrors((fe) => {
        const next = { ...fe };
        delete next.attachments;
        return next;
      });
    }
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
      scrollWindowToTopOnFormError();
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
      setFieldErrors(fieldErrorsFromApiValidation(err, t));
      setError(messageFromApiValidationError(err, t));
      scrollWindowToTopOnFormError();
    } finally {
      setLoading(false);
      submitInFlightRef.current = false;
    }
  }, [form, t, showToast, navigate, newRequestsDisabled]);

  const handleSubmit = (e) => {
    e.preventDefault();
    if (newRequestsDisabled) return;
    setError('');
    setFieldErrors({});
    if (!acceptPrivacy) {
      setError(t('gdpr.accept_privacy'));
      scrollWindowToTopOnFormError();
      return;
    }
    const parsed = parseWithZod(customSolutionFormSchema, form, t);
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors);
      setError(parsed.firstError);
      scrollWindowToTopOnFormError();
      return;
    }
    setConfirmModalOpen(true);
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-4xl">
      <div className="space-y-4">
        <PageTitle>{t('shop.custom_solution')}</PageTitle>
        {error ? (
          <div className="alert alert-error mb-4" role="alert">
            {error}
          </div>
        ) : null}
      </div>
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
              <span className="form-label">
                {t('profile.phone')}
                <FieldHint text={t('gdpr.field_hint_phone')} />
              </span>
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
              className={`file-input file-input-bordered w-full${fieldErrors.attachments ? ' file-input-error' : ''}`}
              multiple
              accept="image/*,.pdf"
              onChange={handleFiles}
              disabled={newRequestsDisabled}
              aria-invalid={!!fieldErrors.attachments}
            />
            {fieldErrors.attachments ? <p className="validator-hint text-error">{fieldErrors.attachments}</p> : null}
          </label>

          <fieldset className="form-field space-y-5 border border-base-300 rounded-lg p-4">
            <legend className="form-label px-1 flex items-center gap-1">
              {t('shop.custom_solution.address_optional')}
              <FieldHint text={t('gdpr.field_hint_address')} />
            </legend>
            <label className="form-field w-full">
              <span className="form-label">{t('checkout.street')}</span>
              <input
                name="address_street"
                className={`input input-bordered w-full${fieldErrors.address_street ? ' input-error' : ''}`}
                value={form.address_street}
                onChange={handleChange}
                aria-invalid={!!fieldErrors.address_street}
                disabled={newRequestsDisabled}
              />
              {fieldErrors.address_street ? <p className="validator-hint text-error">{fieldErrors.address_street}</p> : null}
            </label>
            <div className="flex gap-2">
              <label className="form-field flex-1">
                <span className="form-label">{t('profile.city')}</span>
                <input
                  name="address_city"
                  className={`input input-bordered w-full${fieldErrors.address_city ? ' input-error' : ''}`}
                  value={form.address_city}
                  onChange={handleChange}
                  aria-invalid={!!fieldErrors.address_city}
                  disabled={newRequestsDisabled}
                />
                {fieldErrors.address_city ? <p className="validator-hint text-error">{fieldErrors.address_city}</p> : null}
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
                className={`input input-bordered w-full${fieldErrors.address_province ? ' input-error' : ''}`}
                value={form.address_province}
                onChange={handleChange}
                aria-invalid={!!fieldErrors.address_province}
                disabled={newRequestsDisabled}
              />
              {fieldErrors.address_province ? <p className="validator-hint text-error">{fieldErrors.address_province}</p> : null}
            </label>
            <label className="form-field w-full">
              <span className="form-label">{t('shop.custom_solution.address_note')}</span>
              <textarea
                name="address_note"
                className={`textarea textarea-bordered w-full${fieldErrors.address_note ? ' textarea-error' : ''}`}
                rows={2}
                placeholder=""
                value={form.address_note}
                onChange={handleChange}
                aria-invalid={!!fieldErrors.address_note}
                disabled={newRequestsDisabled}
              />
              {fieldErrors.address_note ? <p className="validator-hint text-error">{fieldErrors.address_note}</p> : null}
            </label>
          </fieldset>

          <GdprNotice noticeKey="gdpr.notice_custom_solution" />

          <div className="flex flex-col gap-3">
            <label className="flex items-start gap-2 cursor-pointer">
              <input
                type="checkbox"
                className="checkbox checkbox-primary mt-0.5 shrink-0"
                checked={acceptPrivacy}
                onChange={(e) => setAcceptPrivacy(e.target.checked)}
                disabled={newRequestsDisabled}
                required
              />
              <span className="text-sm">
                {t('gdpr.accept_privacy').split('[Privacy Policy]')[0]}
                <Link to="/privacy-policy" className="link link-primary">
                  {t('footer.privacy_policy')}
                </Link>
              </span>
            </label>
            <label className="flex items-start gap-2 cursor-pointer">
              <input
                type="checkbox"
                className="checkbox checkbox-primary mt-0.5 shrink-0"
                checked={acceptMarketing}
                onChange={(e) => setAcceptMarketing(e.target.checked)}
                disabled={newRequestsDisabled}
              />
              <span className="text-sm text-base-content/80">{t('gdpr.accept_marketing')}</span>
            </label>
          </div>

          <div className="flex justify-end">
            <button type="submit" className="btn btn-primary md:max-w-xs" disabled={loading || newRequestsDisabled || !acceptPrivacy}>
              {loading ? t('common.loading') : t('shop.custom_solution.submit')}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}
