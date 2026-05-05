import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { emitAppToast } from '../../toastEvents';

const STATUS_COLORS = {
  pending_review: 'badge-warning',
  approved: 'badge-info',
  refunded: 'badge-success',
  rejected: 'badge-error',
  cancelled: 'badge-neutral',
};

export default function AdminReturnRequestShowPage() {
  const { id } = useParams();
  const { t, i18n } = useTranslation();
  const navigate = useNavigate();

  const [rma, setRma] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [actionModalOpen, setActionModalOpen] = useState(false);
  const [actionType, setActionType] = useState('approve');
  const [adminNotes, setAdminNotes] = useState('');

  const [refundModalOpen, setRefundModalOpen] = useState(false);
  const [refundAmount, setRefundAmount] = useState('');

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const { data } = await api.get(`admin/return-requests/${id}`);
        if (!cancelled && data.success) {
          setRma(data.data);
          setAdminNotes(data.data.admin_notes ?? '');
          if (data.data.payment?.amount != null) {
            setRefundAmount(String(Number(data.data.payment.amount).toFixed(2)));
          }
        }
      } catch (err) {
        if (err.response?.status === 401) navigate('/admin/login');
        if (!cancelled) setError(t('common.error'));
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => { cancelled = true; };
  }, [id, navigate, t]);

  const openActionModal = (type) => {
    setActionType(type);
    setError(null);
    setActionModalOpen(true);
  };

  const handleAction = async (e) => {
    e.preventDefault();
    if (actionType === 'reject' && !adminNotes.trim()) {
      setError(t('admin.returns.notes_required_on_reject'));
      return;
    }
    setSaving(true);
    setError(null);
    try {
      const { data } = await api.put(`admin/return-requests/${id}`, {
        action: actionType,
        admin_notes: adminNotes || null,
      });
      if (data.success) {
        setRma(data.data);
        setActionModalOpen(false);
        emitAppToast(t('admin.returns.action_done'), 'success');
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      setError(err.response?.data?.message || t('common.error'));
    } finally {
      setSaving(false);
    }
  };

  const handleRefund = async (e) => {
    e.preventDefault();
    setSaving(true);
    setError(null);
    try {
      const { data } = await api.post(`admin/return-requests/${id}/refund`, {
        refund_amount: parseFloat(refundAmount),
      });
      if (data.success) {
        setRma(data.data);
        setRefundModalOpen(false);
        emitAppToast(t('admin.returns.action_done'), 'success');
      } else {
        setError(data.message || t('common.error'));
      }
    } catch (err) {
      setError(err.response?.data?.message || t('common.error'));
    } finally {
      setSaving(false);
    }
  };

  if (loading) {
    return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  }
  if (!rma) {
    return <p className="text-error">{error || t('common.error')}</p>;
  }

  const order = rma.order;
  const payment = rma.payment;

  return (
    <div className="space-y-6 max-w-3xl">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.returns.detail_title', { id: rma.id })}</PageTitle>
        <Link to="/admin/returns" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>

      {error && <div role="alert" className="alert alert-error text-sm">{error}</div>}

      {/* Status */}
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body py-4 px-4 sm:px-5">
          <div className="flex flex-wrap items-center gap-4">
            <span className={`badge badge-lg ${STATUS_COLORS[rma.status] ?? 'badge-neutral'}`}>
              {t(`admin.returns.status_${rma.status}`)}
            </span>
            <span className="text-sm text-base-content/60 tabular-nums">
              {rma.created_at ? new Date(rma.created_at).toLocaleString(i18n.language) : ''}
            </span>
            {rma.refunded_at && (
              <span className="text-sm text-base-content/60 tabular-nums">
                {t('admin.returns.refunded_at')}: {new Date(rma.refunded_at).toLocaleString(i18n.language)}
              </span>
            )}
            {rma.gateway_refund_reference && (
              <span className="text-xs text-base-content/50 font-mono">{rma.gateway_refund_reference}</span>
            )}
          </div>
        </div>
      </div>

      {/* Order info */}
      {order && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body py-4 px-4 sm:px-5">
            <h2 className="card-title text-base">{t('admin.returns.order_section')}</h2>
            <dl className="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
              <div>
                <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.id')}</dt>
                <dd className="mt-1">
                  <Link to={`/admin/orders/${order.id}`} className="link link-primary font-medium">
                    #{order.id}
                  </Link>
                </dd>
              </div>
              <div>
                <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.status')}</dt>
                <dd className="mt-1">{order.status}</dd>
              </div>
              {order.client && (
                <div>
                  <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.returns.client_section')}</dt>
                  <dd className="mt-1">
                    <Link to={`/admin/clients/${order.client.id}`} className="link link-primary text-sm">
                      {order.client.login_email}
                    </Link>
                  </dd>
                </div>
              )}
              {order.order_date && (
                <div>
                  <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.order_date')}</dt>
                  <dd className="mt-1 tabular-nums">{new Date(order.order_date).toLocaleDateString(i18n.language)}</dd>
                </div>
              )}
            </dl>

            {order.lines && order.lines.length > 0 && (
              <div className="mt-3 overflow-x-auto">
                <table className="table table-sm">
                  <thead>
                    <tr>
                      <th>{t('admin.orders.line_name')}</th>
                      <th className="text-center">{t('admin.orders.line_quantity')}</th>
                      <th className="text-end">{t('admin.orders.line_total')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {order.lines.map((l) => (
                      <tr key={l.id}>
                        <td>{l.product?.name ?? l.pack?.name ?? ''}</td>
                        <td className="text-center tabular-nums">{l.quantity}</td>
                        <td className="text-end tabular-nums">{l.line_total != null ? Number(l.line_total).toFixed(2) : ''} €</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}
          </div>
        </div>
      )}

      {/* Payment info */}
      {payment && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body py-4 px-4 sm:px-5">
            <h2 className="card-title text-base">{t('admin.returns.payment_section')}</h2>
            <dl className="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
              <div>
                <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.payment_amount')}</dt>
                <dd className="mt-1 tabular-nums font-medium">{Number(payment.amount).toFixed(2)} {payment.currency}</dd>
              </div>
              <div>
                <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.payment_method')}</dt>
                <dd className="mt-1">{t(`admin.orders.payment_${payment.payment_method}`) || payment.payment_method}</dd>
              </div>
              <div>
                <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('admin.orders.payment_status')}</dt>
                <dd className="mt-1">{t(`admin.orders.payment_status_${payment.status}`) || payment.status}</dd>
              </div>
              {rma.refund_amount != null && (
                <div>
                  <dt className="text-xs font-semibold text-base-content/60 uppercase tracking-wide">{t('shop.returns.refund_amount')}</dt>
                  <dd className="mt-1 tabular-nums font-medium text-success">{Number(rma.refund_amount).toFixed(2)} €</dd>
                </div>
              )}
            </dl>
          </div>
        </div>
      )}

      {/* Client reason */}
      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body py-4 px-4 sm:px-5">
          <h2 className="card-title text-base">{t('admin.returns.reason_section')}</h2>
          <p className="text-sm whitespace-pre-wrap">{rma.reason}</p>
          {rma.admin_notes && (
            <div className="mt-3 pt-3 border-t border-base-200">
              <p className="text-xs font-semibold text-base-content/60 uppercase tracking-wide mb-1">{t('admin.returns.admin_notes_label')}</p>
              <p className="text-sm whitespace-pre-wrap">{rma.admin_notes}</p>
            </div>
          )}
        </div>
      </div>

      {/* Actions */}
      {rma.status === 'pending_review' && (
        <div className="flex flex-wrap gap-3 justify-end">
          <button
            type="button"
            className="btn btn-error btn-outline btn-sm"
            onClick={() => openActionModal('reject')}
            disabled={saving}
          >
            {t('admin.returns.reject')}
          </button>
          <button
            type="button"
            className="btn btn-primary btn-sm"
            onClick={() => openActionModal('approve')}
            disabled={saving}
          >
            {t('admin.returns.approve')}
          </button>
        </div>
      )}

      {rma.status === 'approved' && (
        <div className="flex justify-end">
          <button
            type="button"
            className="btn btn-primary btn-sm"
            onClick={() => setRefundModalOpen(true)}
            disabled={saving}
          >
            {t('admin.returns.issue_refund')}
          </button>
        </div>
      )}

      {/* Approve/Reject modal */}
      {actionModalOpen && (
        <div className="modal modal-open">
          <div className="modal-box max-w-md">
            <h3 className="font-bold text-lg mb-4">
              {actionType === 'approve' ? t('admin.returns.approve_modal_title') : t('admin.returns.reject_modal_title')}
            </h3>
            <form onSubmit={handleAction} className="space-y-4">
              <fieldset className="fieldset">
                <legend className="fieldset-legend">{t('admin.returns.admin_notes_label')}</legend>
                <textarea
                  className="textarea w-full min-h-20"
                  placeholder={t('admin.returns.admin_notes_placeholder')}
                  value={adminNotes}
                  onChange={(e) => setAdminNotes(e.target.value)}
                  required={actionType === 'reject'}
                />
              </fieldset>
              {error && <div role="alert" className="alert alert-error text-sm">{error}</div>}
              <div className="modal-action">
                <button
                  type="button"
                  className="btn btn-ghost"
                  onClick={() => { setActionModalOpen(false); setError(null); }}
                  disabled={saving}
                >
                  {t('common.cancel')}
                </button>
                <button
                  type="submit"
                  className={`btn ${actionType === 'approve' ? 'btn-primary' : 'btn-error'}`}
                  disabled={saving}
                >
                  {saving ? <span className="loading loading-spinner loading-sm" /> : (
                    actionType === 'approve' ? t('admin.returns.approve') : t('admin.returns.reject')
                  )}
                </button>
              </div>
            </form>
          </div>
          <div className="modal-backdrop" onClick={() => { setActionModalOpen(false); setError(null); }} />
        </div>
      )}

      {/* Refund modal */}
      {refundModalOpen && (
        <div className="modal modal-open">
          <div className="modal-box max-w-sm">
            <h3 className="font-bold text-lg mb-4">{t('admin.returns.issue_refund')}</h3>
            <form onSubmit={handleRefund} className="space-y-4">
              <fieldset className="fieldset">
                <legend className="fieldset-legend">{t('admin.returns.refund_amount')}</legend>
                <input
                  type="number"
                  className="input w-full"
                  min="0.01"
                  step="0.01"
                  value={refundAmount}
                  onChange={(e) => setRefundAmount(e.target.value)}
                  required
                />
              </fieldset>
              {error && <div role="alert" className="alert alert-error text-sm">{error}</div>}
              <div className="modal-action">
                <button
                  type="button"
                  className="btn btn-ghost"
                  onClick={() => { setRefundModalOpen(false); setError(null); }}
                  disabled={saving}
                >
                  {t('common.cancel')}
                </button>
                <button type="submit" className="btn btn-primary" disabled={saving || !refundAmount}>
                  {saving ? <span className="loading loading-spinner loading-sm" /> : t('admin.returns.refund_confirm')}
                </button>
              </div>
            </form>
          </div>
          <div className="modal-backdrop" onClick={() => { setRefundModalOpen(false); setError(null); }} />
        </div>
      )}
    </div>
  );
}
