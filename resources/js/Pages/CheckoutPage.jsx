import React, { useCallback, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import { useCart } from '../contexts/CartContext';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';

export default function CheckoutPage() {
  const { t } = useTranslation();
  const { user } = useAuth();
  const { cart, fetchCart } = useCart();
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [confirmOpen, setConfirmOpen] = useState(false);
  const [form, setForm] = useState({
    payment_method: 'card',
    shipping_street: '',
    shipping_city: '',
    shipping_province: '',
    shipping_postal_code: '',
    shipping_note: '',
    installation_street: '',
    installation_city: '',
    installation_postal_code: '',
    installation_note: '',
  });

  const handleChange = (e) => {
    setForm((f) => ({ ...f, [e.target.name]: e.target.value }));
  };

  const doCheckout = useCallback(async () => {
    setConfirmOpen(false);
    setLoading(true);
    try {
      const { data } = await api.post('orders/checkout', form);
      if (data.success) {
        await fetchCart();
        navigate('/orders/' + data.data.id);
      }
    } catch {
      // show error
    } finally {
      setLoading(false);
    }
  }, [form, navigate, fetchCart]);

  const handleSubmit = (e) => {
    e.preventDefault();
    setConfirmOpen(true);
  };

  if (!user) {
    return (
      <div className="text-center py-8">
        <p className="mb-4">Has d’iniciar sessió per finalitzar la comanda.</p>
        <Link to="/login" className="btn btn-primary">{t('auth.login')}</Link>
      </div>
    );
  }

  if (!cart.lines?.length) {
    return (
      <div className="text-center py-8">
        <p className="mb-4">{t('shop.cart.empty')}</p>
        <Link to="/cart" className="btn btn-primary">{t('shop.cart')}</Link>
      </div>
    );
  }

  return (
    <div className="max-w-2xl mx-auto">
      <PageTitle>{t('shop.checkout')}</PageTitle>
      <ConfirmModal
        open={confirmOpen}
        onClose={() => setConfirmOpen(false)}
        onConfirm={doCheckout}
        title={t('shop.checkout')}
        message={t('checkout.confirm_message')}
        loading={loading}
      />
      <form onSubmit={handleSubmit} className="card bg-base-100 shadow">
        <div className="card-body space-y-5">
          <h2 className="font-semibold text-base-content">{t('checkout.shipping_address')}</h2>
          <label className="form-field w-full">
            <span className="form-label">{t('checkout.street')}</span>
            <input name="shipping_street" className="input input-bordered w-full" value={form.shipping_street} onChange={handleChange} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('profile.city')}</span>
            <input name="shipping_city" className="input input-bordered w-full" value={form.shipping_city} onChange={handleChange} required />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('profile.postal_code')}</span>
            <input name="shipping_postal_code" className="input input-bordered w-full" value={form.shipping_postal_code} onChange={handleChange} />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('checkout.note')}</span>
            <textarea name="shipping_note" className="textarea textarea-bordered w-full" rows={2} value={form.shipping_note} onChange={handleChange} />
          </label>

          <h2 className="font-semibold text-base-content mt-6">{t('checkout.payment')}</h2>
          <label className="form-field w-full">
            <span className="form-label">{t('checkout.payment_method')}</span>
            <select name="payment_method" className="select select-bordered w-full" value={form.payment_method} onChange={handleChange}>
              <option value="card">{t('checkout.payment.card')}</option>
              <option value="paypal">{t('checkout.payment.paypal')}</option>
              <option value="bizum">{t('checkout.payment.bizum')}</option>
            </select>
          </label>

          <p className="text-lg font-semibold mt-4">{t('shop.total')}: {Number(cart.total).toFixed(2)} €</p>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? t('common.loading') : t('shop.checkout')}
          </button>
        </div>
      </form>
    </div>
  );
}
