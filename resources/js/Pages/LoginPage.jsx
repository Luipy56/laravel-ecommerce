import '../scss/main_shop.scss'
import { useState } from 'react'
import { Link, useNavigate, useSearchParams } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useAuth } from '../contexts/AuthContext'
import { useCart } from '../contexts/CartContext'
import { scrollWindowToTopOnFormError } from '../lib/formScroll'
import { loginSchema, parseWithZod } from '../validation'
import {
  HiArrowLeft,
  HiOutlineEnvelope,
  HiOutlineLockClosed,
  HiOutlineEye,
  HiOutlineEyeSlash,
} from 'react-icons/hi2'

export default function LoginPage() {
  const { t } = useTranslation()
  const { login } = useAuth()
  const { mergeCart } = useCart()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const verifiedFromEmail = searchParams.get('verified') === '1'

  const [loginEmail, setLoginEmail] = useState('')
  const [password, setPassword] = useState('')
  const [showPassword, setShowPassword] = useState(false)
  const [remember, setRemember] = useState(false)
  const [error, setError] = useState('')
  const [fieldErrors, setFieldErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setFieldErrors({})
    const parsed = parseWithZod(loginSchema, { login_email: loginEmail, password }, t)
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors)
      setError(parsed.firstError)
      scrollWindowToTopOnFormError()
      return
    }
    setLoading(true)
    try {
      const result = await login(parsed.data.login_email, parsed.data.password, remember)
      if (result.success) {
        await mergeCart()
        const next = searchParams.get('next')
        const safeNext =
          next && next.startsWith('/') && !next.startsWith('//')
            ? next
            : '/'
        if (result.user && !result.user.email_verified) {
          navigate(`/verify-email?next=${encodeURIComponent(safeNext)}`)
          return
        }
        navigate(safeNext)
      } else {
        const msg = result.errors?.login_email?.[0] || result.message || t('auth.failed')
        setError(msg)
        scrollWindowToTopOnFormError()
      }
    } catch (err) {
      const msg =
        err.response?.data?.message ||
        err.response?.data?.errors?.login_email?.[0] ||
        t('common.error')
      setError(msg)
      scrollWindowToTopOnFormError()
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="auth-page" aria-labelledby="login-title">
      <Link to="/" className="auth-page__logo">
        <HiArrowLeft className="size-5" aria-hidden="true" />
        <p>{t('nav.back_home', 'Tornar a l\'inici')}</p>
      </Link>

      <form
        onSubmit={handleSubmit}
        className="auth-card"
        aria-label={t('auth.login', 'Iniciar sessió')}
      >
        <header className="auth-card__header">
          <h1 id="login-title" className="auth-card__title">
            {t('auth.welcome_back', 'Benvingut de nou')}
          </h1>
          <p className="auth-card__subtitle">
            {t('auth.login_subtitle', 'Inicia sessió per accedir al teu compte')}
          </p>
        </header>

        {verifiedFromEmail && (
          <div className="auth-alert" role="status" style={{ background: 'var(--color-success)', color: 'var(--color-success-content)', border: 'none' }}>
            <p>{t('auth.verify_success_banner', 'Correu verificat correctament!')}</p>
          </div>
        )}

        {error && (
          <div className="auth-alert" role="alert">
            <p>{error}</p>
          </div>
        )}

        <div className="auth-field">
          <label className="auth-field__label" htmlFor="login-email">
            {t('auth.email', 'Correu electrònic')}
          </label>
          <div className="auth-field__control">
            <HiOutlineEnvelope className="auth-field__icon" aria-hidden="true" />
            <input
              id="login-email"
              type="email"
              placeholder="tu@email.com"
              value={loginEmail}
              onChange={(e) => {
                setLoginEmail(e.target.value)
                if (fieldErrors.login_email) setFieldErrors((fe) => ({ ...fe, login_email: undefined }))
              }}
              autoComplete="email"
              required
            />
          </div>
          {fieldErrors.login_email && (
            <p className="text-error text-xs mt-1">{fieldErrors.login_email}</p>
          )}
        </div>

        <div className="auth-field">
          <div className="auth-field__top">
            <label className="auth-field__label" htmlFor="login-password">
              {t('auth.password', 'Contrasenya')}
            </label>
            <Link to="/forgot-password" className="auth-link auth-link--small">
              {t('auth.forgot_link', 'Has oblidat la contrasenya?')}
            </Link>
          </div>
          <div className="auth-field__control">
            <HiOutlineLockClosed className="auth-field__icon" aria-hidden="true" />
            <input
              id="login-password"
              type={showPassword ? 'text' : 'password'}
              placeholder="••••••••"
              value={password}
              onChange={(e) => {
                setPassword(e.target.value)
                if (fieldErrors.password) setFieldErrors((fe) => ({ ...fe, password: undefined }))
              }}
              autoComplete="current-password"
              required
            />
            <button
              type="button"
              className="auth-field__toggle"
              onClick={() => setShowPassword((v) => !v)}
              aria-label={showPassword ? t('auth.hide_password', 'Amaga la contrasenya') : t('auth.show_password', 'Mostra la contrasenya')}
            >
              {showPassword
                ? <HiOutlineEyeSlash aria-hidden="true" />
                : <HiOutlineEye aria-hidden="true" />}
            </button>
          </div>
          {fieldErrors.password && (
            <p className="text-error text-xs mt-1">{fieldErrors.password}</p>
          )}
        </div>

        <label htmlFor="login-remember" className="flex items-center gap-2 cursor-pointer mt-1">
          <input
            id="login-remember"
            type="checkbox"
            className="checkbox checkbox-sm"
            checked={remember}
            onChange={(e) => setRemember(e.target.checked)}
          />
          <span className="text-sm text-base-400">{t('auth.remember', 'Recorda\'m')}</span>
        </label>

        <button
          type="submit"
          className="auth-button"
          disabled={loading}
        >
          {loading ? t('common.loading', 'Carregant…') : t('auth.login', 'Iniciar sessió')}
        </button>

        <div className="auth-card__footer">
          <p>
            {t('auth.no_account', 'No tens compte?')}
            <Link to="/register" className="auth-link auth-link--strong">
              {t('auth.register', 'Registra\'t')}
            </Link>
          </p>
        </div>
      </form>
    </main>
  )
}
