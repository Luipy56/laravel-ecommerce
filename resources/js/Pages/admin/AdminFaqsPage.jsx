import React, { useCallback, useEffect, useRef, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';

export default function AdminFaqsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [faqs, setFaqs] = useState([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(false);
  const [search, setSearch] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');
  const [activeFilter, setActiveFilter] = useState('');
  const pageRef = useRef(1);
  const sentinelRef = useRef(null);

  const fetchFaqs = useCallback(async (pageNum, reset = false) => {
    if (reset) setLoading(true);
    else setLoadingMore(true);
    try {
      const params = { page: pageNum, per_page: 25 };
      if (searchDebounce) params.search = searchDebounce;
      if (activeFilter !== '') params.is_active = activeFilter;
      const { data } = await api.get('admin/faqs', { params });
      if (data.success) {
        const newItems = data.data || [];
        if (reset) setFaqs(newItems);
        else setFaqs((prev) => [...prev, ...newItems]);
        const meta = data.meta || {};
        setHasMore((meta.current_page ?? pageNum) < (meta.last_page ?? 1));
        pageRef.current = pageNum;
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      if (reset) setFaqs([]);
    } finally {
      if (reset) setLoading(false);
      else setLoadingMore(false);
    }
  }, [navigate, searchDebounce, activeFilter]);

  useEffect(() => {
    pageRef.current = 1;
    fetchFaqs(1, true);
  }, [fetchFaqs]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    if (!hasMore) return;
    const el = sentinelRef.current;
    if (!el) return;
    const obs = new IntersectionObserver(
      (entries) => {
        if (!entries.some((e) => e.isIntersecting)) return;
        if (!hasMore || loadingMore || loading) return;
        const next = pageRef.current + 1;
        pageRef.current = next;
        fetchFaqs(next, false);
      },
      { rootMargin: '120px' },
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [hasMore, loadingMore, loading, fetchFaqs]);

  const truncate = (s, n = 80) => (s && s.length > n ? `${s.slice(0, n)}…` : s || '');

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.faqs.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.faqs.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.faqs.search_placeholder')}
          />
          <label className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.faqs.filter_active')}</span>
            <select
              className="select select-bordered select-sm sm:select-md w-full sm:w-40"
              value={activeFilter}
              onChange={(e) => setActiveFilter(e.target.value)}
              aria-label={t('admin.faqs.filter_active')}
            >
              <option value="">{t('shop.categories.all')}</option>
              <option value="1">{t('common.yes')}</option>
              <option value="0">{t('common.no')}</option>
            </select>
          </label>
        </div>
        <Link to="/admin/faqs/new" className="btn btn-primary btn-sm sm:btn-md shrink-0 ml-auto">
          {t('admin.faqs.add')}
        </Link>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : faqs.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">{t('admin.faqs.empty')}</div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra">
              <thead>
                <tr>
                  <th className="text-center tabular-nums">{t('admin.common.column_id')}</th>
                  <th className="text-center tabular-nums">{t('admin.faqs.sort_order')}</th>
                  <th>{t('admin.faqs.question_ca')}</th>
                  <th className="text-center">{t('admin.faqs.is_active')}</th>
                </tr>
              </thead>
              <tbody>
                {faqs.map((row) => (
                  <tr
                    key={row.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200"
                    onClick={() => navigate(`/admin/faqs/${row.id}/edit`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/faqs/${row.id}/edit`);
                      }
                    }}
                  >
                    <td className="text-center tabular-nums">{row.id}</td>
                    <td className="text-center tabular-nums">{row.sort_order}</td>
                    <td>{truncate(row.question_ca)}</td>
                    <td className="text-center">{row.is_active ? t('common.yes') : t('common.no')}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>

      <div ref={sentinelRef} className="py-2 flex justify-center" aria-hidden="true">
        {loadingMore && <span className="loading loading-spinner loading-md" />}
      </div>
    </div>
  );
}
