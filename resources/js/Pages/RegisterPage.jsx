import '../scss/main_shop.scss'
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useAuth } from '../contexts/AuthContext'
import { useCart } from '../contexts/CartContext'
import { scrollWindowToTopOnFormError } from '../lib/formScroll'
import { coercePostalCodeFieldValue } from '../lib/postalInput'
import { parseWithZod, registerFormSchema } from '../validation'
import FieldHint from '../components/FieldHint'
import {
  HiArrowLeft,
  HiOutlineEnvelope,
  HiOutlineLockClosed,
  HiOutlineEye,
  HiOutlineEyeSlash,
  HiOutlineUser,
  HiOutlinePhone,
} from 'react-icons/hi2'

export default function RegisterPage() {
  const { t } = useTranslation()
  const { register } = useAuth()
  const { mergeCart } = useCart()
  const navigate = useNavigate()

  const [form, setForm] = useState({
    type: 'person',
    identification: '',
    login_email: '',
    password: '',
    password_confirmation: '',
    name: '',
    surname: '',
    phone: '',
    address_street: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
  })
  const [showPassword, setShowPassword] = useState(false)
  const [showConfirm, setShowConfirm] = useState(false)
  const [error, setError] = useState('')
  const [fieldErrors, setFieldErrors] = useState({})
  const [loading, setLoading] = useState(false)
  const [acceptPrivacy, setAcceptPrivacy] = useState(false)
  const [acceptMarketing, setAcceptMarketing] = useState(false)

  const handleChange = (e) => {
    const { name, value } = e.target
    const next = coercePostalCodeFieldValue(name, value)
    setForm((f) => ({ ...f, [name]: next }))
    if (fieldErrors[name]) {
      setFieldErrors((fe) => { const n = { ...fe }; delete n[name]; return n })
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    setFieldErrors({})
    if (!acceptPrivacy) {
      setError(t('gdpr.accept_privacy'))
      scrollWindowToTopOnFormError()
      return
    }
    const parsed = parseWithZod(registerFormSchema, form, t)
    if (!parsed.ok) {
      setFieldErrors(parsed.fieldErrors)
      setError(parsed.firstError)
      scrollWindowToTopOnFormError()
      return
    }
    setLoading(true)
    try {
      const result = await register({ ...parsed.data, accept_privacy: true, accept_marketing: acceptMarketing })
      if (result.success) {
        await mergeCart()
        navigate('/verify-email?next=/')
      } else {
        const firstError = result.errors && Object.values(result.errors).flat()[0]
        setError(firstError || result.message || t('common.error'))
        scrollWindowToTopOnFormError()
      }
    } catch (err) {
      const data = err.response?.data
      const firstError = data?.errors && Object.values(data.errors).flat()[0]
      setError(firstError || data?.message || t('common.error'))
      scrollWindowToTopOnFormError()
    } finally {
      setLoading(false)
    }
  }

  return (
    <main className="auth-page auth-page--register" aria-labelledby="register-title">
      <Link to="/" className="auth-page__logo">
        <HiArrowLeft className="size-5" aria-hidden="true" />
        <p>{t('nav.back_home', 'Tornar a l\'inici')}</p>
      </Link>

      <form
        onSubmit={handleSubmit}
        className="auth-card auth-card--register"
        aria-label={t('auth.register', 'Registre')}
      >
        <header className="auth-card__header">
          <h1 id="register-title" className="auth-card__title">
            {t('auth.create_account', 'Crea el teu compte')}
          </h1>
          <p className="auth-card__subtitle">
            {t('register.verify_email_hint', 'Rebràs un correu de verificació')}
          </p>
        </header>

        {error && (
          <div className="auth-alert" role="alert">
            <p>{error}</p>
          </div>
        )}

        {/* Account type */}
        <div className="auth-field">
          <span className="auth-field__label">{t('register.account_type', 'Tipus de compte')}</span>
          <div className="join w-full">
            <label htmlFor="reg-type-person" className={`join-item btn flex-1 ${form.type === 'person' ? 'btn-primary' : 'btn-ghost'}`}>
              <input id="reg-type-person" type="radio" name="type" value="person" checked={form.type === 'person'} onChange={handleChange} className="sr-only" />
              {t('register.type_person', 'Persona')}
            </label>
            <label htmlFor="reg-type-company" className={`join-item btn flex-1 ${form.type === 'company' ? 'btn-primary' : 'btn-ghost'}`}>
              <input id="reg-type-company" type="radio" name="type" value="company" checked={form.type === 'company'} onChange={handleChange} className="sr-only" />
              {t('register.type_company', 'Empresa')}
            </label>
          </div>
        </div>

        <div className="auth-grid">
          {/* Email */}
          <div className="auth-field auth-grid__full">
            <label className="auth-field__label" htmlFor="reg-email">
              {t('auth.email', 'Correu electrònic')} *
            </label>
            <div className="auth-field__control">
              <HiOutlineEnvelope className="auth-field__icon" aria-hidden="true" />
              <input
                id="reg-email"
                type="email"
                name="login_email"
                placeholder="tu@email.com"
                value={form.login_email}
                onChange={handleChange}
                autoComplete="email"
                required
              />
            </div>
            {fieldErrors.login_email && <p className="text-error text-xs mt-1">{fieldErrors.login_email}</p>}
          </div>

          {/* Password */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-password">
              {t('auth.password', 'Contrasenya')} *
            </label>
            <div className="auth-field__control">
              <HiOutlineLockClosed className="auth-field__icon" aria-hidden="true" />
              <input
                id="reg-password"
                type={showPassword ? 'text' : 'password'}
                name="password"
                placeholder="••••••••"
                value={form.password}
                onChange={handleChange}
                autoComplete="new-password"
                required
              />
              <button type="button" className="auth-field__toggle" onClick={() => setShowPassword((v) => !v)} aria-label="toggle password">
                {showPassword ? <HiOutlineEyeSlash /> : <HiOutlineEye />}
              </button>
            </div>
            {fieldErrors.password && <p className="text-error text-xs mt-1">{fieldErrors.password}</p>}
          </div>

          {/* Confirm password */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-confirm">
              {t('auth.password_confirmation', 'Confirma la contrasenya')} *
            </label>
            <div className="auth-field__control">
              <HiOutlineLockClosed className="auth-field__icon" aria-hidden="true" />
              <input
                id="reg-confirm"
                type={showConfirm ? 'text' : 'password'}
                name="password_confirmation"
                placeholder="••••••••"
                value={form.password_confirmation}
                onChange={handleChange}
                autoComplete="new-password"
                required
              />
              <button type="button" className="auth-field__toggle" onClick={() => setShowConfirm((v) => !v)} aria-label="toggle confirm">
                {showConfirm ? <HiOutlineEyeSlash /> : <HiOutlineEye />}
              </button>
            </div>
            {fieldErrors.password_confirmation && <p className="text-error text-xs mt-1">{fieldErrors.password_confirmation}</p>}
          </div>

          {/* Name */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-name">{t('profile.name', 'Nom')} *</label>
            <div className="auth-field__control">
              <HiOutlineUser className="auth-field__icon" aria-hidden="true" />
              <input id="reg-name" type="text" name="name" value={form.name} onChange={handleChange} required />
            </div>
            {fieldErrors.name && <p className="text-error text-xs mt-1">{fieldErrors.name}</p>}
          </div>

          {/* Surname */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-surname">{t('profile.surname', 'Cognoms')}</label>
            <div className="auth-field__control">
              <HiOutlineUser className="auth-field__icon" aria-hidden="true" />
              <input id="reg-surname" type="text" name="surname" value={form.surname} onChange={handleChange} />
            </div>
          </div>

          {/* Phone */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-phone">
              {t('profile.phone', 'Telèfon')}
              <FieldHint text={t('gdpr.field_hint_phone', '')} />
            </label>
            <div className="auth-field__control">
              <HiOutlinePhone className="auth-field__icon" aria-hidden="true" />
              <input id="reg-phone" type="tel" name="phone" value={form.phone} onChange={handleChange} />
            </div>
          </div>

          {/* Postal code */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-postal">{t('profile.postal_code', 'Codi postal')}</label>
            <div className="auth-field__control auth-field__control--plain">
              <input
                id="reg-postal"
                type="text"
                name="address_postal_code"
                inputMode="numeric"
                pattern="[0-9]*"
                value={form.address_postal_code}
                onChange={handleChange}
              />
            </div>
          </div>

          {/* City */}
          <div className="auth-field">
            <label className="auth-field__label" htmlFor="reg-city">{t('profile.city', 'Ciutat')}</label>
            <div className="auth-field__control auth-field__control--plain">
              <input id="reg-city" type="text" name="address_city" value={form.address_city} onChange={handleChange} />
            </div>
          </div>
        </div>

        {/* Privacy checkboxes */}
        <div className="flex flex-col gap-3 mt-2">
          <label className="flex items-start gap-2 cursor-pointer">
            <input
              type="checkbox"
              className="checkbox checkbox-primary shrink-0 mt-0.5"
              checked={acceptPrivacy}
              onChange={(e) => setAcceptPrivacy(e.target.checked)}
              required
            />
            <span className="text-sm text-base-400">
              {t('gdpr.accept_privacy_prefix', 'Accepto la')}{' '}
              <Link to="/privacy-policy" className="auth-link">
                {t('footer.privacy_policy', 'política de privacitat')}
              </Link>
            </span>
          </label>
          <label className="flex items-start gap-2 cursor-pointer">
            <input
              type="checkbox"
              className="checkbox checkbox-primary shrink-0 mt-0.5"
              checked={acceptMarketing}
              onChange={(e) => setAcceptMarketing(e.target.checked)}
            />
            <span className="text-sm text-base-400">{t('gdpr.accept_marketing', 'Accepto rebre comunicacions comercials')}</span>
          </label>
        </div>

        <button type="submit" className="auth-button" disabled={loading || !acceptPrivacy}>
          {loading ? t('common.loading', 'Carregant…') : t('auth.register', 'Registra\'t')}
        </button>

        <div className="auth-card__footer">
          <p>
            {t('auth.have_account', 'Ja tens compte?')}
            <Link to="/login" className="auth-link auth-link--strong">
              {t('auth.login', 'Inicia sessió')}
            </Link>
          </p>
        </div>
      </form>
    </main>
  )
}
