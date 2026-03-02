import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useSearchParams, useParams, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';
import PageTitle from '../components/PageTitle';

function buildSearchParams({ categoryIds, featureIds, search, page }) {
  const next = new URLSearchParams();
  if (search) next.set('search', search);
  categoryIds.forEach((id) => next.append('category_id', id));
  featureIds.forEach((id) => next.append('feature_id', id));
  if (page > 1) next.set('page', String(page));
  return next;
}

export default function ProductListPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const { id: categoryIdFromRoute } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const isCategoryRoute = categoryIdFromRoute && location.pathname.includes('/categories/');
  const categoryIdsFromUrl = searchParams.getAll('category_id');
  const categoryIds = useMemo(() => {
    if (isCategoryRoute) {
      const withRoute = [String(categoryIdFromRoute), ...categoryIdsFromUrl];
      return [...new Set(withRoute)];
    }
    return categoryIdsFromUrl;
  }, [isCategoryRoute, categoryIdFromRoute, categoryIdsFromUrl.join(',')]);
  const featureIds = searchParams.getAll('feature_id');
  const search = searchParams.get('search');
  const pageParam = searchParams.get('page');
  const currentPage = Math.max(1, parseInt(pageParam || '1', 10) || 1);

  const [catalogItems, setCatalogItems] = useState([]);
  const [categories, setCategories] = useState([]);
  const [featuresList, setFeaturesList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [pagination, setPagination] = useState({ current_page: 1, last_page: 1, per_page: 15, total: 0 });

  const setFilters = useCallback(
    (updates) => {
      const next = buildSearchParams({
        categoryIds: updates.categoryIds ?? categoryIds,
        featureIds: updates.featureIds ?? featureIds,
        search: updates.search ?? search ?? '',
        page: updates.page ?? currentPage,
      });
      setSearchParams(next);
    },
    [categoryIds, featureIds, search, currentPage, setSearchParams]
  );

  const handleAllCategories = useCallback(() => {
    const next = buildSearchParams({ categoryIds: [], featureIds: [], search: search ?? '', page: 1 });
    navigate('/products?' + next.toString());
  }, [search, navigate]);

  const toggleCategory = useCallback(
    (id) => {
      const sid = String(id);
      const next = categoryIds.includes(sid)
        ? categoryIds.filter((c) => c !== sid)
        : [...categoryIds, sid];
      const payload = { categoryIds: next, page: 1 };
      if (isCategoryRoute && next.length > 0 && !next.includes(String(categoryIdFromRoute))) {
        const nextParams = buildSearchParams({
          ...payload,
          featureIds,
          search: search ?? '',
        });
        navigate('/products?' + nextParams.toString());
      } else {
        setFilters(payload);
      }
    },
    [categoryIds, isCategoryRoute, categoryIdFromRoute, featureIds, search, setFilters, navigate]
  );

  const toggleFeature = useCallback(
    (id) => {
      const sid = String(id);
      const next = featureIds.includes(sid)
        ? featureIds.filter((f) => f !== sid)
        : [...featureIds, sid];
      setFilters({ featureIds: next, page: 1 });
    },
    [featureIds, setFilters]
  );

  useEffect(() => {
    const ac = new AbortController();
    const params = { page: currentPage, include_packs: true };
    if (categoryIds.length) params.category_id = categoryIds;
    if (featureIds.length) params.feature_ids = featureIds;
    if (search) params.search = search;
    api
      .get('products', { params, signal: ac.signal })
      .then((r) => {
        if (r.data.success) {
          const list = Array.isArray(r.data.data) ? r.data.data : r.data.data?.data ?? [];
          const items = list.map((entry) => {
            if (entry.type === 'pack' && entry.data) {
              const d = entry.data;
              const price = Number(d.price) || 0;
              return {
                type: 'pack',
                item: {
                  id: d.id,
                  name: d.name,
                  price,
                  items: d.items ?? [],
                  images: d.images ?? [],
                  primaryImageUrl: d.images?.[0]?.url ?? '/images/dummy.jpg',
                  formattedPrice: new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(price),
                },
              };
            }
            if (entry.type === 'product' && entry.data) {
              return { type: 'product', item: Product.fromApi(entry.data) };
            }
            return null;
          }).filter(Boolean);
          setCatalogItems(items);
          if (r.data.meta) {
            setPagination({
              current_page: r.data.meta.current_page,
              last_page: r.data.meta.last_page,
              per_page: r.data.meta.per_page,
              total: r.data.meta.total,
            });
          }
          requestAnimationFrame(() => {
            requestAnimationFrame(() => {
              window.scrollTo({ top: 0, behavior: 'smooth' });
            });
          });
        }
      })
      .catch((err) => {
        if (err.name !== 'AbortError') setCatalogItems([]);
      })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, [categoryIds.join(','), featureIds.join(','), search, currentPage]);

  useEffect(() => {
    api.get('categories').then((r) => {
      if (r.data.success) setCategories(r.data.data || []);
    });
  }, []);

  useEffect(() => {
    api.get('features').then((r) => {
      if (r.data.success) setFeaturesList(r.data.data || []);
    });
  }, []);

  const featuresByGroup = useMemo(() => {
    const map = new Map();
    for (const f of featuresList) {
      const name = f.feature_name || '';
      if (!map.has(name)) map.set(name, []);
      map.get(name).push(f);
    }
    return Array.from(map.entries()).map(([name, list]) => ({ name, list }));
  }, [featuresList]);

  return (
    <div className="flex flex-col lg:flex-row gap-6">
      <aside className="lg:w-64 shrink-0 space-y-6 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto lg:pr-1">
        <div>
          <h2 className="font-semibold mb-2">{t('shop.categories')}</h2>
          <ul className="menu bg-base-100 rounded-box border border-base-300">
            <li>
              <button type="button" onClick={handleAllCategories} className={categoryIds.length === 0 ? 'active' : ''}>
                {t('shop.categories.all')}
              </button>
            </li>
            {categories.map((c) => (
              <li key={c.id}>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="checkbox"
                    className="checkbox checkbox-sm"
                    checked={categoryIds.includes(String(c.id))}
                    onChange={() => toggleCategory(c.id)}
                    aria-label={c.name}
                  />
                  <span>{c.name}</span>
                </label>
              </li>
            ))}
          </ul>
        </div>
        {featuresByGroup.length > 0 && (
          <div>
            <h2 className="font-semibold mb-2">{t('shop.filters.features')}</h2>
            <div className="space-y-3">
              {featuresByGroup.map(({ name, list }) => (
                <div key={name} className="bg-base-100 rounded-box border border-base-300 p-3">
                  <p className="text-sm font-medium text-base-content/80 mb-2">{name}</p>
                  <div className="flex flex-wrap gap-2">
                    {list.map((f) => (
                      <label key={f.id} className="flex items-center gap-1.5 cursor-pointer">
                        <input
                          type="checkbox"
                          className="checkbox checkbox-xs"
                          checked={featureIds.includes(String(f.id))}
                          onChange={() => toggleFeature(f.id)}
                          aria-label={f.value}
                        />
                        <span className="text-sm">{f.value}</span>
                      </label>
                    ))}
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </aside>
      <div className="flex-1">
        <PageTitle className="mb-4">{search ? t('common.search') + ': ' + search : t('shop.products')}</PageTitle>
        {loading ? (
          <div className="flex justify-center py-12">
            <span className="loading loading-spinner loading-lg" />
          </div>
        ) : catalogItems.length === 0 ? (
          search ? (
            <div className="rounded-box bg-base-200/60 p-6 sm:p-8 text-center">
              <p className="text-lg font-medium text-base-content mb-2">
                {t('shop.search.no_results', { query: search })}
              </p>
              <ul className="text-base-content/80 text-left list-disc list-inside max-w-md mx-auto space-y-1 mt-4">
                <li>{t('shop.search.check_spelling')}</li>
                <li>{t('shop.search.try_different')}</li>
              </ul>
            </div>
          ) : (
            <p className="text-base-content/70">{t('shop.no_products')}</p>
          )
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            {catalogItems.map(({ type, item }) => (
              <ProductCard
                key={type === 'product' ? `p-${item.id}` : `k-${item.id}`}
                product={type === 'product' ? item : undefined}
                pack={type === 'pack' ? item : undefined}
              />
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
