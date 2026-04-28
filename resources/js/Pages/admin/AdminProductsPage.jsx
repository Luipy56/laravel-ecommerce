import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminIndexColumnVisibility } from '../../hooks/useAdminShopSettingsQuery';

export default function AdminProductsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { isVisible } = useAdminIndexColumnVisibility('products');
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [meta, setMeta] = useState({ current_page: 1, last_page: 1, per_page: 20, total: 0 });
  const [page, setPage] = useState(1);
  const [search, setSearch] = useState('');
  const [categoryId, setCategoryId] = useState('');
  const [searchDebounce, setSearchDebounce] = useState('');

  const fetchCategories = useCallback(async () => {
    try {
      const { data } = await api.get('admin/categories', { params: { per_page: 500 } });
      if (data.success) setCategories(data.data || []);
    } catch {
      setCategories([]);
    }
  }, []);

  const fetchProducts = useCallback(async () => {
    setLoading(true);
    try {
      const params = { page, per_page: 20 };
      if (searchDebounce) params.search = searchDebounce;
      if (categoryId) params.category_id = categoryId;
      const { data } = await api.get('admin/products', { params });
      if (data.success) {
        setProducts(data.data || []);
        setMeta(data.meta || meta);
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      setProducts([]);
    } finally {
      setLoading(false);
    }
  }, [page, searchDebounce, categoryId, navigate]);

  useEffect(() => {
    fetchCategories();
  }, [fetchCategories]);

  useEffect(() => {
    fetchProducts();
  }, [fetchProducts]);

  useEffect(() => {
    const tid = setTimeout(() => setSearchDebounce(search.trim()), 300);
    return () => clearTimeout(tid);
  }, [search]);

  useEffect(() => {
    setPage(1);
  }, [searchDebounce, categoryId]);

  return (
    <div className="space-y-6">
      <PageTitle>{t('admin.products.title')}</PageTitle>

      <div className="flex flex-wrap items-center gap-2 sm:gap-4">
        <div className="flex flex-wrap items-center gap-2 sm:gap-4 flex-1 min-w-0">
          <input
            type="search"
            className="input input-bordered input-sm sm:input-md w-full min-w-0 max-w-xs"
            placeholder={t('admin.products.search_placeholder')}
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            aria-label={t('admin.products.search_placeholder')}
          />
          <label className="flex items-center gap-2 shrink-0">
            <span className="text-sm text-base-content/70 whitespace-nowrap">{t('admin.products.filter_category')}</span>
            <select
              className="select select-bordered select-sm sm:select-md w-full sm:w-48"
              value={categoryId}
              onChange={(e) => setCategoryId(e.target.value)}
              aria-label={t('admin.products.filter_category')}
            >
              <option value="">{t('shop.categories.all')}</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </select>
          </label>
        </div>
        <Link to="/admin/products/new" className="btn btn-primary btn-circle btn-sm sm:btn-md shrink-0 ml-auto" aria-label={t('admin.products.add')}>
          <span className="text-xl sm:text-2xl leading-none" aria-hidden="true">+</span>
        </Link>
      </div>

      <div className="card bg-base-100 shadow border border-base-200 overflow-hidden">
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" aria-hidden="true" />
          </div>
        ) : products.length === 0 ? (
          <div className="p-8 text-center text-base-content/70">
            {t('admin.products.no_products')}
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="table table-zebra [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap [&_thead_th]:border-b-2 [&_thead_th]:border-base-300 [&_thead_th]:font-semibold [&_thead_th]:bg-transparent">
              <thead>
                <tr>
                  {isVisible('id') ? <th className="text-center tabular-nums">{t('admin.common.column_id')}</th> : null}
                  {isVisible('code') ? <th>{t('admin.products.code')}</th> : null}
                  {isVisible('name') ? <th>{t('admin.products.name')}</th> : null}
                  {isVisible('category') ? <th>{t('admin.products.category')}</th> : null}
                  {isVisible('price') ? <th className="text-end">{t('admin.products.price')}</th> : null}
                  {isVisible('discount_percent') ? <th className="text-end">{t('admin.products.discount_percent')}</th> : null}
                  {isVisible('stock') ? <th className="text-center">{t('admin.products.stock')}</th> : null}
                  {isVisible('is_featured') ? <th className="text-center">{t('admin.products.is_featured')}</th> : null}
                  {isVisible('is_trending') ? <th className="text-center">{t('admin.products.is_trending')}</th> : null}
                  {isVisible('is_active') ? <th className="text-center">{t('admin.products.is_active')}</th> : null}
                </tr>
              </thead>
              <tbody>
                {products.map((p) => (
                  <tr
                    key={p.id}
                    role="button"
                    tabIndex={0}
                    className="cursor-pointer hover:bg-base-200 focus:bg-base-200 focus:outline-none"
                    onClick={() => navigate(`/admin/products/${p.id}`)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(`/admin/products/${p.id}`);
                      }
                    }}
                  >
                    {isVisible('id') ? <td className="text-center tabular-nums">{p.id}</td> : null}
                    {isVisible('code') ? <td>{p.code}</td> : null}
                    {isVisible('name') ? <td>{p.name}</td> : null}
                    {isVisible('category') ? <td>{p.category?.name}</td> : null}
                    {isVisible('price') ? (
                      <td className="text-end tabular-nums">{p.price != null ? Number(p.price).toFixed(2) + ' €' : ''}</td>
                    ) : null}
                    {isVisible('discount_percent') ? (
                      <td className="text-end tabular-nums">
                        {p.discount_percent != null &&
                        p.discount_percent !== '' &&
                        Number(p.discount_percent) > 0
                          ? `${Number(p.discount_percent)}%`
                          : ''}
                      </td>
                    ) : null}
                    {isVisible('stock') ? <td className="text-center tabular-nums">{p.stock}</td> : null}
                    {isVisible('is_featured') ? <td className="text-center">{p.is_featured ? t('common.yes') : t('common.no')}</td> : null}
                    {isVisible('is_trending') ? <td className="text-center">{p.is_trending ? t('common.yes') : t('common.no')}</td> : null}
                    {isVisible('is_active') ? <td className="text-center">{p.is_active ? t('common.yes') : t('common.no')}</td> : null}
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
