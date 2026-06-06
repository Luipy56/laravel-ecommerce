import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

const STAGE_URL = 'https://stage-serra.ldeluipy.es';

const COMMENT_MAX = 4000;
const TITLE_MAX = 200;

const LABEL_TO_STAGING = 'to-staging';
const LABEL_HUMAN_VALIDATION = 'waiting for human validation';

export default function AdminHelpPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [title, setTitle] = useState('');
  const [comment, setComment] = useState('');
  const [label, setLabel] = useState(LABEL_HUMAN_VALIDATION);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [successModalOpen, setSuccessModalOpen] = useState(false);
  const [submittedLabel, setSubmittedLabel] = useState(LABEL_HUMAN_VALIDATION);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccessModalOpen(false);

    const trimmedComment = comment.trim();
    if (!trimmedComment) {
      setError(t('admin.help.error'));
      return;
    }

    setLoading(true);
    try {
      const payload = {
        comment: trimmedComment,
        label,
      };
      const trimmedTitle = title.trim();
      if (trimmedTitle) {
        payload.title = trimmedTitle;
      }

      const { data } = await api.post('admin/help-requests', payload);
      if (data.success) {
        setSubmittedLabel(label);
        setTitle('');
        setComment('');
        setLabel(LABEL_HUMAN_VALIDATION);
        setSuccessModalOpen(true);
      } else {
        setError(t('admin.help.error'));
      }
    } catch (err) {
      if (err.response?.status === 401) {
        navigate('/admin/login');
        return;
      }
      setError(t('admin.help.error'));
    } finally {
      setLoading(false);
    }
  };

  const stageLink = (
    <a
      href={STAGE_URL}
      className="link link-primary"
      target="_blank"
      rel="noopener noreferrer"
    >
      {t('admin.help.stage_link')}
    </a>
  );

  const isToStaging = submittedLabel === LABEL_TO_STAGING;

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.help.title')}</PageTitle>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-4">
          <p className="text-base-content/80 text-sm">{t('admin.help.intro')}</p>

          {error && (
            <div role="alert" className="alert alert-error text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <label className="form-control w-full">
              <span className="label-text mb-1">{t('admin.help.label_field')}</span>
              <select
                className="select select-bordered select-sm sm:select-md w-full"
                value={label}
                onChange={(e) => setLabel(e.target.value)}
                disabled={loading}
              >
                <option value={LABEL_TO_STAGING}>{t('admin.help.label_to_staging')}</option>
                <option value={LABEL_HUMAN_VALIDATION}>{t('admin.help.label_human_validation')}</option>
              </select>
            </label>

            <label className="form-control w-full">
              <span className="label-text mb-1">{t('admin.help.title_optional')}</span>
              <input
                type="text"
                className="input input-bordered input-sm sm:input-md w-full"
                value={title}
                onChange={(e) => setTitle(e.target.value.slice(0, TITLE_MAX))}
                maxLength={TITLE_MAX}
                disabled={loading}
                autoComplete="off"
              />
            </label>

            <label className="form-control w-full">
              <span className="label-text mb-1">{t('admin.help.comment_label')}</span>
              <textarea
                className="textarea textarea-bordered w-full min-h-40 text-sm sm:text-base"
                placeholder={t('admin.help.comment_placeholder')}
                value={comment}
                onChange={(e) => setComment(e.target.value.slice(0, COMMENT_MAX))}
                maxLength={COMMENT_MAX}
                required
                disabled={loading}
              />
              <span className="label-text-alt text-end tabular-nums mt-1">
                {comment.length}/{COMMENT_MAX}
              </span>
            </label>

            <div className="flex justify-end">
              <button
                type="submit"
                className="btn btn-primary btn-sm sm:btn-md"
                disabled={loading || !comment.trim()}
              >
                {loading ? (
                  <span className="loading loading-spinner loading-sm" aria-hidden="true" />
                ) : (
                  t('admin.help.send')
                )}
              </button>
            </div>
          </form>
        </div>
      </div>

      {successModalOpen && (
        <dialog className="modal modal-open" aria-labelledby="admin-help-success-title">
          <div className="modal-box max-w-md">
            <h3 id="admin-help-success-title" className="font-bold text-lg mb-2">
              {t('admin.help.modal_title')}
            </h3>
            <p className="text-sm text-base-content/80">
              {isToStaging ? (
                <>
                  {t('admin.help.modal_to_staging_before')} {stageLink}
                  {t('admin.help.modal_to_staging_after')}
                </>
              ) : (
                <>
                  {t('admin.help.modal_validation_before')} {stageLink}
                  {t('admin.help.modal_validation_after')}
                </>
              )}
            </p>
            <div className="modal-action">
              <button
                type="button"
                className="btn btn-primary btn-sm sm:btn-md"
                onClick={() => setSuccessModalOpen(false)}
              >
                {t('common.close')}
              </button>
            </div>
          </div>
          <form method="dialog" className="modal-backdrop">
            <button type="button" onClick={() => setSuccessModalOpen(false)}>
              {t('common.close')}
            </button>
          </form>
        </dialog>
      )}
    </div>
  );
}
