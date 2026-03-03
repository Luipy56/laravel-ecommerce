import React, { useState, useEffect, useCallback } from 'react';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useAuth } from '../contexts/AuthContext';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';

const ADDRESS_TYPES = ['shipping', 'installation', 'other'];

export default function ProfilePage() {
  const { t } = useTranslation();
  const { user } = useAuth();
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saved, setSaved] = useState(false);

  // Account form (identification + primary contact)
  const [accountForm, setAccountForm] = useState({
    identification: '',
    name: '',
    surname: '',
    phone: '',
  });
  const [accountSaving, setAccountSaving] = useState(false);

  // Password form
  const [passwordForm, setPasswordForm] = useState({ password: '', password_confirmation: '' });
  const [passwordSaving, setPasswordSaving] = useState(false);

  // Address modal: null = closed, {} = create, { id, ... } = edit
  const [addressModal, setAddressModal] = useState(null);
  const [addressForm, setAddressForm] = useState({
    type: 'shipping',
    label: '',
    street: '',
    city: '',
    province: '',
    postal_code: '',
  });
  const [addressSaving, setAddressSaving] = useState(false);

  // Delete confirmation: { type: 'address'|'contact', id } or null
  const [deleteConfirm, setDeleteConfirm] = useState(null);
  const [deleteLoading, setDeleteLoading] = useState(false);

  // Contact modal
  const [contactModal, setContactModal] = useState(null);
  const [contactForm, setContactForm] = useState({
    name: '',
    surname: '',
    phone: '',
    phone2: '',
    email: '',
    is_primary: false,
  });
  const [contactSaving, setContactSaving] = useState(false);

  const fetchProfile = useCallback(async () => {
    const r = await api.get('profile');
    if (r.data.success && r.data.data) {
      const d = r.data.data;
      setProfile(d);
      setAccountForm({
        identification: d.identification ?? '',
        name: d.name ?? '',
        surname: d.surname ?? '',
        phone: d.phone ?? '',
      });
    }
  }, []);

  useEffect(() => {
    if (!user) return;
    setLoading(true);
    fetchProfile().finally(() => setLoading(false));
  }, [user, fetchProfile]);

  useEffect(() => {
    if (!saved) return;
    const timer = setTimeout(() => setSaved(false), 3000);
    return () => clearTimeout(timer);
  }, [saved]);

  const handleAccountChange = (e) => {
    const { name, value } = e.target;
    setAccountForm((f) => ({ ...f, [name]: value }));
  };

  const handleAccountSubmit = async (e) => {
    e.preventDefault();
    setAccountSaving(true);
    try {
      await api.put('profile', {
        identification: accountForm.identification,
        name: accountForm.name,
        surname: accountForm.surname,
        phone: accountForm.phone,
      });
      await fetchProfile();
      setSaved(true);
    } finally {
      setAccountSaving(false);
    }
  };

  const handlePasswordChange = (e) => {
    setPasswordForm((f) => ({ ...f, [e.target.name]: e.target.value }));
  };

  const handlePasswordSubmit = async (e) => {
    e.preventDefault();
    if (!passwordForm.password) return;
    setPasswordSaving(true);
    try {
      await api.put('profile', {
        name: profile?.name ?? '',
        surname: profile?.surname ?? '',
        phone: profile?.phone ?? '',
        password: passwordForm.password,
        password_confirmation: passwordForm.password_confirmation,
      });
      setPasswordForm({ password: '', password_confirmation: '' });
      setSaved(true);
    } finally {
      setPasswordSaving(false);
    }
  };

  const openAddressModal = (addr = null) => {
    if (addr) {
      setAddressModal({ id: addr.id });
      setAddressForm({
        type: addr.type,
        label: addr.label ?? '',
        street: addr.street,
        city: addr.city,
        province: addr.province ?? '',
        postal_code: addr.postal_code ?? '',
      });
    } else {
      setAddressModal({});
      setAddressForm({
        type: 'shipping',
        label: '',
        street: '',
        city: '',
        province: '',
        postal_code: '',
      });
    }
  };

  const handleAddressFormChange = (e) => {
    const { name, value } = e.target;
    setAddressForm((f) => ({ ...f, [name]: value }));
  };

  const handleAddressSubmit = async (e) => {
    e.preventDefault();
    setAddressSaving(true);
    try {
      if (addressModal.id) {
        await api.put(`profile/addresses/${addressModal.id}`, addressForm);
      } else {
        await api.post('profile/addresses', addressForm);
      }
      await fetchProfile();
      setAddressModal(null);
      setSaved(true);
    } finally {
      setAddressSaving(false);
    }
  };

  const handleDeleteAddress = (id) => {
    setDeleteConfirm({ type: 'address', id });
  };

  const doDeleteAddress = async () => {
    if (deleteConfirm?.type !== 'address') return;
    setDeleteLoading(true);
    try {
      await api.delete(`profile/addresses/${deleteConfirm.id}`);
      await fetchProfile();
      setSaved(true);
      setDeleteConfirm(null);
    } catch (err) {
      // ignore
    } finally {
      setDeleteLoading(false);
    }
  };

  const openContactModal = (contact = null) => {
    if (contact) {
      setContactModal({ id: contact.id });
      setContactForm({
        name: contact.name,
        surname: contact.surname ?? '',
        phone: contact.phone ?? '',
        phone2: contact.phone2 ?? '',
        email: contact.email ?? '',
        is_primary: contact.is_primary ?? false,
      });
    } else {
      setContactModal({});
      setContactForm({
        name: '',
        surname: '',
        phone: '',
        phone2: '',
        email: '',
        is_primary: false,
      });
    }
  };

  const handleContactFormChange = (e) => {
    const { name, value, type, checked } = e.target;
    setContactForm((f) => ({
      ...f,
      [name]: type === 'checkbox' ? checked : value,
    }));
  };

  const handleContactSubmit = async (e) => {
    e.preventDefault();
    setContactSaving(true);
    try {
      if (contactModal.id) {
        await api.put(`profile/contacts/${contactModal.id}`, contactForm);
      } else {
        await api.post('profile/contacts', contactForm);
      }
      await fetchProfile();
      setContactModal(null);
      setSaved(true);
    } finally {
      setContactSaving(false);
    }
  };

  const handleDeleteContact = (id) => {
    setDeleteConfirm({ type: 'contact', id });
  };

  const doDeleteContact = async () => {
    if (deleteConfirm?.type !== 'contact') return;
    setDeleteLoading(true);
    try {
      await api.delete(`profile/contacts/${deleteConfirm.id}`);
      await fetchProfile();
      setSaved(true);
      setDeleteConfirm(null);
    } catch (err) {
      // ignore
    } finally {
      setDeleteLoading(false);
    }
  };

  const addressTypeLabel = (type) => {
    if (type === 'shipping') return t('profile.address_type_shipping');
    if (type === 'installation') return t('profile.address_type_installation');
    return t('profile.address_type_other');
  };

  if (!user) return null;
  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg text-primary" />
      </div>
    );
  }
  if (!profile) return null;

  const isCompany = profile.type === 'company';

  return (
    <div className="max-w-2xl mx-auto space-y-8 pb-12">
      <PageTitle>{t('shop.profile')}</PageTitle>
      {saved && (
        <div role="alert" className="alert alert-success">
          {t('common.success')}
        </div>
      )}

      {/* Account data */}
      <section className="card bg-base-100 shadow">
        <div className="card-body">
          <h2 className="card-title text-lg">{t('profile.account_data')}</h2>
          <div className="flex flex-wrap gap-2 items-center">
            <span className="badge badge-neutral">
              {profile.type === 'company' ? t('profile.type_company') : t('profile.type_person')}
            </span>
            <span className="text-base-content/70 text-sm">{t('profile.email')}: {profile.login_email}</span>
          </div>
          <form onSubmit={handleAccountSubmit} className="space-y-4 mt-4">
            <label className="label">
              <span className="label-text">{t('profile.identification')}</span>
            </label>
            <input
              type="text"
              name="identification"
              className="input input-bordered w-full"
              value={accountForm.identification}
              onChange={handleAccountChange}
              placeholder="DNI, NIE, CIF"
            />
            <div className="divider my-2">{t('profile.contact_data')}</div>
            <input
              type="text"
              name="name"
              className="input input-bordered w-full"
              value={accountForm.name}
              onChange={handleAccountChange}
              placeholder={t('profile.name')}
              required
            />
            <input
              type="text"
              name="surname"
              className="input input-bordered w-full"
              value={accountForm.surname}
              onChange={handleAccountChange}
              placeholder={t('profile.surname')}
            />
            <input
              type="tel"
              name="phone"
              className="input input-bordered w-full"
              value={accountForm.phone}
              onChange={handleAccountChange}
              placeholder={t('profile.phone')}
            />
            <button type="submit" className="btn btn-primary" disabled={accountSaving}>
              {accountSaving ? t('common.loading') : t('common.save')}
            </button>
          </form>
        </div>
      </section>

      {/* Company: contacts list */}
      {isCompany && (
        <section className="card bg-base-100 shadow">
          <div className="card-body">
            <div className="flex flex-wrap items-center justify-between gap-2">
              <h2 className="card-title text-lg">{t('profile.contacts')}</h2>
              <button type="button" className="btn btn-sm btn-primary" onClick={() => openContactModal()}>
                {t('profile.add_contact')}
              </button>
            </div>
            {profile.contacts?.length ? (
              <ul className="space-y-3">
                {profile.contacts.map((c) => (
                  <li key={c.id} className="flex flex-wrap items-center justify-between gap-2 p-3 bg-base-200 rounded-lg">
                    <div>
                      <span className="font-medium">{c.name} {c.surname}</span>
                      {c.is_primary && (
                        <span className="badge badge-sm badge-primary ml-2">{t('profile.primary_contact')}</span>
                      )}
                      <div className="text-sm text-base-content/70">
                        {c.phone && <span>{c.phone}</span>}
                        {c.phone2 && <span> · {c.phone2}</span>}
                        {c.email && <span> · {c.email}</span>}
                      </div>
                    </div>
                    <div className="flex gap-1">
                      <button type="button" className="btn btn-ghost btn-sm" onClick={() => openContactModal(c)}>
                        {t('common.edit')}
                      </button>
                      <button type="button" className="btn btn-ghost btn-sm text-error" onClick={() => handleDeleteContact(c.id)}>
                        {t('profile.delete')}
                      </button>
                    </div>
                  </li>
                ))}
              </ul>
            ) : (
              <p className="text-base-content/70">{t('profile.contacts_empty')}</p>
            )}
          </div>
        </section>
      )}

      {/* Addresses */}
      <section className="card bg-base-100 shadow">
        <div className="card-body">
          <div className="flex flex-wrap items-center justify-between gap-2">
            <h2 className="card-title text-lg">{t('profile.addresses')}</h2>
            <button type="button" className="btn btn-sm btn-primary" onClick={() => openAddressModal()}>
              {t('profile.add_address')}
            </button>
          </div>
          {profile.addresses?.length ? (
            <ul className="space-y-3">
              {profile.addresses.map((addr) => (
                <li key={addr.id} className="flex flex-wrap items-start justify-between gap-2 p-3 bg-base-200 rounded-lg">
                  <div>
                    <div className="flex flex-wrap gap-2 items-center">
                      <span className="badge badge-outline">{addressTypeLabel(addr.type)}</span>
                      {addr.label && <span className="text-sm font-medium">{addr.label}</span>}
                    </div>
                    <p className="text-sm mt-1">
                      {addr.street}, {addr.postal_code} {addr.city}
                      {addr.province ? `, ${addr.province}` : ''}
                    </p>
                  </div>
                  <div className="flex gap-1">
                    <button type="button" className="btn btn-ghost btn-sm" onClick={() => openAddressModal(addr)}>
                      {t('common.edit')}
                    </button>
                    <button type="button" className="btn btn-ghost btn-sm text-error" onClick={() => handleDeleteAddress(addr.id)}>
                      {t('profile.delete')}
                    </button>
                  </div>
                </li>
              ))}
            </ul>
          ) : (
            <p className="text-base-content/70">{t('profile.addresses_empty')}</p>
          )}
        </div>
      </section>

      {/* Change password */}
      <section className="card bg-base-100 shadow">
        <div className="card-body">
          <h2 className="card-title text-lg">{t('profile.change_password')}</h2>
          <form onSubmit={handlePasswordSubmit} className="space-y-4">
            <input
              type="password"
              name="password"
              className="input input-bordered w-full"
              value={passwordForm.password}
              onChange={handlePasswordChange}
              placeholder={t('profile.new_password')}
              minLength={8}
            />
            <input
              type="password"
              name="password_confirmation"
              className="input input-bordered w-full"
              value={passwordForm.password_confirmation}
              onChange={handlePasswordChange}
              placeholder={t('auth.password_confirmation')}
            />
            <button type="submit" className="btn btn-neutral" disabled={passwordSaving || !passwordForm.password}>
              {passwordSaving ? t('common.loading') : t('common.save')}
            </button>
          </form>
        </div>
      </section>

      {/* Address modal */}
      <dialog className={`modal ${addressModal !== null ? 'modal-open' : ''}`}>
        <div className="modal-box">
          <h3 className="font-bold text-lg">
            {addressModal?.id ? t('profile.edit_address') : t('profile.add_address')}
          </h3>
          <form onSubmit={handleAddressSubmit} className="space-y-3 mt-4">
            <label className="label">
              <span className="label-text">{t('profile.address_type')}</span>
            </label>
            <select
              name="type"
              className="select select-bordered w-full"
              value={addressForm.type}
              onChange={handleAddressFormChange}
            >
              {ADDRESS_TYPES.map((type) => (
                <option key={type} value={type}>{addressTypeLabel(type)}</option>
              ))}
            </select>
            <label className="label">
              <span className="label-text">{t('profile.address_label')} ({t('common.optional')})</span>
            </label>
            <input
              type="text"
              name="label"
              className="input input-bordered w-full"
              value={addressForm.label}
              onChange={handleAddressFormChange}
            />
            <label className="label">
              <span className="label-text">{t('profile.street')} *</span>
            </label>
            <input
              type="text"
              name="street"
              className="input input-bordered w-full"
              value={addressForm.street}
              onChange={handleAddressFormChange}
              required
            />
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="label"><span className="label-text">{t('profile.city')} *</span></label>
                <input
                  type="text"
                  name="city"
                  className="input input-bordered w-full"
                  value={addressForm.city}
                  onChange={handleAddressFormChange}
                  required
                />
              </div>
              <div>
                <label className="label"><span className="label-text">{t('profile.postal_code')}</span></label>
                <input
                  type="text"
                  name="postal_code"
                  className="input input-bordered w-full"
                  value={addressForm.postal_code}
                  onChange={handleAddressFormChange}
                />
              </div>
            </div>
            <label className="label">
              <span className="label-text">{t('profile.province')}</span>
            </label>
            <input
              type="text"
              name="province"
              className="input input-bordered w-full"
              value={addressForm.province}
              onChange={handleAddressFormChange}
            />
            <div className="modal-action">
              <button type="button" className="btn btn-ghost" onClick={() => setAddressModal(null)}>
                {t('common.cancel')}
              </button>
              <button type="submit" className="btn btn-primary" disabled={addressSaving}>
                {addressSaving ? t('common.loading') : t('common.save')}
              </button>
            </div>
          </form>
        </div>
        <form method="dialog" className="modal-backdrop">
          <button type="button" onClick={() => setAddressModal(null)}>{t('common.close')}</button>
        </form>
      </dialog>

      {/* Contact modal (company only) */}
      <dialog className={`modal ${contactModal !== null ? 'modal-open' : ''}`}>
        <div className="modal-box">
          <h3 className="font-bold text-lg">
            {contactModal?.id ? t('profile.edit_contact') : t('profile.add_contact')}
          </h3>
          <form onSubmit={handleContactSubmit} className="space-y-3 mt-4">
            <input
              type="text"
              name="name"
              className="input input-bordered w-full"
              value={contactForm.name}
              onChange={handleContactFormChange}
              placeholder={t('profile.name')}
              required
            />
            <input
              type="text"
              name="surname"
              className="input input-bordered w-full"
              value={contactForm.surname}
              onChange={handleContactFormChange}
              placeholder={t('profile.surname')}
            />
            <input
              type="tel"
              name="phone"
              className="input input-bordered w-full"
              value={contactForm.phone}
              onChange={handleContactFormChange}
              placeholder={t('profile.phone')}
            />
            <input
              type="tel"
              name="phone2"
              className="input input-bordered w-full"
              value={contactForm.phone2}
              onChange={handleContactFormChange}
              placeholder={t('profile.phone2')}
            />
            <input
              type="email"
              name="email"
              className="input input-bordered w-full"
              value={contactForm.email}
              onChange={handleContactFormChange}
              placeholder={t('profile.email')}
            />
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                name="is_primary"
                className="checkbox checkbox-sm"
                checked={contactForm.is_primary}
                onChange={handleContactFormChange}
              />
              <span className="label-text">{t('profile.primary_contact')}</span>
            </label>
            <div className="modal-action">
              <button type="button" className="btn btn-ghost" onClick={() => setContactModal(null)}>
                {t('common.cancel')}
              </button>
              <button type="submit" className="btn btn-primary" disabled={contactSaving}>
                {contactSaving ? t('common.loading') : t('common.save')}
              </button>
            </div>
          </form>
        </div>
        <form method="dialog" className="modal-backdrop">
          <button type="button" onClick={() => setContactModal(null)}>{t('common.close')}</button>
        </form>
      </dialog>

      {/* Delete confirmation modals */}
      <ConfirmModal
        open={deleteConfirm?.type === 'address'}
        onClose={() => setDeleteConfirm(null)}
        onConfirm={doDeleteAddress}
        title={t('common.delete')}
        message={t('profile.delete_address_confirm')}
        confirmLabel={t('common.delete')}
        loading={deleteLoading}
        confirmVariant="error"
      />
      <ConfirmModal
        open={deleteConfirm?.type === 'contact'}
        onClose={() => setDeleteConfirm(null)}
        onConfirm={doDeleteContact}
        title={t('common.delete')}
        message={t('profile.delete_contact_confirm')}
        confirmLabel={t('common.delete')}
        loading={deleteLoading}
        confirmVariant="error"
      />
    </div>
  );
}
