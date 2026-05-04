import React, { useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import StarRating from '../../components/StarRating';
import { useAdminToast } from '../../contexts/AdminToastContext';

const STATUS_COLORS = {
  pending: 'badge-warning',
  approved: 'badge-success',
  rejected: 'badge-error',
};

export default function AdminReviewShowPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();

  const [review, setReview] = useState(null);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState(null);

  const [rejectModalOpen, setRejectModalOpen] = useState(false);
  const [adminNote, setAdminNote] = useState('');
  const [deleteConfirmOpen, setDeleteConfirmOpen] = useState(false);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const { data } = await api.get(`admin/reviews/${id}`);
        if (!cancelled && data.success) {
          setReview(data.data);
          setAdminNote(data.data.admin_note ?? '');
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

  const updateStatus = async (status, note) => {
    setSaving(true);
    setError(null);
    try {
      const { data } = await api.patch(`admin/reviews/${id}`, {
        status,
        admin_note: note ?? null,
      });
      if (data.success) {
        setReview(data.data);
        setAdminNote(data.data.admin_note ?? '');
        showSuccess(t('common.saved'));
        setRejectModalOpen(false);
      }
    } catch (err) {
      setError(err?.response?.data?.message ?? t('common.error'));
    } finally {
      setSaving(false);
    }
  };

  const handleDelete = async () => {
    setSaving(true);
    try {
      await api.delete(`admin/reviews/${id}`);
      navigate('/admin/reviews');
    } catch (err) {
      setError(err?.response?.data?.message ?? t('common.error'));
      setSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  if (!review) {
    return (
      <div className="space-y-4">
        <Link to="/admin/reviews" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        <p className="text-error">{error ?? t('common.error')}</p>
      </div>
    );
  }

  const dateStr = review.created_at
    ? new Date(review.created_at).toLocaleDateString('ca-ES', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '';

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">{t('admin.reviews.detail_title')}</PageTitle>
        <Link to="/admin/reviews" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
      </div>

      {error && <div role="alert" className="alert alert-error alert-soft">{error}</div>}

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-4">
          {/* Status badge */}
          <div className="flex flex-wrap items-center gap-3">
            <span className={`badge badge-soft badge-md ${STATUS_COLORS[review.status] ?? 'badge-ghost'}`}>
              {t(`admin.reviews.status_${review.status}`)}
            </span>
            {review.verified_purchase && (
              <span className="badge badge-success badge-soft badge-sm">{t('shop.reviews.verified_purchase')}</span>
            )}
          </div>

          {/* Product */}
          <div>
            <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.column_product')}</span>
            <p className="mt-0.5 font-medium">
              {review.product ? (
                <Link to={`/admin/products/${review.product.id}`} className="link link-primary">
                  {review.product.name}
                  {review.product.code && <span className="text-base-content/50 ml-1 font-normal">({review.product.code})</span>}
                </Link>
              ) : review.product_id}
            </p>
          </div>

          {/* Client */}
          <div>
            <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.column_client')}</span>
            <p className="mt-0.5">
              {review.client_name && <span className="font-medium">{review.client_name} · </span>}
              {review.client_email && (
                <Link to={`/admin/clients/${review.client_id}`} className="link link-primary text-sm">
                  {review.client_email}
                </Link>
              )}
            </p>
          </div>

          {/* Order */}
          {review.order_id && (
            <div>
              <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.column_order')}</span>
              <p className="mt-0.5">
                <Link to={`/admin/orders/${review.order_id}`} className="link link-primary text-sm">
                  #{review.order_id}
                </Link>
              </p>
            </div>
          )}

          {/* Rating */}
          <div>
            <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.column_rating')}</span>
            <div className="mt-1 flex items-center gap-2">
              <StarRating value={review.rating} size="md" />
              <span className="text-sm text-base-content/70">({review.rating}/5)</span>
            </div>
          </div>

          {/* Comment */}
          <div>
            <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.column_comment')}</span>
            <p className="mt-1 text-sm text-base-content/80 whitespace-pre-wrap">
              {review.comment || <span className="italic text-base-content/40">{t('admin.reviews.no_comment')}</span>}
            </p>
          </div>

          {/* Date */}
          <div>
            <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.common.column_date')}</span>
            <p className="mt-0.5 text-sm text-base-content/70">{dateStr}</p>
          </div>

          {/* Admin note */}
          {review.admin_note && (
            <div>
              <span className="text-xs font-semibold uppercase tracking-wide text-base-content/50">{t('admin.reviews.admin_note')}</span>
              <p className="mt-0.5 text-sm text-base-content/70 italic">{review.admin_note}</p>
            </div>
          )}
        </div>
      </div>

      {/* Action buttons */}
      <div className="flex flex-wrap gap-3">
        {review.status !== 'approved' && (
          <button
            type="button"
            className="btn btn-primary"
            disabled={saving}
            onClick={() => updateStatus('approved', null)}
          >
            {saving && <span className="loading loading-spinner loading-sm" />}
            {t('admin.reviews.approve')}
          </button>
        )}
        {review.status !== 'rejected' && (
          <button
            type="button"
            className="btn btn-error btn-outline"
            disabled={saving}
            onClick={() => setRejectModalOpen(true)}
          >
            {t('admin.reviews.reject')}
          </button>
        )}
        {review.status !== 'pending' && (
          <button
            type="button"
            className="btn btn-ghost"
            disabled={saving}
            onClick={() => updateStatus('pending', null)}
          >
            {t('admin.reviews.reset_pending')}
          </button>
        )}
        <button
          type="button"
          className="btn btn-ghost text-error ml-auto"
          disabled={saving}
          onClick={() => setDeleteConfirmOpen(true)}
        >
          {t('common.delete')}
        </button>
      </div>

      {/* Reject modal */}
      {rejectModalOpen && (
        <dialog className="modal modal-open">
          <div className="modal-box">
            <h3 className="font-bold text-lg mb-3">{t('admin.reviews.reject')}</h3>
            <div className="space-y-3">
              <div>
                <label className="text-sm font-medium" htmlFor="admin-note-input">
                  {t('admin.reviews.admin_note')} <span className="text-base-content/50 font-normal">({t('common.optional')})</span>
                </label>
                <textarea
                  id="admin-note-input"
                  className="textarea textarea-bordered w-full mt-1"
                  rows={3}
                  maxLength={500}
                  value={adminNote}
                  onChange={(e) => setAdminNote(e.target.value)}
                  placeholder={t('admin.reviews.admin_note_placeholder')}
                />
              </div>
            </div>
            <div className="modal-action">
              <button type="button" className="btn btn-ghost" onClick={() => setRejectModalOpen(false)}>
                {t('common.cancel')}
              </button>
              <button
                type="button"
                className="btn btn-error"
                disabled={saving}
                onClick={() => updateStatus('rejected', adminNote.trim() || null)}
              >
                {saving && <span className="loading loading-spinner loading-sm" />}
                {t('admin.reviews.reject')}
              </button>
            </div>
          </div>
          <form method="dialog" className="modal-backdrop">
            <button type="button" onClick={() => setRejectModalOpen(false)}>close</button>
          </form>
        </dialog>
      )}

      {/* Delete confirm modal */}
      {deleteConfirmOpen && (
        <dialog className="modal modal-open">
          <div className="modal-box">
            <h3 className="font-bold text-lg mb-2">{t('common.delete')}</h3>
            <p className="text-sm text-base-content/70">{t('admin.reviews.delete_confirm')}</p>
            <div className="modal-action">
              <button type="button" className="btn btn-ghost" onClick={() => setDeleteConfirmOpen(false)}>
                {t('common.cancel')}
              </button>
              <button
                type="button"
                className="btn btn-error"
                disabled={saving}
                onClick={handleDelete}
              >
                {saving && <span className="loading loading-spinner loading-sm" />}
                {t('common.delete')}
              </button>
            </div>
          </div>
          <form method="dialog" className="modal-backdrop">
            <button type="button" onClick={() => setDeleteConfirmOpen(false)}>close</button>
          </form>
        </dialog>
      )}
    </div>
  );
}
