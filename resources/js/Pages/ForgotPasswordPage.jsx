import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import { scrollWindowToTopOnFormError } from '../lib/formScroll';

export default function ForgotPasswordPage() {
  const { t } = useTranslation();
  const [loginEmail, setLoginEmail] = useState('');
  const [loading, setLoading] = useState(false);
  const [done, setDone] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      await api.post('forgot-password', { login_email: loginEmail });
      setDone(true);
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.login_email?.[0] || t('common.error');
      setError(msg);
      scrollWindowToTopOnFormError();
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-md">
      <PageTitle title={t('auth.forgot_title')} />
      <div className="card bg-base-100 shadow-lg">
        <div className="card-body">
          {done ? (
            <p className="text-sm">{t('auth.forgot_sent')}</p>
          ) : (
            <form onSubmit={handleSubmit} className="space-y-4">
              {error ? <div className="alert alert-error text-sm">{error}</div> : null}
              <p className="text-sm opacity-80">{t('auth.forgot_intro')}</p>
              <label htmlFor="forgot-email" className="form-field w-full">
                <span className="form-label">{t('auth.email')}</span>
                <input
                  id="forgot-email"
                  type="email"
                  className="input input-bordered w-full"
                  value={loginEmail}
                  onChange={(e) => setLoginEmail(e.target.value)}
                  required
                  autoComplete="email"
                />
              </label>
              <div className="flex justify-end gap-2">
                <Link to="/login" className="btn btn-ghost btn-sm">
                  {t('common.back')}
                </Link>
                <button type="submit" className="btn btn-primary btn-sm" disabled={loading}>
                  {loading ? t('common.loading') : t('auth.forgot_submit')}
                </button>
              </div>
            </form>
          )}
          {!done ? (
            <p className="text-sm mt-4">
              <Link to="/login" className="link link-primary">{t('auth.login')}</Link>
            </p>
          ) : null}
        </div>
      </div>
    </div>
  );
}
