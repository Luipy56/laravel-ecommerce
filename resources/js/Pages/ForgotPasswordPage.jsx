import '../scss/main_shop.scss'
import { useState } from 'react'
import { Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { api } from '../api'
import { scrollWindowToTopOnFormError } from '../lib/formScroll'
import { HiArrowLeft, HiOutlineEnvelope } from 'react-icons/hi2'

export default function ForgotPasswordPage() {
  const { t } = useTranslation()
  const [loginEmail, setLoginEmail] = useState('')
  const [loading, setLoading] = useState(false)
  const [done, setDone] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setLoading(true)
    try {
      await api.post('forgot-password', { login_email: loginEmail })
      setDone(true)
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.login_email?.[0] || t('common.error')
      setError(msg)
      scrollWindowToTopOnFormError()
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="auth-page">
      <Link to="/login" className="auth-page__logo">
        <HiArrowLeft className="size-5" aria-hidden="true" />
        <p>{t('auth.login', 'Iniciar sessió')}</p>
      </Link>

      <div className="auth-card">
        <header className="auth-card__header">
          <h1 className="auth-card__title">{t('auth.forgot_title', 'Recuperar contrasenya')}</h1>
          <p className="auth-card__subtitle">{t('auth.forgot_intro', 'Introdueix el teu correu per rebre les instruccions')}</p>
        </header>

        {done ? (
          <div className="auth-card--success">
            <p className="text-sm text-base-400">{t('auth.forgot_sent', 'Correu enviat! Comprova la teva safata d\'entrada.')}</p>
            <Link to="/login" className="auth-button mt-4" style={{ display: 'inline-flex', textDecoration: 'none' }}>
              {t('auth.login', 'Tornar a l\'inici de sessió')}
            </Link>
          </div>
        ) : (
          <form onSubmit={handleSubmit}>
            {error && (
              <div className="auth-alert" role="alert">
                <p>{error}</p>
              </div>
            )}

            <div className="auth-field">
              <label className="auth-field__label" htmlFor="forgot-email">
                {t('auth.email', 'Correu electrònic')}
              </label>
              <div className="auth-field__control">
                <HiOutlineEnvelope className="auth-field__icon" aria-hidden="true" />
                <input
                  id="forgot-email"
                  type="email"
                  placeholder="tu@email.com"
                  value={loginEmail}
                  onChange={(e) => setLoginEmail(e.target.value)}
                  required
                  autoComplete="email"
                />
              </div>
            </div>

            <button type="submit" className="auth-button" disabled={loading}>
              {loading ? t('common.loading', 'Carregant…') : t('auth.forgot_submit', 'Enviar instruccions')}
            </button>

            <div className="auth-card__footer">
              <p>
                <Link to="/login" className="auth-link">
                  {t('auth.login', 'Tornar a iniciar sessió')}
                </Link>
              </p>
            </div>
          </form>
        )}
      </div>
    </main>
  )
}
