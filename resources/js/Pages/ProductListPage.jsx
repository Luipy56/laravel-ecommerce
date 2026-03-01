import React, { useEffect, useState } from 'react';
import { Link, useSearchParams, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';
import PageTitle from '../components/PageTitle';

export default function ProductListPage() {
  const { t } = useTranslation();
  const { id: categoryIdFromRoute } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const categoryId = categoryIdFromRoute || searchParams.get('category_id');
  const search = searchParams.get('search');
  const pageParam = searchParams.get('page');
  const currentPage = Math.max(1, parseInt(pageParam || '1', 10) || 1);
  const [products, setProducts] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

  useEffect(() => {
    const ac = new AbortController();
    const params = { page: currentPage };
    if (categoryId) params.category_id = categoryId;
    if (search) params.search = search;
    api.get('products', { params, signal: ac.signal })
      .then((r) => {
        if (r.data.success) {
          const list = Array.isArray(r.data.data) ? r.data.data : (r.data.data?.data ?? []);
          setProducts(list.map((p) => Product.fromApi(p)));
          if (r.data.meta) {
            setPagination({
              current_page: r.data.meta.current_page,
              last_page: r.data.meta.last_page,
              per_page: r.data.meta.per_page,
              total: r.data.meta.total,
            });
          }
          // Scroll to top after new content is painted (avoids stopping halfway on first/last page)
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              window.scrollTo({ top: 0, behavior: 'smooth' });
            });
          });
        }
      })
      .catch((err) => { if (err.name !== 'AbortError') setProducts([]); })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, [categoryId, search, currentPage]);

  useEffect(() => {
    api.get('categories').then((r) => {
      if (r.data.success) setCategories(r.data.data || []);
    });
  }, []);

  return (
    <div className="flex flex-col lg:flex-row gap-6">
      <aside className="lg:w-56 shrink-0">
        <h2 className="font-semibold mb-2">{t('shop.categories')}</h2>
        <ul className="menu bg-base-100 rounded-box border border-base-300">
          <li><Link to="/products">{t('shop.categories.all')}</Link></li>
          {categories.map((c) => (
            <li key={c.id}>
              <Link to={`/products?category_id=${c.id}`} className={categoryId === String(c.id) ? 'active' : ''}>
                {c.name}
              </Link>
            </li>
          ))}
        </ul>
      </aside>
      <div className="flex-1">
        <PageTitle className="mb-4">{search ? t('common.search') + ': ' + search : t('shop.products')}</PageTitle>
        {loading ? (
          <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>
        ) : products.length === 0 ? (
          <p>{t('shop.cart.empty')}</p>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            {products.map((p) => (
              <ProductCard key={p.id} product={p} />
            ))}
          </div>
        )}
        {pagination.last_page > 1 && (
          <div className="flex flex-wrap items-center justify-center gap-2 mt-8">
            <button
              type="button"
              className="btn btn-sm btn-outline btn-square"
              disabled={pagination.current_page <= 1}
              onClick={() => {
                const next = new URLSearchParams(searchParams);
                if (pagination.current_page <= 2) next.delete('page');
                else next.set('page', String(pagination.current_page - 1));
                setSearchParams(next);
              }}
              aria-label={t('shop.pagination.prev')}
            >
              ‹
            </button>
            <span className="text-sm text-base-content/80 px-2">
              {t('shop.pagination.page')} {pagination.current_page} {t('shop.pagination.of')} {pagination.last_page}
            </span>
            <button
              type="button"
              className="btn btn-sm btn-outline btn-square"
              disabled={pagination.current_page >= pagination.last_page}
              onClick={() => {
                const next = new URLSearchParams(searchParams);
                next.set('page', String(pagination.current_page + 1));
                setSearchParams(next);
              }}
              aria-label={t('shop.pagination.next')}
            >
              ›
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
