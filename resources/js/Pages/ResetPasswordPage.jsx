import React, { useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import { scrollWindowToTopOnFormError } from '../lib/formScroll';

export default function ResetPasswordPage() {
  const { t } = useTranslation();
  const [searchParams] = useSearchParams();
  const token = useMemo(() => searchParams.get('token') || '', [searchParams]);
  const loginEmail = useMemo(() => searchParams.get('login_email') || '', [searchParams]);

  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [loading, setLoading] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState('');

  const missingParams = !token || !loginEmail;

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await api.post('reset-password', {
        token,
        login_email: loginEmail,
        password,
        password_confirmation: passwordConfirmation,
      });
      setDone(true);
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.token?.[0] || t('common.error');
      setError(msg);
      scrollWindowToTopOnFormError();
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-md">
      <PageTitle title={t('auth.reset_title')} />
      <div className="card bg-base-100 shadow-lg">
        <div className="card-body">
          {missingParams ? (
            <div className="alert alert-error text-sm">{t('auth.reset_missing_link')}</div>
          ) : done ? (
            <p className="text-sm">{t('auth.reset_done')}</p>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {error ? <div className="alert alert-error text-sm">{error}</div> : null}
              <label htmlFor="reset-email" className="form-field w-full">
                <span className="form-label">{t('auth.email')}</span>
                <input id="reset-email" type="email" className="input input-bordered w-full bg-base-200" readOnly value={loginEmail} />
              </label>
              <label htmlFor="reset-password" className="form-field w-full">
                <span className="form-label">{t('auth.password')}</span>
                <input
                  id="reset-password"
                  type="password"
                  className="input input-bordered w-full"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  required
                  autoComplete="new-password"
                />
              </label>
              <label htmlFor="reset-password2" className="form-field w-full">
                <span className="form-label">{t('auth.password_confirmation')}</span>
                <input
                  id="reset-password2"
                  type="password"
                  className="input input-bordered w-full"
                  value={passwordConfirmation}
                  onChange={(e) => setPasswordConfirmation(e.target.value)}
                  required
                  autoComplete="new-password"
                />
              </label>
              <div className="flex justify-end">
                <button type="submit" className="btn btn-primary btn-sm" disabled={loading}>
                  {loading ? t('common.loading') : t('auth.reset_submit')}
                </button>
              </div>
            </form>
          )}
          <p className="text-sm mt-4">
            <Link to="/login" className="link link-primary">{t('auth.login')}</Link>
          </p>
        </div>
      </div>
    </div>
  );
}
