import React, { useCallback, useEffect, useMemo, useRef } from 'react';
import { useSearchParams, useParams, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useInfiniteQuery, useQuery } from '@tanstack/react-query';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';
import PageTitle from '../components/PageTitle';

const defaultPagination = { current_page: 1, last_page: 1, per_page: 15, total: 0 };

/**
 * Query string for filters. When categoryInPath is true, category is only in the URL path (/categories/:id/products), not repeated here.
 */
function buildSearchParams({ selectedCategoryId, featureIds, search, categoryInPath = false, packsOnly = false }) {
  const next = new URLSearchParams();
  if (search) next.set('search', search);
  if (!categoryInPath && selectedCategoryId) next.set('category_id', String(selectedCategoryId));
  featureIds.forEach((id) => next.append('feature_id', id));
  if (packsOnly) next.set('packs_only', '1');
  return next;
}

function mapCatalogFromResponse(r) {
  if (!r.data.success) {
    return { items: [], pagination: { ...defaultPagination } };
  }
  const list = Array.isArray(r.data.data) ? r.data.data : r.data.data?.data ?? [];
  const items = list
    .map((entry) => {
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
    })
    .filter(Boolean);
  const pagination = r.data.meta
    ? {
        current_page: r.data.meta.current_page,
        last_page: r.data.meta.last_page,
        per_page: r.data.meta.per_page,
        total: r.data.meta.total,
      }
    : { ...defaultPagination };
  return { items, pagination };
}

export default function ProductListPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const location = useLocation();
  const { id: categoryIdFromRoute } = useParams();
  const [searchParams, setSearchParams] = useSearchParams();
  const isCategoryRoute = Boolean(categoryIdFromRoute && location.pathname.includes('/categories/'));
  const categoryIdFromQuery = searchParams.get('category_id');

  const selectedCategoryId = useMemo(() => {
    if (isCategoryRoute && categoryIdFromRoute) return String(categoryIdFromRoute);
    if (categoryIdFromQuery) return String(categoryIdFromQuery);
    return null;
  }, [isCategoryRoute, categoryIdFromRoute, categoryIdFromQuery]);

  const featureIds = searchParams.getAll('feature_id');
  const search = searchParams.get('search');
  const packsOnly = searchParams.get('packs_only') === '1';

  const featureIdsKey = featureIds.join(',');

  const catalogQueryKey = useMemo(
    () => ['products', 'catalog', selectedCategoryId ?? '', featureIdsKey, search ?? '', packsOnly ? '1' : '0'],
    [selectedCategoryId, featureIdsKey, search, packsOnly]
  );

  const loadMoreSentinelRef = useRef(null);

  const categoriesQuery = useQuery({
    queryKey: ['categories'],
    queryFn: async ({ signal }) => {
      const r = await api.get('categories', { signal });
      return r.data.success ? r.data.data || [] : [];
    },
  });

  const featuresQuery = useQuery({
    queryKey: ['features'],
    queryFn: async ({ signal }) => {
      const r = await api.get('features', { signal });
      return r.data.success ? r.data.data || [] : [];
    },
  });

  const catalogInfinite = useInfiniteQuery({
    queryKey: catalogQueryKey,
    initialPageParam: 1,
    queryFn: async ({ pageParam, signal }) => {
      const params = { page: pageParam };
      if (packsOnly) {
        params.packs_only = 1;
      } else {
        params.include_packs = true;
      }
      if (selectedCategoryId) params.category_id = selectedCategoryId;
      if (featureIds.length) params.feature_ids = featureIds;
      if (search) params.search = search;
      const r = await api.get('products', { params, signal });
      return mapCatalogFromResponse(r);
    },
    getNextPageParam: (lastPage) => {
      const { current_page: cur, last_page: last } = lastPage.pagination;
      if (cur < last) return cur + 1;
      return undefined;
    },
  });

  useEffect(() => {
    if (searchParams.get('page')) {
      const next = new URLSearchParams(searchParams);
      next.delete('page');
      setSearchParams(next, { replace: true });
    }
  }, [searchParams, setSearchParams]);

  const filterKey = useMemo(
    () => `${selectedCategoryId ?? ''}|${featureIdsKey}|${search ?? ''}|${packsOnly ? '1' : '0'}`,
    [selectedCategoryId, featureIdsKey, search, packsOnly]
  );
  const prevFilterKeyRef = useRef(null);
  useEffect(() => {
    if (prevFilterKeyRef.current === null) {
      prevFilterKeyRef.current = filterKey;
      return;
    }
    if (prevFilterKeyRef.current !== filterKey) {
      prevFilterKeyRef.current = filterKey;
      requestAnimationFrame(() => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
      });
    }
  }, [filterKey]);

  useEffect(() => {
    const el = loadMoreSentinelRef.current;
    if (!el) return undefined;
    const obs = new IntersectionObserver(
      (entries) => {
        const hit = entries.some((e) => e.isIntersecting);
        if (!hit) return;
        if (!catalogInfinite.hasNextPage || catalogInfinite.isFetchingNextPage) return;
        catalogInfinite.fetchNextPage();
      },
      { root: null, rootMargin: '120px', threshold: 0 }
    );
    obs.observe(el);
    return () => obs.disconnect();
  }, [catalogInfinite.hasNextPage, catalogInfinite.isFetchingNextPage, catalogInfinite.fetchNextPage]);

  const setFilters = useCallback(
    (updates) => {
      const next = buildSearchParams({
        selectedCategoryId: updates.selectedCategoryId !== undefined ? updates.selectedCategoryId : selectedCategoryId,
        featureIds: updates.featureIds ?? featureIds,
        search: updates.search ?? search ?? '',
        categoryInPath: isCategoryRoute,
        packsOnly: updates.packsOnly !== undefined ? updates.packsOnly : packsOnly,
      });
      setSearchParams(next);
    },
    [selectedCategoryId, featureIds, search, setSearchParams, isCategoryRoute, packsOnly]
  );

  const handleAllCategories = useCallback(() => {
    const next = buildSearchParams({
      selectedCategoryId: null,
      featureIds: [],
      search: search ?? '',
      categoryInPath: false,
      packsOnly,
    });
    navigate('/products?' + next.toString());
  }, [search, navigate, packsOnly]);

  const selectCategory = useCallback(
    (id) => {
      const sid = String(id);
      const qs = buildSearchParams({
        selectedCategoryId: null,
        featureIds,
        search: search ?? '',
        categoryInPath: true,
        packsOnly,
      }).toString();
      navigate(`/categories/${sid}/products${qs ? `?${qs}` : ''}`);
    },
    [featureIds, search, navigate, packsOnly]
  );

  const toggleFeature = useCallback(
    (id) => {
      const sid = String(id);
      const next = featureIds.includes(sid)
        ? featureIds.filter((f) => f !== sid)
        : [...featureIds, sid];
      setFilters({ featureIds: next });
    },
    [featureIds, setFilters]
  );

  const catalogItems = useMemo(
    () => catalogInfinite.data?.pages.flatMap((p) => p.items) ?? [],
    [catalogInfinite.data?.pages]
  );

  const categoriesList = categoriesQuery.data ?? [];
  const featuresList = featuresQuery.data ?? [];
  const loadingInitial =
    categoriesQuery.isPending || featuresQuery.isPending || catalogInfinite.isPending;

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
        <div className="bg-base-100 rounded-box border border-base-300 p-3">
          <label className="flex items-center justify-between gap-3 cursor-pointer">
            <span className="text-sm font-medium text-base-content">{t('shop.filters.packs_only')}</span>
            <input
              type="checkbox"
              role="switch"
              className="toggle toggle-primary toggle-sm shrink-0"
              checked={packsOnly}
              onChange={() => setFilters({ packsOnly: !packsOnly })}
              aria-checked={packsOnly}
              aria-label={t('shop.filters.packs_only')}
            />
          </label>
        </div>
        <div>
          <h2 className="font-semibold mb-2">{t('shop.categories')}</h2>
          <ul className="menu bg-base-100 rounded-box border border-base-300">
            <li>
              <button type="button" onClick={handleAllCategories} className={selectedCategoryId == null ? 'active' : ''}>
                {t('shop.categories.all')}
              </button>
            </li>
            {categoriesList.map((c) => (
              <li key={c.id}>
                <label className="flex items-center gap-2 cursor-pointer">
                  <input
                    type="radio"
                    name="shop-catalog-category"
                    className="radio radio-sm"
                    checked={selectedCategoryId === String(c.id)}
                    onChange={() => selectCategory(c.id)}
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
        {loadingInitial ? (
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
          <>
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
              {catalogItems.map(({ type, item }) => (
                <ProductCard
                  key={type === 'product' ? `p-${item.id}` : `k-${item.id}`}
                  product={type === 'product' ? item : undefined}
                  pack={type === 'pack' ? item : undefined}
                />
              ))}
            </div>
            {catalogInfinite.hasNextPage ? (
              <div
                ref={loadMoreSentinelRef}
                className="flex justify-center py-8 min-h-[3rem]"
                aria-live="polite"
                aria-busy={catalogInfinite.isFetchingNextPage}
              >
                {catalogInfinite.isFetchingNextPage ? (
                  <span className="loading loading-spinner loading-md" />
                ) : (
                  <span className="sr-only">{t('shop.catalog.scroll_for_more')}</span>
                )}
              </div>
            ) : null}
          </>
        )}
      </div>
    </div>
  );
}
