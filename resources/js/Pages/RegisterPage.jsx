import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';

export default function RegisterPage() {
  const { t } = useTranslation();
  const { register } = useAuth();
  const { mergeCart } = useCart();
  const navigate = useNavigate();
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
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const result = await register(form);
      if (result.success) {
        await mergeCart();
        navigate('/');
      } else {
        const firstError = result.errors && Object.values(result.errors).flat()[0];
        setError(firstError || result.message || t('common.error'));
      }
    } catch (err) {
      const data = err.response?.data;
      const firstError = data?.errors && Object.values(data.errors).flat()[0];
      setError(firstError || data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  const isCompany = form.type === 'company';

  return (
    <div className="max-w-4xl mx-auto card bg-base-100 shadow-lg">
      <div className="card-body">
        <h1 className="card-title text-2xl">{t('auth.register')}</h1>
        <p className="text-sm text-base-content/70">{t('register.required_note')}</p>
        <form onSubmit={handleSubmit} className="space-y-6">
          {error && <div className="alert alert-error text-sm">{error}</div>}

          <div className="form-field w-full" role="group" aria-labelledby="register-type-legend">
            <span id="register-type-legend" className="form-label block">{t('register.account_type')} *</span>
            <div className="join w-full max-w-xs mt-1">
              <label htmlFor="register-type-person" className={`join-item btn flex-1 ${form.type === 'person' ? 'btn-active' : ''}`}>
                <input id="register-type-person" type="radio" name="type" value="person" checked={form.type === 'person'} onChange={handleChange} className="sr-only" />
                {t('register.type_person')}
              </label>
              <label htmlFor="register-type-company" className={`join-item btn flex-1 ${form.type === 'company' ? 'btn-active' : ''}`}>
                <input id="register-type-company" type="radio" name="type" value="company" checked={form.type === 'company'} onChange={handleChange} className="sr-only" />
                {t('register.type_company')}
              </label>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div className="space-y-5">
              <h2 className="font-semibold text-base-content border-b border-base-300 pb-1">{t('register.section_access')}</h2>
              <label htmlFor="register-login_email" className="form-field w-full">
                <span className="form-label">{t('auth.email')} *</span>
                <input id="register-login_email" type="email" name="login_email" className="input input-bordered w-full" value={form.login_email} onChange={handleChange} required />
              </label>
              <label htmlFor="register-password" className="form-field w-full">
                <span className="form-label">{t('auth.password')} *</span>
                <input id="register-password" type="password" name="password" className="input input-bordered w-full" value={form.password} onChange={handleChange} required minLength={8} />
              </label>
              <label htmlFor="register-password_confirmation" className="form-field w-full">
                <span className="form-label">{t('auth.password_confirmation')} *</span>
                <input id="register-password_confirmation" type="password" name="password_confirmation" className="input input-bordered w-full" value={form.password_confirmation} onChange={handleChange} required />
              </label>
            </div>
            <div className="space-y-5">
              <h2 className="font-semibold text-base-content border-b border-base-300 pb-1">{t('register.section_contact')}</h2>
              <label htmlFor="register-name" className="form-field w-full">
                <span className="form-label">
                  {t('profile.name')} *
                  {isCompany && <span className="text-base-content/60 font-normal"> ({t('register.contact_name_hint')})</span>}
                </span>
                <input id="register-name" type="text" name="name" className="input input-bordered w-full" value={form.name} onChange={handleChange} required />
              </label>
              <label htmlFor="register-surname" className="form-field w-full">
                <span className="form-label">{t('profile.surname')}</span>
                <input id="register-surname" type="text" name="surname" className="input input-bordered w-full" value={form.surname} onChange={handleChange} />
              </label>
              <label htmlFor="register-phone" className="form-field w-full">
                <span className="form-label">{t('profile.phone')}</span>
                <input id="register-phone" type="tel" name="phone" className="input input-bordered w-full" value={form.phone} onChange={handleChange} />
              </label>
              <label htmlFor="register-identification" className="form-field w-full">
                <span className="form-label">{t('register.identification')}</span>
                <input
                  id="register-identification"
                  type="text"
                  name="identification"
                  className="input input-bordered w-full"
                  placeholder={t('register.identification_placeholder')}
                  value={form.identification}
                  onChange={handleChange}
                  maxLength={20}
                />
              </label>
            </div>
          </div>

          <fieldset className="form-field space-y-5 border border-base-300 rounded-lg p-4">
            <legend className="form-label px-1">{t('register.address_optional')}</legend>
            <label htmlFor="register-address_street" className="form-field w-full">
              <span className="form-label">{t('checkout.street')}</span>
              <input id="register-address_street" name="address_street" className="input input-bordered w-full" value={form.address_street} onChange={handleChange} />
            </label>
            <div className="flex gap-2">
              <label htmlFor="register-address_city" className="form-field flex-1">
                <span className="form-label">{t('profile.city')}</span>
                <input id="register-address_city" name="address_city" className="input input-bordered w-full" value={form.address_city} onChange={handleChange} />
              </label>
              <label htmlFor="register-address_postal_code" className="form-field w-28">
                <span className="form-label">{t('profile.postal_code')}</span>
                <input id="register-address_postal_code" name="address_postal_code" className="input input-bordered w-full" value={form.address_postal_code} onChange={handleChange} />
              </label>
            </div>
            <label htmlFor="register-address_province" className="form-field w-full">
              <span className="form-label">{t('profile.province')}</span>
              <input id="register-address_province" name="address_province" className="input input-bordered w-full" value={form.address_province} onChange={handleChange} />
            </label>
          </fieldset>

          <div className="flex flex-wrap items-center justify-between gap-4">
            <Link to="/login" className="link link-primary text-sm">{t('auth.login')}</Link>
            <button type="submit" className="btn btn-primary" disabled={loading}>
              {loading ? t('common.loading') : t('auth.register')}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
}
