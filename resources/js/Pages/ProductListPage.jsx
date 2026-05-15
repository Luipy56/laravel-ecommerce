import './ProductListPage.scss';
import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { useSearchParams, useParams, useNavigate, useLocation } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useStorefrontNavbarVisibility } from '../contexts/StorefrontNavbarVisibilityContext';
import { useInfiniteQuery, useQuery } from '@tanstack/react-query';
import { api } from '../api';
import { Product } from '../lib/Product';
import { catalogFeatureTypeLabel } from '../lib/catalogFeatureTypeLabel';
import ProductCard from '../components/ProductCard';
import PageTitle from '../components/PageTitle';

const defaultPagination = { current_page: 1, last_page: 1, per_page: 15, total: 0 };

const fmt = new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });

function buildSearchParams({ selectedCategoryId, featureIds, search, categoryInPath = false, packsOnly = false, priceMin = null, priceMax = null }) {
  const next = new URLSearchParams();
  if (search) next.set('search', search);
  if (!categoryInPath && selectedCategoryId) next.set('category_id', String(selectedCategoryId));
  featureIds.forEach((id) => next.append('feature_id', id));
  if (packsOnly) next.set('packs_only', '1');
  if (priceMin !== null) next.set('price_min', String(priceMin));
  if (priceMax !== null) next.set('price_max', String(priceMax));
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

function PriceRangeSlider({ globalMin, globalMax, priceMin, priceMax, onChange }) {
  const { t } = useTranslation();
  const [localMin, setLocalMin] = useState(priceMin ?? globalMin);
  const [localMax, setLocalMax] = useState(priceMax ?? globalMax);
  const debounceRef = useRef(null);

  useEffect(() => { setLocalMin(priceMin ?? globalMin); }, [priceMin, globalMin]);
  useEffect(() => { setLocalMax(priceMax ?? globalMax); }, [priceMax, globalMax]);

  const span = globalMax - globalMin || 1;
  const minPct = ((localMin - globalMin) / span) * 100;
  const maxPct = ((localMax - globalMin) / span) * 100;

  const commit = useCallback((min, max) => {
    clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => onChange(min, max), 350);
  }, [onChange]);

  const handleMinChange = (e) => {
    const v = Math.min(Number(e.target.value), localMax - 1);
    setLocalMin(v);
    commit(v, localMax);
  };

  const handleMaxChange = (e) => {
    const v = Math.max(Number(e.target.value), localMin + 1);
    setLocalMax(v);
    commit(localMin, v);
  };

  const isFiltered = (priceMin !== null && priceMin > globalMin) || (priceMax !== null && priceMax < globalMax);

  return (
    <div className="sidebar-block">
      <div className="sidebar-block__header">
        <h4>{t('shop.filters.price')}</h4>
        {isFiltered && (
          <button
            type="button"
            className="sidebar-block__clear"
            onClick={() => { setLocalMin(globalMin); setLocalMax(globalMax); onChange(null, null); }}
          >
            {t('shop.filters.price_any')}
          </button>
        )}
      </div>
      <div className="price-inputs">
        <label className="price-input">
          <input
            type="number"
            min={globalMin}
            max={globalMax}
            value={localMin}
            onChange={(e) => {
              const v = Math.max(globalMin, Math.min(Number(e.target.value) || globalMin, localMax - 1));
              setLocalMin(v);
              commit(v, localMax);
            }}
            onKeyDown={(e) => { if (e.key === 'Enter') e.target.blur(); }}
            aria-label={t('shop.filters.price_min')}
          />
          <span>€</span>
        </label>
        <span className="price-inputs__separator">–</span>
        <label className="price-input">
          <input
            type="number"
            min={globalMin}
            max={globalMax}
            value={localMax}
            onChange={(e) => {
              const v = Math.min(globalMax, Math.max(Number(e.target.value) || globalMax, localMin + 1));
              setLocalMax(v);
              commit(localMin, v);
            }}
            onKeyDown={(e) => { if (e.key === 'Enter') e.target.blur(); }}
            aria-label={t('shop.filters.price_max')}
          />
          <span>€</span>
        </label>
      </div>
      <div className="price-slider">
        <div className="price-slider__track" />
        <div
          className="price-slider__fill"
          style={{ left: `${minPct}%`, right: `${100 - maxPct}%` }}
        />
        <div className="price-slider__thumb" style={{ left: `${minPct}%` }} />
        <div className="price-slider__thumb" style={{ left: `${maxPct}%` }} />
        <input
          type="range"
          min={globalMin}
          max={globalMax}
          step={1}
          value={localMin}
          onChange={handleMinChange}
          className={`price-slider__input${minPct >= 95 ? ' price-slider__input--top' : ''}`}
          aria-label={t('shop.filters.price_min')}
        />
        <input
          type="range"
          min={globalMin}
          max={globalMax}
          step={1}
          value={localMax}
          onChange={handleMaxChange}
          className="price-slider__input"
          aria-label={t('shop.filters.price_max')}
        />
      </div>
    </div>
  );
}

export default function ProductListPage() {
  const { t } = useTranslation();
  const { visible: navbarVisible } = useStorefrontNavbarVisibility();
  const [isLgUp, setIsLgUp] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia('(min-width: 1024px)');
    const sync = () => setIsLgUp(mq.matches);
    sync();
    mq.addEventListener('change', sync);
    return () => mq.removeEventListener('change', sync);
  }, []);

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
  const priceMinParam = searchParams.get('price_min') ? Number(searchParams.get('price_min')) : null;
  const priceMaxParam = searchParams.get('price_max') ? Number(searchParams.get('price_max')) : null;

  const featureIdsKey = featureIds.join(',');

  const catalogQueryKey = useMemo(
    () => ['products', 'catalog', selectedCategoryId ?? '', featureIdsKey, search ?? '', packsOnly ? '1' : '0', priceMinParam ?? '', priceMaxParam ?? ''],
    [selectedCategoryId, featureIdsKey, search, packsOnly, priceMinParam, priceMaxParam]
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

  const priceRangeQuery = useQuery({
    queryKey: ['products', 'price-range'],
    queryFn: async ({ signal }) => {
      const r = await api.get('products/price-range', { signal });
      return r.data.success ? r.data.data : { min: 0, max: 9999 };
    },
    staleTime: 5 * 60 * 1000,
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
      if (priceMinParam !== null) params.price_min = priceMinParam;
      if (priceMaxParam !== null) params.price_max = priceMaxParam;
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
    () => `${selectedCategoryId ?? ''}|${featureIdsKey}|${search ?? ''}|${packsOnly ? '1' : '0'}|${priceMinParam ?? ''}|${priceMaxParam ?? ''}`,
    [selectedCategoryId, featureIdsKey, search, packsOnly, priceMinParam, priceMaxParam]
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
        priceMin: updates.priceMin !== undefined ? updates.priceMin : priceMinParam,
        priceMax: updates.priceMax !== undefined ? updates.priceMax : priceMaxParam,
      });
      setSearchParams(next);
    },
    [selectedCategoryId, featureIds, search, setSearchParams, isCategoryRoute, packsOnly, priceMinParam, priceMaxParam]
  );

  const handleClearAllFilters = useCallback(() => {
    const next = buildSearchParams({
      selectedCategoryId: null,
      featureIds: [],
      search: search ?? '',
      categoryInPath: false,
      packsOnly: false,
      priceMin: null,
      priceMax: null,
    });
    navigate('/products?' + next.toString());
  }, [search, navigate]);

  const selectCategory = useCallback(
    (id) => {
      const sid = String(id);
      if (selectedCategoryId === sid) {
        // clicking the active category deselects it
        const next = buildSearchParams({
          selectedCategoryId: null,
          featureIds: [],
          search: search ?? '',
          packsOnly,
          priceMin: priceMinParam,
          priceMax: priceMaxParam,
        });
        navigate('/products?' + next.toString());
        return;
      }
      const qs = buildSearchParams({
        selectedCategoryId: null,
        featureIds,
        search: search ?? '',
        categoryInPath: true,
        packsOnly,
        priceMin: priceMinParam,
        priceMax: priceMaxParam,
      }).toString();
      navigate(`/categories/${sid}/products${qs ? `?${qs}` : ''}`);
    },
    [selectedCategoryId, featureIds, search, navigate, packsOnly, priceMinParam, priceMaxParam]
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

  const handlePriceChange = useCallback(
    (min, max) => {
      const gMin = priceRangeQuery.data?.min ?? 0;
      const gMax = priceRangeQuery.data?.max ?? 9999;
      setFilters({
        priceMin: min !== null && min > gMin ? min : null,
        priceMax: max !== null && max < gMax ? max : null,
      });
    },
    [setFilters, priceRangeQuery.data]
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
      const groupKey = String(f.feature_name_id ?? f.id);
      if (!map.has(groupKey)) {
        map.set(groupKey, { list: [], heading: '' });
      }
      const entry = map.get(groupKey);
      entry.list.push(f);
      const lbl = catalogFeatureTypeLabel(
        { type: f.feature_name, feature_name_code: f.feature_name_code },
        t
      );
      if (lbl && !entry.heading) entry.heading = lbl;
    }
    return Array.from(map.values()).map(({ list, heading }) => ({
      name: heading || t('shop.filters.feature_group'),
      list,
    }));
  }, [featuresList, t]);

  const globalMin = priceRangeQuery.data?.min ?? 0;
  const globalMax = priceRangeQuery.data?.max ?? 9999;
  const showPriceSlider = !priceRangeQuery.isPending && globalMax > globalMin;

  const hasActiveFilters = selectedCategoryId !== null || featureIds.length > 0 || packsOnly || priceMinParam !== null || priceMaxParam !== null;

  return (
    <div className="catalog-page">
      <section className="catalog section">
        <div className="page-container catalog-layout">
          <aside
            className="sidebar"
            style={{
              '--catalog-sidebar-top': navbarVisible ? (isLgUp ? '4.25rem' : '8rem') : '1rem',
              '--catalog-sidebar-max-h': navbarVisible
                ? isLgUp
                  ? 'calc(100vh - 5.25rem)'
                  : 'calc(100vh - 9rem)'
                : 'calc(100vh - 2rem)',
            }}
          >
            {hasActiveFilters && (
              <button type="button" className="clear-btn" onClick={handleClearAllFilters}>
                {t('shop.filters.clear')}
              </button>
            )}

            <div className="sidebar-block">
              <h4>{t('shop.categories')}</h4>
              <div className="filters-tags">
                {categoriesList.map((c) => (
                  <button
                    key={c.id}
                    type="button"
                    className={`tag${selectedCategoryId === String(c.id) ? ' active' : ''}`}
                    onClick={() => selectCategory(c.id)}
                  >
                    {c.name}
                  </button>
                ))}
              </div>
            </div>

            <div className="sidebar-block">
              <label className="toggle-label">
                <span>{t('shop.filters.packs_only')}</span>
                <input
                  type="checkbox"
                  role="switch"
                  checked={packsOnly}
                  onChange={() => setFilters({ packsOnly: !packsOnly })}
                  aria-checked={packsOnly}
                  aria-label={t('shop.filters.packs_only')}
                />
              </label>
            </div>

            {showPriceSlider && (
              <PriceRangeSlider
                globalMin={globalMin}
                globalMax={globalMax}
                priceMin={priceMinParam}
                priceMax={priceMaxParam}
                onChange={handlePriceChange}
              />
            )}

            {featuresByGroup.map(({ name, list }) => (
              <div key={list[0]?.feature_name_id ?? list[0]?.id ?? name} className="sidebar-block">
                <h4>{name}</h4>
                <div className="checkbox-list">
                  {list.map((f) => (
                    <label key={f.id}>
                      <input
                        type="checkbox"
                        checked={featureIds.includes(String(f.id))}
                        onChange={() => toggleFeature(f.id)}
                        aria-label={f.value}
                      />
                      {f.value}
                    </label>
                  ))}
                </div>
              </div>
            ))}
          </aside>

          <div className="catalog-content">
            <PageTitle className="catalog-title">
              {search ? `${t('common.search')}: ${search}` : t('shop.products')}
            </PageTitle>

            {loadingInitial ? (
              <div className="catalog-loading">
                <span className="loading loading-spinner loading-lg" />
              </div>
            ) : catalogItems.length === 0 ? (
              search ? (
                <div className="catalog-empty">
                  <p className="catalog-empty__title">
                    {t('shop.search.no_results', { query: search })}
                  </p>
                  <ul className="catalog-empty__tips">
                    <li>{t('shop.search.check_spelling')}</li>
                    <li>{t('shop.search.try_different')}</li>
                  </ul>
                </div>
              ) : (
                <p className="catalog-empty__text">{t('shop.no_products')}</p>
              )
            ) : (
              <>
                <div className="products-grid">
                  {catalogItems.map(({ type, item }) => (
                    <ProductCard
                      key={type === 'product' ? `p-${item.id}` : `k-${item.id}`}
                      product={type === 'product' ? item : undefined}
                      pack={type === 'pack' ? item : undefined}
                    />
                  ))}
                </div>
                {catalogInfinite.hasNextPage && (
                  <div
                    ref={loadMoreSentinelRef}
                    className="catalog-sentinel"
                    aria-live="polite"
                    aria-busy={catalogInfinite.isFetchingNextPage}
                  >
                    {catalogInfinite.isFetchingNextPage ? (
                      <span className="loading loading-spinner loading-md" />
                    ) : (
                      <span className="sr-only">{t('shop.catalog.scroll_for_more')}</span>
                    )}
                  </div>
                )}
              </>
            )}
          </div>
        </div>
      </section>
    </div>
  );
}
