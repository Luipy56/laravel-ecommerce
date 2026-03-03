import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

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

export default function AdminPersonalizedSolutionShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { id } = useParams();
  const [solution, setSolution] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');

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
        <div className="flex gap-2">
          <Link to="/admin/personalized-solutions" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          <Link to={`/admin/personalized-solutions/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
        </div>
      </div>

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

          {(solution.resolution || solution.status) && (
            <section>
              <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.admin_response')}</h2>
              <dl className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.status')}</dt><dd><span className={`badge badge-sm ${getStatusBadgeClass(solution.status)}`}>{t(`admin.personalized_solutions.status_${solution.status}`)}</span></dd></div>
                {solution.resolution && <div className="sm:col-span-2"><dt className="text-sm text-base-content/70">{t('admin.personalized_solutions.resolution')}</dt><dd className="whitespace-pre-wrap">{solution.resolution}</dd></div>}
              </dl>
            </section>
          )}

          {solution.attachments && solution.attachments.length > 0 && (
            <section>
              <h2 className="text-lg font-semibold mb-2">{t('admin.personalized_solutions.attachments')}</h2>
              <ul className="list-disc list-inside space-y-1">
                {solution.attachments.map((a) => (
                  <li key={a.id}>
                    <a href={a.url} target="_blank" rel="noopener noreferrer" className="link link-hover">
                      {a.original_filename || String(a.id)}
                    </a>
                    {a.size_bytes != null && <span className="text-base-content/70 text-sm ml-1">({Math.round(a.size_bytes / 1024)} KB)</span>}
                  </li>
                ))}
              </ul>
            </section>
          )}

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
