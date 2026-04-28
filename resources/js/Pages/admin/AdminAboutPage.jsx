import React, { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import ReactMarkdown from 'react-markdown';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { APP_VERSION } from '../../config/version';

function markdownComponents() {
  return {
    h1: ({ children }) => <h2 className="text-xl font-bold text-base-content mt-6 mb-2 first:mt-0">{children}</h2>,
    h2: ({ children }) => <h3 className="text-lg font-semibold text-base-content mt-5 mb-2 border-b border-base-300 pb-1">{children}</h3>,
    h3: ({ children }) => <h4 className="text-base font-semibold mt-4 mb-1">{children}</h4>,
    p: ({ children }) => <p className="text-base-content/90 mb-3 leading-relaxed">{children}</p>,
    ul: ({ children }) => <ul className="list-disc pl-5 mb-3 space-y-1 text-base-content/90">{children}</ul>,
    ol: ({ children }) => <ol className="list-decimal pl-5 mb-3 space-y-1 text-base-content/90">{children}</ol>,
    li: ({ children }) => <li className="leading-relaxed">{children}</li>,
    code: ({ className, children, ...props }) => {
      const inline = !className;
      if (inline) {
        return <code className="bg-base-200 px-1 py-0.5 rounded text-sm font-mono" {...props}>{children}</code>;
      }
      return (
        <code className="block bg-base-200 p-3 rounded-lg text-sm font-mono overflow-x-auto whitespace-pre-wrap mb-3" {...props}>
          {children}
        </code>
      );
    },
    pre: ({ children }) => <div className="mb-3">{children}</div>,
    a: ({ href, children }) => (
      <a href={href} className="link link-primary" target="_blank" rel="noopener noreferrer">
        {children}
      </a>
    ),
    hr: () => <hr className="border-base-300 my-6" />,
    strong: ({ children }) => <strong className="font-semibold text-base-content">{children}</strong>,
    blockquote: ({ children }) => (
      <blockquote className="border-l-4 border-primary/40 pl-3 my-3 text-base-content/80 italic">{children}</blockquote>
    ),
  };
}

export default function AdminAboutPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [markdown, setMarkdown] = useState('');
  const [loadError, setLoadError] = useState('');
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      try {
        const { data } = await api.get('admin/changelog');
        if (cancelled) return;
        if (data.success && typeof data.data?.markdown === 'string') {
          setMarkdown(data.data.markdown);
        } else {
          setLoadError(t('common.error'));
        }
      } catch (err) {
        if (err.response?.status === 401) navigate('/admin/login');
        else if (!cancelled) setLoadError(err.response?.data?.message || t('common.error'));
      } finally {
        if (!cancelled) setLoading(false);
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [navigate, t]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.about.title')}</PageTitle>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-base">{t('admin.about.version_title')}</h2>
          <p className="text-base-content/80 tabular-nums">{APP_VERSION}</p>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-4">
          <h2 className="card-title text-base">{t('admin.about.team_title')}</h2>
          <p className="text-base-content/90">{t('admin.about.team_intro')}</p>
          <div className="grid gap-4 sm:grid-cols-2">
            <div className="rounded-lg border border-base-300 bg-base-200/40 p-4">
              <p className="font-semibold text-base-content">{t('admin.about.developer_primary_name')}</p>
              <p className="text-sm text-base-content/80 mt-2 leading-relaxed">{t('admin.about.developer_primary_bio')}</p>
            </div>
            <div className="rounded-lg border border-base-300 bg-base-200/40 p-4">
              <p className="font-semibold text-base-content">{t('admin.about.developer_laia_name')}</p>
              <p className="text-sm text-base-content/80 mt-2 leading-relaxed">{t('admin.about.developer_laia_bio')}</p>
            </div>
          </div>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body space-y-3">
          <h2 className="card-title text-base">{t('admin.about.stack_title')}</h2>
          <ul className="list-disc pl-5 space-y-2 text-base-content/90">
            <li>{t('admin.about.stack_backend')}</li>
            <li>{t('admin.about.stack_frontend')}</li>
            <li>{t('admin.about.stack_data')}</li>
            <li>{t('admin.about.stack_payments')}</li>
          </ul>
        </div>
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="card-title text-base mb-4">{t('admin.about.changelog_title')}</h2>
          {loading && (
            <div className="flex justify-center py-8">
              <span className="loading loading-spinner loading-lg" aria-hidden="true" />
            </div>
          )}
          {!loading && loadError && (
            <div role="alert" className="alert alert-error text-sm">{loadError}</div>
          )}
          {!loading && !loadError && markdown && (
            <div className="markdown-body max-h-[min(70vh,52rem)] overflow-y-auto rounded-lg border border-base-300 bg-base-200/30 p-4 text-sm">
              <ReactMarkdown components={markdownComponents()}>
                {markdown}
              </ReactMarkdown>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
