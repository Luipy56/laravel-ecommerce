import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { scrollWindowToTopOnFormError } from '../../lib/formScroll';
import { adminLoginSchema, parseWithZod } from '../../validation';

export default function AdminLoginPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const doLogin = async (user, pass) => {
    setError('');
    setFieldErrors({});
    setLoading(true);
    try {
      const { data } = await api.post('admin/login', { username: user, password: pass });
      if (data.success) navigate('/admin');
      else {
        setError(data.message || t('admin.login.error'));
        scrollWindowToTopOnFormError();
      }
    } catch (err) {
      setError(err.response?.data?.message || t('admin.login.error'));
      scrollWindowToTopOnFormError();
    } finally {
      setLoading(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const parsed = parseWithZod(adminLoginSchema, { username, password }, t);
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors);
      setError(parsed.firstError);
      scrollWindowToTopOnFormError();
      return;
    }
    await doLogin(parsed.data.username, parsed.data.password);
  };

  const handleAutoLogin = (e) => {
    e.preventDefault();
    doLogin('admin', 'admin');
  };

  return (
    <div className="min-h-screen flex flex-col bg-admin-login-animated">
      <div className="flex-1 flex items-center justify-center px-4 py-12 relative z-10">
        <div className="card bg-base-100 shadow-2xl w-full max-w-sm border border-base-200">
          <div className="card-body">
            <h1 className="card-title text-2xl justify-center text-center">
              {t('home.hero.title')}
            </h1>
            <p className="text-center text-base-content/70 text-sm mb-2">Admin</p>
            <form onSubmit={handleSubmit} className="space-y-5" aria-label={t('admin.login.title')}>
              {error && (
                <div role="alert" className="alert alert-error text-sm">
                  {error}
                </div>
              )}
              <label className="form-field w-full">
                <span className="form-label">{t('admin.login.username')}</span>
                <input
                  type="text"
                  className={`input input-bordered w-full${fieldErrors.username ? ' input-error' : ''}`}
                  value={username}
                  onChange={(e) => {
                    setUsername(e.target.value);
                    if (fieldErrors.username) setFieldErrors((fe) => ({ ...fe, username: undefined }));
                  }}
                  autoComplete="username"
                  aria-label={t('admin.login.username')}
                  aria-invalid={!!fieldErrors.username}
                />
                {fieldErrors.username ? <p className="validator-hint text-error">{fieldErrors.username}</p> : null}
              </label>
              <label className="form-field w-full">
                <span className="form-label">{t('admin.login.password')}</span>
                <input
                  type="password"
                  className={`input input-bordered w-full${fieldErrors.password ? ' input-error' : ''}`}
                  value={password}
                  onChange={(e) => {
                    setPassword(e.target.value);
                    if (fieldErrors.password) setFieldErrors((fe) => ({ ...fe, password: undefined }));
                  }}
                  autoComplete="current-password"
                  aria-label={t('admin.login.password')}
                  aria-invalid={!!fieldErrors.password}
                />
                {fieldErrors.password ? <p className="validator-hint text-error">{fieldErrors.password}</p> : null}
              </label>
              <button type="submit" className="btn btn-primary w-full" disabled={loading}>
                {loading ? t('common.loading') : t('admin.login.submit')}
              </button>
              <button
                type="button"
                className="btn btn-ghost btn-sm w-full text-base-content/60"
                onClick={handleAutoLogin}
                disabled={loading}
              >
                Auto login (admin / admin)
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
}
