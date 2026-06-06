import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useToast } from '../../contexts/ToastContext';

const STAGE_URL = 'https://stage-serra.ldeluipy.es';

const COMMENT_MAX = 4000;
const TITLE_MAX = 200;

export default function AdminHelpPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showToast } = useToast();
  const [title, setTitle] = useState('');
  const [comment, setComment] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState(false);

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError('');
    setSuccess(false);

    const trimmedComment = comment.trim();
    if (!trimmedComment) {
      setError(t('admin.help.error'));
      return;
    }

    setLoading(true);
    try {
      const payload = { comment: trimmedComment };
      const trimmedTitle = title.trim();
      if (trimmedTitle) {
        payload.title = trimmedTitle;
      }

      const { data } = await api.post('admin/help-requests', payload);
      if (data.success) {
        setSuccess(true);
        setTitle('');
        setComment('');
        showToast({ message: t('admin.help.success_toast'), type: 'primary' });
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

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.help.title')}</PageTitle>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-4">
          <p className="text-base-content/80 text-sm">{t('admin.help.intro')}</p>

          {success && (
            <p className="text-sm text-base-content/80">
              {t('admin.help.stage_preview')}{' '}
              <a
                href={STAGE_URL}
                className="link link-primary"
                target="_blank"
                rel="noopener noreferrer"
              >
                stage-serra.ldeluipy.es
              </a>
            </p>
          )}

          {error && (
            <div role="alert" className="alert alert-error text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
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
    </div>
  );
}
