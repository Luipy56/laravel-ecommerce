import React, { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import PageTitle from '../components/PageTitle';
import ConfirmModal from '../components/ConfirmModal';
import { useToast } from '../contexts/ToastContext';
import { useAuth } from '../contexts/AuthContext';

function statusBadgeClass(status) {
  switch (status) {
    case 'pending_review': return 'badge-warning';
    case 'reviewed': return 'badge-info';
    case 'client_contacted': return 'badge-success';
    case 'rejected': return 'badge-error';
    case 'completed': return 'badge-success';
    default: return 'badge-ghost';
  }
}

export default function ClientPersonalizedSolutionPage() {
  const { token } = useParams();
  const { t } = useTranslation();
  const { showToast } = useToast();
  const { user } = useAuth();
  const [loading, setLoading] = useState(true);
  const [notFound, setNotFound] = useState(false);
  const [data, setData] = useState(null);
  const [form, setForm] = useState({
    email: '',
    phone: '',
    address_street: '',
    address_city: '',
    address_province: '',
    address_postal_code: '',
    address_note: '',
  });
  const [improvement, setImprovement] = useState('');
  const [saving, setSaving] = useState(false);
  const [improveLoading, setImproveLoading] = useState(false);
  const [deleteOpen, setDeleteOpen] = useState(false);
  const [deleteLoading, setDeleteLoading] = useState(false);

  useEffect(() => {
    if (!token) return;
    let cancelled = false;
    setLoading(true);
    api.get(`public/personalized-solutions/${token}`)
      .then((r) => {
        if (cancelled) return;
        if (r.data.success && r.data.data) {
          const d = r.data.data;
          setData(d);
          setForm({
            email: d.email ?? '',
            phone: d.phone ?? '',
            address_street: d.address_street ?? '',
            address_city: d.address_city ?? '',
            address_province: d.address_province ?? '',
            address_postal_code: d.address_postal_code ?? '',
            address_note: d.address_note ?? '',
          });
          setNotFound(false);
        } else setNotFound(true);
      })
      .catch(() => { if (!cancelled) setNotFound(true); })
      .finally(() => { if (!cancelled) setLoading(false); });
    return () => { cancelled = true; };
  }, [token]);

  const handleSave = async (e) => {
    e.preventDefault();
    if (!token) return;
    setSaving(true);
    try {
      const { data } = await api.patch(`public/personalized-solutions/${token}`, {
        email: form.email.trim(),
        phone: form.phone.trim() || null,
        address_street: form.address_street.trim() || null,
        address_city: form.address_city.trim() || null,
        address_province: form.address_province.trim() || null,
        address_postal_code: form.address_postal_code.trim(),
        address_note: form.address_note.trim() || null,
      });
      if (data.success && data.data) {
        setData(data.data);
        showToast({ message: t('common.success'), type: 'success' });
      }
    } catch (err) {
      showToast({ message: err.response?.data?.message || t('common.error'), type: 'error' });
    } finally {
      setSaving(false);
    }
  };

  const handleImprovement = async (e) => {
    e.preventDefault();
    if (!token || !improvement.trim()) return;
    setImproveLoading(true);
    try {
      const { data } = await api.post(`public/personalized-solutions/${token}/request-improvements`, {
        message: improvement.trim(),
      });
      if (data.success && data.data) {
        setData(data.data);
        setImprovement('');
        showToast({ message: t('common.success'), type: 'success' });
      }
    } catch (err) {
      showToast({ message: err.response?.data?.message || t('common.error'), type: 'error' });
    } finally {
      setImproveLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!token) return;
    setDeleteLoading(true);
    try {
      await api.delete(`public/personalized-solutions/${token}`);
      showToast({ message: t('shop.client_portal.deleted'), type: 'success' });
      setDeleteOpen(false);
      setNotFound(true);
      setData(null);
    } catch (err) {
      showToast({ message: err.response?.data?.message || t('common.error'), type: 'error' });
    } finally {
      setDeleteLoading(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  if (notFound || !data) {
    return (
      <div className="mx-auto w-full min-w-0 max-w-3xl py-8">
        <PageTitle>{t('shop.client_portal.title')}</PageTitle>
        <p>{t('shop.client_portal.not_found')}</p>
        <Link to="/" className="btn btn-primary mt-4">{t('shop.home')}</Link>
      </div>
    );
  }

  const statusKey = `admin.personalized_solutions.status_${data.status}`;
  const statusLabel = t(statusKey) !== statusKey ? t(statusKey) : data.status;
  const order = data.order;
  const showPay = order?.can_pay && order?.has_payment === false;

  return (
    <div className="mx-auto w-full min-w-0 max-w-3xl space-y-6 pb-12">
      <PageTitle>{t('shop.client_portal.title')}</PageTitle>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-3">
          <div className="flex flex-wrap items-center gap-2">
            <span className={`badge ${statusBadgeClass(data.status)}`}>{statusLabel}</span>
            {data.iterations_count > 0 && (
              <span className="text-sm text-base-content/70">
                {t('shop.client_portal.iterations')}: {data.iterations_count}
              </span>
            )}
          </div>
          {data.problem_description && (
            <section>
              <h2 className="text-sm font-semibold text-base-content/70">{t('shop.custom_solution.description')}</h2>
              <p className="whitespace-pre-wrap">{data.problem_description}</p>
            </section>
          )}
          {data.resolution && (
            <section>
              <h2 className="text-sm font-semibold text-base-content/70">{t('shop.client_portal.resolution')}</h2>
              <p className="whitespace-pre-wrap">{data.resolution}</p>
            </section>
          )}
          {data.improvement_feedback && (
            <section className="text-sm">
              <p className="font-semibold text-base-content/70">{t('admin.personalized_solutions.improvement_feedback')}</p>
              <p className="whitespace-pre-wrap">{data.improvement_feedback}</p>
            </section>
          )}
        </div>
      </div>

      {showPay && order && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body space-y-3">
            <h2 className="card-title text-base">{t('shop.client_portal.pay_title')}</h2>
            <p className="tabular-nums font-semibold">{Number(order.grand_total ?? 0).toFixed(2)} €</p>
            {user ? (
              <Link to={order.pay_path || `/orders/${order.id}`} className="btn btn-primary btn-sm sm:btn-md w-fit">
                {t('shop.client_portal.pay_cta')}
              </Link>
            ) : (
              <>
                <p className="text-sm text-base-content/80">{t('shop.client_portal.pay_login_hint')}</p>
                <Link
                  to={`/login?next=${encodeURIComponent(order.pay_path || `/orders/${order.id}`)}`}
                  className="btn btn-primary btn-sm sm:btn-md w-fit"
                >
                  {t('auth.login')}
                </Link>
              </>
            )}
          </div>
        </div>
      )}

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-base">{t('shop.client_portal.contact_title')}</h2>
          <form onSubmit={handleSave} className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <label className="form-field sm:col-span-2">
              <span className="form-label">{t('auth.email')} *</span>
              <input type="email" className="input input-bordered w-full" value={form.email} onChange={(e) => setForm((f) => ({ ...f, email: e.target.value }))} required />
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.phone')}</span>
              <input type="text" className="input input-bordered w-full" value={form.phone} onChange={(e) => setForm((f) => ({ ...f, phone: e.target.value }))} />
            </label>
            <label className="form-field sm:col-span-2">
              <span className="form-label">{t('admin.personalized_solutions.address_street')}</span>
              <input type="text" className="input input-bordered w-full" value={form.address_street} onChange={(e) => setForm((f) => ({ ...f, address_street: e.target.value }))} />
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.address_city')}</span>
              <input type="text" className="input input-bordered w-full" value={form.address_city} onChange={(e) => setForm((f) => ({ ...f, address_city: e.target.value }))} />
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.address_province')}</span>
              <input type="text" className="input input-bordered w-full" value={form.address_province} onChange={(e) => setForm((f) => ({ ...f, address_province: e.target.value }))} />
            </label>
            <label className="form-field">
              <span className="form-label">{t('admin.personalized_solutions.address_postal_code')} *</span>
              <input type="text" className="input input-bordered w-full" value={form.address_postal_code} onChange={(e) => setForm((f) => ({ ...f, address_postal_code: e.target.value }))} required />
            </label>
            <label className="form-field sm:col-span-2">
              <span className="form-label">{t('shop.custom_solution.address_note')}</span>
              <textarea className="textarea textarea-bordered w-full min-h-[80px]" value={form.address_note} onChange={(e) => setForm((f) => ({ ...f, address_note: e.target.value }))} />
            </label>
            <div className="sm:col-span-2 flex justify-end">
              <button type="submit" className="btn btn-primary btn-sm sm:btn-md" disabled={saving}>
                {saving ? t('common.loading') : t('shop.client_portal.save')}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-3">
          <h2 className="card-title text-base">{t('shop.client_portal.improvement_title')}</h2>
          <p className="text-sm text-base-content/80">{t('shop.client_portal.improvement_hint')}</p>
          <form onSubmit={handleImprovement} className="space-y-3">
            <textarea
              className="textarea textarea-bordered w-full min-h-[120px]"
              value={improvement}
              onChange={(e) => setImprovement(e.target.value)}
            />
            <div className="flex justify-end">
              <button type="submit" className="btn btn-outline btn-sm sm:btn-md" disabled={improveLoading || !improvement.trim()}>
                {improveLoading ? t('common.loading') : t('shop.client_portal.improvement_submit')}
              </button>
            </div>
          </form>
        </div>
      </div>

      <div className="flex flex-wrap gap-2 justify-between items-center">
        <button type="button" className="btn btn-error btn-outline btn-sm sm:btn-md" onClick={() => setDeleteOpen(true)}>
          {t('shop.client_portal.delete_title')}
        </button>
        <Link to="/custom-solution" className="btn btn-ghost btn-sm sm:btn-md">{t('shop.custom_solution')}</Link>
      </div>

      <ConfirmModal
        open={deleteOpen}
        title={t('shop.client_portal.delete_title')}
        message={t('shop.client_portal.delete_confirm')}
        confirmLabel={t('common.delete')}
        loading={deleteLoading}
        confirmVariant="error"
        onConfirm={handleDelete}
        onClose={() => !deleteLoading && setDeleteOpen(false)}
      />
    </div>
  );
}
