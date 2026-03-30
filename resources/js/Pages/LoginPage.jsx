import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import { loginSchema, parseWithZod } from '../validation';

export default function LoginPage() {
  const { t } = useTranslation();
  const { login } = useAuth();
  const { mergeCart } = useCart();
  const navigate = useNavigate();
  const [loginEmail, setLoginEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [fieldErrors, setFieldErrors] = useState({});
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setFieldErrors({});
    const parsed = parseWithZod(loginSchema, { login_email: loginEmail, password }, t);
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors);
      setError(parsed.firstError);
      return;
    }
    setLoading(true);
    try {
      const result = await login(parsed.data.login_email, parsed.data.password, remember);
      if (result.success) {
        await mergeCart();
        navigate('/');
      } else {
        const msg = result.errors?.login_email?.[0] || result.message || t('auth.failed');
        setError(msg);
      }
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.login_email?.[0] || t('common.error');
      setError(msg);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-md card bg-base-100 shadow-lg">
      <div className="card-body">
        <h1 className="card-title text-2xl">{t('auth.login')}</h1>
        <form onSubmit={handleSubmit} className="space-y-5">
          {error && <div className="alert alert-error text-sm">{error}</div>}
          <label htmlFor="login-email" className="form-field w-full">
            <span className="form-label">{t('auth.email')}</span>
            <input
              id="login-email"
              type="email"
              className={`input input-bordered w-full${fieldErrors.login_email ? ' input-error' : ''}`}
              value={loginEmail}
              onChange={(e) => {
                setLoginEmail(e.target.value);
                if (fieldErrors.login_email) setFieldErrors((fe) => ({ ...fe, login_email: undefined }));
              }}
              autoComplete="email"
              aria-invalid={!!fieldErrors.login_email}
            />
            {fieldErrors.login_email ? <p className="validator-hint text-error">{fieldErrors.login_email}</p> : null}
          </label>
          <label htmlFor="login-password" className="form-field w-full">
            <span className="form-label">{t('auth.password')}</span>
            <input
              id="login-password"
              type="password"
              className={`input input-bordered w-full${fieldErrors.password ? ' input-error' : ''}`}
              value={password}
              onChange={(e) => {
                setPassword(e.target.value);
                if (fieldErrors.password) setFieldErrors((fe) => ({ ...fe, password: undefined }));
              }}
              autoComplete="current-password"
              aria-invalid={!!fieldErrors.password}
            />
            {fieldErrors.password ? <p className="validator-hint text-error">{fieldErrors.password}</p> : null}
          </label>
          <label htmlFor="login-remember" className="label cursor-pointer justify-start gap-2">
            <input id="login-remember" type="checkbox" className="checkbox checkbox-sm" checked={remember} onChange={(e) => setRemember(e.target.checked)} />
            <span className="label-text">{t('auth.remember')}</span>
          </label>
          <button type="submit" className="btn btn-primary w-full" disabled={loading}>
            {loading ? t('common.loading') : t('auth.login')}
          </button>
        </form>
        <p className="text-sm mt-4">
          <Link to="/register" className="link link-primary">{t('auth.register')}</Link>
        </p>
      </div>
    </div>
  );
}
