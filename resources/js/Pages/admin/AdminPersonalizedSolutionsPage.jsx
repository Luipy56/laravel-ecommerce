import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';

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

export default function AdminPersonalizedSolutionsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { isVisible } = useAdminIndexColumnVisibility('personalized_solutions');
  const [solutions, setSolutions] = useState([]);
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [activeFilter, setActiveFilter] = useState('1');

  const fetchSolutions = useCallback(async () => {
    setLoading(true);
    try {
      const params = { page, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (statusFilter) params.status = statusFilter;
      if (activeFilter !== '') params.is_active = activeFilter === '1';
      const { data } = await api.get('admin/personalized-solutions', { params });
      if (data.success) {
        setSolutions(data.data || []);
        setMeta(data.meta || meta);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setSolutions([]);
    } finally {
      setLoading(false);
    }
  }, [navigate, page, searchDebounce, statusFilter, activeFilter]);

  useEffect(() => {
    fetchSolutions();
  }, [fetchSolutions]);

  useEffect(() => {
    setPage(1);
  }, [searchDebounce, statusFilter, activeFilter]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.personalized_solutions.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <input
          type="search"
          className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
          placeholder={t('admin.personalized_solutions.search_placeholder')}
          value={search}
          onChange={(e) => setSearch(e.target.value)}
          aria-label={t('admin.personalized_solutions.search_placeholder')}
        />
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.personalized_solutions.filter_status')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-44"
            value={statusFilter}
            onChange={(e) => setStatusFilter(e.target.value)}
            aria-label={t('admin.personalized_solutions.filter_status')}
          >
            <option value="">{t('admin.personalized_solutions.status_all')}</option>
            {STATUSES.map((s) => (
              <option key={s} value={s}>{t(`admin.personalized_solutions.status_${s}`)}</option>
            ))}
          </select>
        </label>
        <label className="flex items-center gap-2 shrink-0">
          <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.personalized_solutions.filter_active')}</span>
          <select
            className="select select-bordered select-sm sm:select-md w-full sm:w-40"
            value={activeFilter}
            onChange={(e) => setActiveFilter(e.target.value)}
            aria-label={t('admin.personalized_solutions.filter_active')}
          >
            <option value="">{t('shop.categories.all')}</option>
            <option value="1">{t('common.yes')}</option>
            <option value="0">{t('common.no')}</option>
          </select>
        </label>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : solutions.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.personalized_solutions.no_solutions')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>
                  {isVisible('id') ? <th className="text-center tabular-nums">{t('admin.common.column_id')}</th> : null}
                  {isVisible('email') ? <th>{t('admin.personalized_solutions.email')}</th> : null}
                  {isVisible('phone') ? <th>{t('admin.personalized_solutions.phone')}</th> : null}
                  {isVisible('problem_description') ? <th>{t('admin.personalized_solutions.problem_description')}</th> : null}
                  {isVisible('status') ? <th className="text-center">{t('admin.personalized_solutions.status')}</th> : null}
                  {isVisible('created_at') ? <th className="text-end">{t('admin.personalized_solutions.created_at')}</th> : null}
                  {isVisible('client_login_email') ? <th>{t('admin.personalized_solutions.client_login_email')}</th> : null}
                  {isVisible('is_active') ? <th className="text-center">{t('admin.products.is_active')}</th> : null}
                </tr>
              </thead>
              <tbody>
                {solutions.map((s) => (
                  <tr
                    key={s.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/personalized-solutions/${s.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/personalized-solutions/${s.id}`);
                      }
                    }}
                  >
                    {isVisible('id') ? <td className="text-center tabular-nums">{s.id}</td> : null}
                    {isVisible('email') ? <td>{s.email ?? ''}</td> : null}
                    {isVisible('phone') ? <td>{s.phone ?? ''}</td> : null}
                    {isVisible('problem_description') ? <td className="max-w-[200px] truncate">{s.problem_description ?? ''}</td> : null}
                    {isVisible('status') ? (
                      <td className="text-center"><span className={`badge badge-sm ${getStatusBadgeClass(s.status)}`}>{t(`admin.personalized_solutions.status_${s.status}`)}</span></td>
                    ) : null}
                    {isVisible('created_at') ? <td className="text-end">{s.created_at ? new Date(s.created_at).toLocaleDateString() : ''}</td> : null}
                    {isVisible('client_login_email') ? <td>{s.client_login_email ?? ''}</td> : null}
                    {isVisible('is_active') ? <td className="text-center">{s.is_active ? t('common.yes') : t('common.no')}</td> : null}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      {meta.last_page > 1 && (
        <div className="join flex justify-center">
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page <= 1}
            onClick={() => setPage((p) => Math.max(1, p - 1))}
          >
            {t('shop.pagination.prev')}
          </button>
          <span className="join-item flex items-center justify-center px-4 py-2 h-8 text-sm text-base-content bg-base-100 border border-base-300">
            {t('shop.pagination.page')} {page} {t('shop.pagination.of')} {meta.last_page}
          </span>
          <button
            type="button"
            className="btn join-item btn-sm bg-base-100 border-base-300"
            disabled={page >= meta.last_page}
            onClick={() => setPage((p) => Math.min(meta.last_page, p + 1))}
          >
            {t('shop.pagination.next')}
          </button>
        </div>
      )}
    </div>
  );
}
