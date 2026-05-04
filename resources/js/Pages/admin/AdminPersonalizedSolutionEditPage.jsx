import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import { sanitizePostalCodeDigits } from '../../lib/postalInput';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

const STATUSES = ['pending_review', 'reviewed', 'client_contacted', 'rejected', 'completed'];

export default function AdminPersonalizedSolutionEditPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [form, setForm] = useState({
    email: '',
    phone: '',
    address_street: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
    address_note: '',
    problem_description: '',
    resolution: '',
    status: 'pending_review',
    client_id: '',
    order_id: '',
    is_active: true,
    clearImprovementFeedback: false,
  });
  const [clients, setClients] = useState([]);
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(false);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [submitError, setSubmitError] = useState('');

  const update = (key, value) => setForm((prev) => ({ ...prev, [key]: value }));

  const fetchSolution = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/personalized-solutions/${id}`);
      if (data.success && data.data) {
        const d = data.data;
        setForm({
          email: d.email ?? '',
          phone: d.phone ?? '',
          address_street: d.address_street ?? '',
          address_city: d.address_city ?? '',
          address_province: d.address_province ?? '',
          address_postal_code: d.address_postal_code ?? '',
          address_note: d.address_note ?? '',
          problem_description: d.problem_description ?? '',
          resolution: d.resolution ?? '',
          status: d.status || 'pending_review',
          client_id: d.client_id ? String(d.client_id) : '',
          order_id: d.order_id ? String(d.order_id) : '',
          is_active: !!d.is_active,
          clearImprovementFeedback: false,
        });
      } else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  const fetchClients = useCallback(async () => {
    try {
      const { data } = await api.get('admin/clients', { params: { per_page: 500 } });
      if (data.success) setClients(data.data || []);
    } catch {
      setClients([]);
    }
  }, []);

  const fetchOrders = useCallback(async () => {
    try {
      const { data } = await api.get('admin/orders', { params: { kind: 'order', per_page: 500 } });
      if (data.success) setOrders(data.data || []);
    } catch {
      setOrders([]);
    }
  }, []);

  useEffect(() => {
    fetchSolution();
    fetchClients();
    fetchOrders();
  }, [fetchSolution, fetchClients, fetchOrders]);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitError('');
    setLoading(true);
    try {
      const payload = {
        email: form.email.trim() || null,
        phone: form.phone.trim() || null,
        address_street: form.address_street.trim() || null,
        address_city: form.address_city.trim() || null,
        address_province: form.address_province.trim() || null,
        address_postal_code: form.address_postal_code.trim() || null,
        address_note: form.address_note.trim() || null,
        problem_description: form.problem_description.trim() || null,
        resolution: form.resolution.trim() || null,
        status: form.status,
        client_id: form.client_id ? Number(form.client_id) : null,
        order_id: form.order_id ? Number(form.order_id) : null,
        is_active: form.is_active,
        clear_improvement_feedback: form.clearImprovementFeedback,
      };
      const { data } = await api.put(`admin/personalized-solutions/${id}`, payload);
      if (data.success) {
        showSuccess(t('common.saved'));
        navigate(`/admin/personalized-solutions/${id}`);
      } else setSubmitError(data.message || t('common.error'));
    } catch (err) {
      const msg = err.response?.data?.message || err.response?.data?.errors?.status?.[0] || t('common.error');
      setSubmitError(msg);
    } finally {
      setLoading(false);
    }
  };

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/personalized-solutions" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
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
        <PageTitle className="mb-0">{t('admin.personalized_solutions.edit')} #{id}</PageTitle>
        <Link to={`/admin/personalized-solutions/${id}`} className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <form onSubmit={handleSubmit} className="space-y-6">
            {submitError && (
              <div role="alert" className="alert alert-error text-sm">{submitError}</div>
            )}

            <section>
              <h2 className="text-lg font-semibold mb-3">{t('admin.personalized_solutions.contact')}</h2>
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label className="form-field sm:col-span-2">
                  <span className="form-label">{t('admin.personalized_solutions.email')}</span>
                  <input
                    type="email"
                    className="input input-bordered w-full"
                    value={form.email}
                    onChange={(e) => update('email', e.target.value)}
                    aria-label={t('admin.personalized_solutions.email')}
                  />
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.phone')}</span>
                  <input
                    type="text"
                    className="input input-bordered w-full"
                    value={form.phone}
                    onChange={(e) => update('phone', e.target.value)}
                    aria-label={t('admin.personalized_solutions.phone')}
                  />
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.client')}</span>
                  <select
                    className="select select-bordered w-full"
                    value={form.client_id}
                    onChange={(e) => update('client_id', e.target.value)}
                    aria-label={t('admin.personalized_solutions.client')}
                  >
                    <option value="">{t('common.none')}</option>
                    {clients.map((c) => (
                      <option key={c.id} value={c.id}>{c.login_email}{c.primary_contact_name ? ` (${c.primary_contact_name})` : ''}</option>
                    ))}
                  </select>
                </label>
                <label className="form-field sm:col-span-2">
                  <span className="form-label">{t('admin.personalized_solutions.order')}</span>
                  <select
                    className="select select-bordered w-full"
                    value={form.order_id}
                    onChange={(e) => update('order_id', e.target.value)}
                    aria-label={t('admin.personalized_solutions.order')}
                  >
                    <option value="">{t('common.none')}</option>
                    {orders.map((o) => (
                      <option key={o.id} value={o.id}>#{o.id} {o.client_login_email ? ` · ${o.client_login_email}` : ''}</option>
                    ))}
                  </select>
                </label>
              </div>
            </section>

            <section>
              <h2 className="text-lg font-semibold mb-3">{t('admin.personalized_solutions.address')}</h2>
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <label className="form-field sm:col-span-2">
                  <span className="form-label">{t('admin.personalized_solutions.address_street')}</span>
                  <input
                    type="text"
                    className="input input-bordered w-full"
                    value={form.address_street}
                    onChange={(e) => update('address_street', e.target.value)}
                    aria-label={t('admin.personalized_solutions.address_street')}
                  />
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.address_city')}</span>
                  <input
                    type="text"
                    className="input input-bordered w-full"
                    value={form.address_city}
                    onChange={(e) => update('address_city', e.target.value)}
                    aria-label={t('admin.personalized_solutions.address_city')}
                  />
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.address_province')}</span>
                  <input
                    type="text"
                    className="input input-bordered w-full"
                    value={form.address_province}
                    onChange={(e) => update('address_province', e.target.value)}
                    aria-label={t('admin.personalized_solutions.address_province')}
                  />
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.address_postal_code')} *</span>
                  <input
                    type="text"
                    inputMode="numeric"
                    autoComplete="postal-code"
                    pattern="[0-9]*"
                    className="input input-bordered w-full"
                    value={form.address_postal_code}
                    onChange={(e) => update('address_postal_code', sanitizePostalCodeDigits(e.target.value))}
                    aria-label={t('admin.personalized_solutions.address_postal_code')}
                    required
                  />
                </label>
                <label className="form-field sm:col-span-2">
                  <span className="form-label">{t('shop.custom_solution.address_note')}</span>
                  <input
                    type="text"
                    className="input input-bordered w-full"
                    value={form.address_note}
                    onChange={(e) => update('address_note', e.target.value)}
                    aria-label={t('shop.custom_solution.address_note')}
                  />
                </label>
              </div>
            </section>

            <section>
              <h2 className="text-lg font-semibold mb-3">{t('admin.personalized_solutions.problem_description')}</h2>
              <label className="form-field">
                <textarea
                  className="textarea textarea-bordered w-full min-h-24"
                  value={form.problem_description}
                  onChange={(e) => update('problem_description', e.target.value)}
                  aria-label={t('admin.personalized_solutions.problem_description')}
                />
              </label>
            </section>

            <section>
              <h2 className="text-lg font-semibold mb-3">{t('admin.personalized_solutions.admin_response')}</h2>
              <div className="space-y-4">
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.status')} *</span>
                  <select
                    className="select select-bordered w-full max-w-xs"
                    value={form.status}
                    onChange={(e) => update('status', e.target.value)}
                    required
                    aria-label={t('admin.personalized_solutions.status')}
                  >
                    {STATUSES.map((s) => (
                      <option key={s} value={s}>{t(`admin.personalized_solutions.status_${s}`)}</option>
                    ))}
                  </select>
                </label>
                <label className="form-field">
                  <span className="form-label">{t('admin.personalized_solutions.resolution')}</span>
                  <textarea
                    className="textarea textarea-bordered w-full min-h-32"
                    value={form.resolution}
                    onChange={(e) => update('resolution', e.target.value)}
                    placeholder={t('admin.personalized_solutions.resolution_placeholder')}
                    aria-label={t('admin.personalized_solutions.resolution')}
                  />
                </label>
              </div>
            </section>

            <label className="label cursor-pointer gap-2 justify-start">
              <input
                type="checkbox"
                className="checkbox checkbox-sm"
                checked={form.clearImprovementFeedback}
                onChange={(e) => update('clearImprovementFeedback', e.target.checked)}
              />
              <span className="label-text">{t('admin.personalized_solutions.clear_improvement')}</span>
            </label>

            <label className="label cursor-pointer gap-2">
              <input
                type="checkbox"
                className="checkbox checkbox-sm"
                checked={form.is_active}
                onChange={(e) => update('is_active', e.target.checked)}
              />
              <span className="label-text">{t('admin.products.is_active')}</span>
            </label>

            <div className="flex justify-between gap-2 pt-4 border-t border-base-200">
              <Link to={`/admin/personalized-solutions/${id}`} className="btn btn-ghost">{t('common.back')}</Link>
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
