import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';
import { useToast } from '../../contexts/ToastContext';

const IMAGE_EXTENSIONS = /\.(jpe?g|png|gif|webp|bmp|svg)$/i;

const STATUSES = ['pending_review', 'reviewed', 'client_contacted', 'rejected', 'completed'];

function getStatusBadgeClass(status) {
  switch (status) {
    case 'pending_review': return 'badge-warning';
    case 'reviewed': return 'badge-info';
    case 'client_contacted': return 'badge-success';
    case 'rejected': return 'badge-error';
    case 'completed': return 'badge-success';
    default: return 'badge-ghost';
  }
}

function isImageAttachment(attachment) {
  const name = attachment.original_filename || '';
  return IMAGE_EXTENSIONS.test(name) || (attachment.content_type && attachment.content_type.startsWith('image/'));
}

export default function AdminPersonalizedSolutionShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { showToast } = useToast();
  const { id } = useParams();
  const [solution, setSolution] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [notifyLoading, setNotifyLoading] = useState(false);
  const [resolutionSaveLoading, setResolutionSaveLoading] = useState(false);
  const [draftResolution, setDraftResolution] = useState('');
  const [draftStatus, setDraftStatus] = useState('pending_review');
  const resolutionDialogRef = useRef(null);
  const notifyConfirmDialogRef = useRef(null);

  const fetchSolution = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/personalized-solutions/${id}`);
      if (data.success) setSolution(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchSolution();
  }, [fetchSolution]);

  const openResolutionModal = () => {
    if (!solution) return;
    setDraftResolution(solution.resolution ?? '');
    setDraftStatus(solution.status || 'pending_review');
    resolutionDialogRef.current?.showModal();
  };

  const closeResolutionModal = () => {
    resolutionDialogRef.current?.close();
  };

  const openNotifyConfirmModal = () => {
    if (!solution?.email) return;
    notifyConfirmDialogRef.current?.showModal();
  };

  const confirmAndNotifyResolution = () => {
    notifyConfirmDialogRef.current?.close();
    void handleNotifyResolution();
  };

  const handleNotifyResolution = async () => {
    if (!id) return;
    setNotifyLoading(true);
    try {
      const { data } = await api.post(`admin/personalized-solutions/${id}/notify-resolution`);
      if (data.success) {
        showSuccess(t('admin.personalized_solutions.resend_resolution_done'));
      } else showToast({ message: data.message || t('common.error'), type: 'error' });
    } catch (err) {
      showToast({ message: err.response?.data?.message || t('common.error'), type: 'error' });
    } finally {
      setNotifyLoading(false);
    }
  };

  const handleSaveResolutionModal = async () => {
    if (!id) return;
    setResolutionSaveLoading(true);
    try {
      const { data } = await api.patch(`admin/personalized-solutions/${id}/resolution`, {
        resolution: draftResolution.trim() || null,
        status: draftStatus,
      });
      if (data.success && data.data) {
        setSolution(data.data);
        showSuccess(t('admin.personalized_solutions.resolution_saved'));
        closeResolutionModal();
      } else {
        showToast({ message: data.message || t('common.error'), type: 'error' });
      }
    } catch (err) {
      showToast({ message: err.response?.data?.message || t('common.error'), type: 'error' });
    } finally {
      setResolutionSaveLoading(false);
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

  if (!loaded || !solution) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  const hasAddress = solution.address_street || solution.address_city || solution.address_postal_code;

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">
          {t('admin.personalized_solutions.title')} #{solution.id}
        </PageTitle>
        <div className="flex flex-wrap gap-2 justify-end">
          <Link to="/admin/personalized-solutions" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <button
            type="button"
            className="btn btn-primary btn-sm shrink-0"
            onClick={openResolutionModal}
          >
            {t('admin.personalized_solutions.resolution_modal_open')}
          </button>
          <button
            type="button"
            className="btn btn-outline btn-sm shrink-0"
            disabled={notifyLoading || !solution.email}
            onClick={openNotifyConfirmModal}
            title={t('admin.personalized_solutions.email_client_title')}
          >
            {notifyLoading ? t('common.loading') : t('admin.personalized_solutions.email_client_short')}
          </button>
          <Link to={`/admin/personalized-solutions/${id}/edit`} className="btn btn-ghost btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

      <dialog ref={notifyConfirmDialogRef} id="admin-sp-notify-confirm-modal" className="modal" aria-labelledby="admin-sp-notify-confirm-title">
        <div className="modal-box max-w-md">
          <h2 id="admin-sp-notify-confirm-title" className="font-semibold text-lg mb-2">
            {t('admin.personalized_solutions.email_client_confirm_title')}
          </h2>
          <p className="text-sm text-base-content/80 mb-4">
            {t('admin.personalized_solutions.email_client_confirm_body')}
          </p>
          <div className="modal-action flex flex-wrap items-center justify-between gap-2">
            <form method="dialog">
              <button type="submit" className="btn btn-ghost">{t('common.cancel')}</button>
            </form>
            <button
              type="button"
              className="btn btn-primary"
              disabled={notifyLoading}
              onClick={confirmAndNotifyResolution}
            >
              {notifyLoading ? t('common.loading') : t('common.confirm')}
            </button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop">
          <button type="submit" className="btn" aria-label={t('common.close')}>{t('common.close')}</button>
        </form>
      </dialog>

      <dialog ref={resolutionDialogRef} id="admin-sp-resolution-modal" className="modal">
        <div className="modal-box max-w-2xl">
          <h2 className="font-semibold text-lg mb-4 whitespace-pre-line leading-snug">
            {t('admin.personalized_solutions.resolution_modal_title')}
          </h2>
          <div className="space-y-4">
            <label className="form-control w-full max-w-xs">
              <span className="label-text">{t('admin.personalized_solutions.status')}</span>
              <select
                className="select select-bordered select-sm w-full min-w-0"
                value={draftStatus}
                onChange={(e) => setDraftStatus(e.target.value)}
                aria-label={t('admin.personalized_solutions.status')}
              >
                {STATUSES.map((s) => (
                  <option key={s} value={s}>{t(`admin.personalized_solutions.status_${s}`)}</option>
                ))}
              </select>
            </label>
            <label className="form-control w-full">
              <span className="label-text">{t('admin.personalized_solutions.resolution')}</span>
              <textarea
                className="textarea textarea-bordered w-full min-h-40"
                value={draftResolution}
                onChange={(e) => setDraftResolution(e.target.value)}
                placeholder={t('admin.personalized_solutions.resolution_placeholder')}
                aria-label={t('admin.personalized_solutions.resolution')}
              />
            </label>
          </div>
          <div className="modal-action flex flex-wrap items-center justify-between gap-2">
            <form method="dialog">
              <button type="submit" className="btn btn-ghost">{t('common.close')}</button>
            </form>
            <button
              type="button"
              className="btn btn-primary"
              disabled={resolutionSaveLoading}
              onClick={handleSaveResolutionModal}
            >
              {resolutionSaveLoading ? t('common.loading') : t('common.save')}
            </button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop">
          <button type="submit" className="btn">{t('common.close')}</button>
        </form>
      </dialog>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-6">
          <section>
            <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.contact')}</h2>
            <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
              <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.email')}</dt><dd>{solution.email ?? ''}</dd></div>
              <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.phone')}</dt><dd>{solution.phone ?? ''}</dd></div>
              {solution.client && (
                <>
                  <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.client')}</dt><dd><Link to={`/admin/clients/${solution.client.id}`} className="link link-hover">{solution.client.login_email}</Link></dd></div>
                  {solution.client.identification && <div><dt className="text-sm text-base-content/70">{t('admin.clients.identification')}</dt><dd>{solution.client.identification}</dd></div>}
                </>
              )}
              {solution.order && (
                <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.order')}</dt><dd>{solution.order.reference}</dd></div>
              )}
              {solution.portal_url && (
                <div className="sm:col-span-2">
                  <dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.portal_url')}</dt>
                  <dd><a href={solution.portal_url} className="link link-hover break-all" target="_blank" rel="noopener noreferrer">{solution.portal_url}</a></dd>
                </div>
              )}
            </dl>
          </section>

          {hasAddress && (
            <section>
              <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.address')}</h2>
              <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                {solution.address_street && <div className="sm:col-span-2"><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.address_street')}</dt><dd>{solution.address_street}</dd></div>}
                {solution.address_city && <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.address_city')}</dt><dd>{solution.address_city}</dd></div>}
                {solution.address_province && <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.address_province')}</dt><dd>{solution.address_province}</dd></div>}
                {solution.address_postal_code && <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.address_postal_code')}</dt><dd>{solution.address_postal_code}</dd></div>}
                {solution.address_note && <div className="sm:col-span-2"><dt className="text-sm text-base-content/70">{t('shop.custom_solution.address_note')}</dt><dd className="whitespace-pre-wrap">{solution.address_note}</dd></div>}
              </dl>
            </section>
          )}

          <section>
            <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.problem_description')}</h2>
            <p className="whitespace-pre-wrap">{solution.problem_description ?? ''}</p>
          </section>

          {(solution.iterations_count > 0 || solution.improvement_feedback) && (
            <section>
              <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.improvement_feedback')}</h2>
              <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.iterations')}</dt><dd>{solution.iterations_count ?? 0}</dd></div>
                {solution.improvement_feedback_at && (
                  <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.improvement_at')}</dt><dd>{new Date(solution.improvement_feedback_at).toLocaleString()}</dd></div>
                )}
                {solution.improvement_feedback && (
                  <div className="sm:col-span-2"><dt className="text-sm text-base-content/70 sr-only">{t('admin.personalized_solutions.improvement_feedback')}</dt><dd className="whitespace-pre-wrap">{solution.improvement_feedback}</dd></div>
                )}
              </dl>
            </section>
          )}

          {(solution.resolution || solution.status) && (
            <section>
              <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.admin_response')}</h2>
              <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.status')}</dt><dd><span className={`badge badge-sm ${getStatusBadgeClass(solution.status)}`}>{t(`admin.personalized_solutions.status_${solution.status}`)}</span></dd></div>
                {solution.resolution && <div className="sm:col-span-2"><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.resolution')}</dt><dd className="whitespace-pre-wrap">{solution.resolution}</dd></div>}
              </dl>
            </section>
          )}

          {solution.attachments && solution.attachments.length > 0 && (() => {
            const imageAttachments = solution.attachments.filter(isImageAttachment);
            const otherAttachments = solution.attachments.filter((a) => !isImageAttachment(a));
            return (
              <section>
                <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.attachments')}</h2>
                {imageAttachments.length > 0 && (
                  <div className="mb-4">
                    <h3 className="text-sm font-medium text-base-content/70 mb-2">{t('admin.personalized_solutions.attachments_images')}</h3>
                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                      {imageAttachments.map((a) => (
                        <a key={a.id} href={a.url} target="_blank" rel="noopener noreferrer" className="block rounded-lg overflow-hidden border border-base-300 bg-base-200 hover:border-base-content/20 transition-colors">
                          <img src={a.url} alt={a.original_filename || ''} className="w-full h-40 object-cover" />
                          {a.original_filename && <p className="p-2 text-sm truncate text-base-content/80" title={a.original_filename}>{a.original_filename}</p>}
                        </a>
                      ))}
                    </div>
                  </div>
                )}
                {otherAttachments.length > 0 && (
                  <div>
                    <h3 className="text-sm font-medium text-base-content/70 mb-2">{t('admin.personalized_solutions.attachments_files')}</h3>
                    <ul className="list-disc list-inside space-y-1">
                      {otherAttachments.map((a) => (
                        <li key={a.id}>
                          <a href={a.url} target="_blank" rel="noopener noreferrer" className="link link-hover">
                            {a.original_filename || String(a.id)}
                          </a>
                          {a.size_bytes != null && <span className="text-base-content/70 text-sm ml-1">({Math.round(a.size_bytes / 1024)} KB)</span>}
                        </li>
                      ))}
                    </ul>
                  </div>
                )}
              </section>
            );
          })()}

          <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2 border-t border-base-200 pt-4">
            <div><dt className="text-sm text-base-content/70">{t('admin.products.is_active')}</dt><dd>{solution.is_active ? t('common.yes') : t('common.no')}</dd></div>
            <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.created_at')}</dt><dd>{solution.created_at ? new Date(solution.created_at).toLocaleString() : ''}</dd></div>
            {solution.updated_at && <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.updated_at')}</dt><dd>{new Date(solution.updated_at).toLocaleString()}</dd></div>}
          </dl>
        </div>
      </div>
    </div>
  );
}
