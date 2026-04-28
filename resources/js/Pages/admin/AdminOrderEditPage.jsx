import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

const STATUSES = ['pending', 'awaiting_payment', 'awaiting_installation_price', 'in_transit', 'sent', 'installation_pending', 'installation_confirmed'];
const INSTALL_STATUSES = ['pending', 'priced', 'rejected'];

function formatDateForInput(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${y}-${m}-${day}`;
}

export default function AdminOrderEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [status, setStatus] = useState('pending');
  const [shippingDate, setShippingDate] = useState('');
  const [installationRequested, setInstallationRequested] = useState(false);
  const [installationPrice, setInstallationPrice] = useState('');
  const [installationStatus, setInstallationStatus] = useState('pending');
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const fetchOrder = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/orders/${id}`);
      if (data.success && data.data) {
        const o = data.data;
        if (o.kind !== 'order') {
          setLoadError(t('admin.orders.edit_only_confirmed'));
          setLoaded(true);
          return;
        }
        setStatus(o.status || 'pending');
        setShippingDate(formatDateForInput(o.shipping_date));
        setInstallationRequested(!!o.installation_requested);
        setInstallationPrice(o.installation_price != null ? String(o.installation_price) : '');
        setInstallationStatus(o.installation_status || 'pending');
      } else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchOrder();
  }, [fetchOrder]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError('');
    setLoading(true);
    try {
      const payload = {
        status,
        shipping_date: shippingDate.trim() || null,
      };
      if (installationRequested) {
        payload.installation_price = installationPrice.trim() === '' ? null : parseFloat(installationPrice);
        payload.installation_status = installationStatus;
      }
      const { data } = await api.put(`admin/orders/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate(`/admin/orders/${id}`);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      setSubmitError(err.response?.data?.message || err.response?.data?.errors?.status?.[0] || t('common.error'));
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/orders" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-warning">{loadError}</div>
        <Link to={`/admin/orders/${id}`} className="btn btn-ghost btn-sm">{t('admin.orders.view_order')}</Link>
      </div>
    );
  }

  if (!loaded) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.orders.edit')} #{id}</PageTitle>
        <Link to={`/admin/orders/${id}`} className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {submitError && (
              <div role="alert" className="alert alert-error text-sm">{submitError}</div>
            )}
            <label className="form-field">
              <span className="form-label">{t('admin.orders.status')} *</span>
              <select
                className="select select-bordered w-full"
                value={status}
                onChange={(e) => setStatus(e.target.value)}
                required
                aria-label={t('admin.orders.status')}
              >
                {STATUSES.map((s) => (
                  <option key={s} value={s}>{t(`admin.orders.status_${s}`)}</option>
                ))}
              </select>
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.orders.shipping_date')}</span>
              <input
                type="date"
                className="input input-bordered w-full"
                value={shippingDate}
                onChange={(e) => setShippingDate(e.target.value)}
                aria-label={t('admin.orders.shipping_date')}
              />
            </label>
            <p className="text-sm text-base-content/70">{t('admin.orders.shipping_fixed_hint')}</p>

            {installationRequested && (
              <div className="space-y-4 pt-2 border-t border-base-200">
                <p className="text-sm font-semibold text-base-content">{t('admin.orders.installation')}</p>
                <label className="form-field">
                  <span className="form-label">{t('admin.orders.installation_status_label')}</span>
                  <select
                    className="select select-bordered w-full max-w-md"
                    value={installationStatus}
                    onChange={(e) => setInstallationStatus(e.target.value)}
                    aria-label={t('admin.orders.installation_status_label')}
                  >
                    {INSTALL_STATUSES.map((s) => (
                      <option key={s} value={s}>{t(`admin.orders.install_status_${s}`)}</option>
                    ))}
                  </select>
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.orders.installation_fee_label')} (€)</span>
                  <input
                    type="number"
                    step="0.01"
                    min="0"
                    className="input input-bordered w-full max-w-xs"
                    value={installationPrice}
                    onChange={(e) => setInstallationPrice(e.target.value)}
                    placeholder="0.00"
                    aria-label={t('admin.orders.installation_fee_label')}
                  />
                </label>
              </div>
            )}

            <div className="flex justify-between gap-2 pt-4">
              <Link to={`/admin/orders/${id}`} className="btn btn-ghost">{t('common.back')}</Link>
              <button type="submit" className="btn btn-primary" disabled={loading}>
                {loading ? t('common.loading') : t('common.save')}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
