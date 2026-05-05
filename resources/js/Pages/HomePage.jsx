import React, { useMemo } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';

/** Preserves featured order; categories appear in first-seen order. */
function groupFeaturedProductsByCategory(products) {
  const groups = [];
  const indexByKey = new Map();
  for (const product of products) {
    const categoryId = product.category?.id ?? null;
    const key = categoryId != null ? String(categoryId) : '__none__';
    let idx = indexByKey.get(key);
    if (idx === undefined) {
      idx = groups.length;
      indexByKey.set(key, idx);
      groups.push({
        key,
        categoryId,
        categoryName: product.category?.name ?? null,
        products: [],
      });
    }
    groups[idx].products.push(product);
  }
  return groups;
}

export default function HomePage() {
  const { t } = useTranslation();

  const categoriesQuery = useQuery({
    queryKey: ['categories'],
    queryFn: async ({ signal }) => {
      const r = await api.get('categories', { signal });
      return r.data.success ? r.data.data || [] : [];
    },
  });

  const featuredQuery = useQuery({
    queryKey: ['products', 'featured'],
    queryFn: async ({ signal }) => {
      const r = await api.get('products/featured', { signal });
      if (!r.data.success) return [];
      return (r.data.data || []).map((p) => Product.fromApi(p));
    },
  });

  const categories = categoriesQuery.data ?? [];
  const featured = featuredQuery.data ?? [];
  const featuredByCategory = useMemo(() => groupFeaturedProductsByCategory(featured), [featured]);
  const loading = categoriesQuery.isPending || featuredQuery.isPending;

  return (
    <div className="space-y-10">
      <section className="hero hero-gradient rounded-box text-primary-content min-h-[200px] sm:min-h-[240px]">
        <div className="hero-content flex flex-col items-center text-center max-w-2xl px-4 py-8">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight mb-2">
            {t('home.hero.title')}
          </h1>
          <p className="text-primary-content/90 text-base sm:text-lg">
            {t('home.hero.tagline')}
          </p>
        </div>
      </section>

      {categories.length > 0 && (
        <section>
          <h2 className="text-xl font-semibold mb-3">{t('shop.categories')}</h2>
          <div className="flex flex-wrap gap-2">
            {categories.map((c) => (
              <Link
                key={c.id}
                to={`/categories/${c.id}/products`}
                className="btn btn-outline btn-sm"
              >
                {c.name}
              </Link>
            ))}
          </div>
        </section>
      )}

      <section>
        <h2 className="text-xl font-semibold mb-4">{t('shop.featured')}</h2>
        {loading ? (
          <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>
        ) : featured.length === 0 ? (
          <p className="text-base-content/70 py-8">{t('shop.featured_empty')}</p>
        ) : (
          <>
            <div className="space-y-8 sm:hidden">
              {featuredByCategory.map((group) => (
                <section
                  key={group.key}
                  className="min-w-0"
                  aria-labelledby={`home-featured-cat-${group.key}`}
                >
                  <h3
                    id={`home-featured-cat-${group.key}`}
                    className="text-base font-semibold mb-3 text-base-content"
                  >
                    {group.categoryName ?? t('shop.featured_uncategorized')}
                  </h3>
                  <div className="flex flex-row gap-4 overflow-x-auto pb-2 snap-x snap-mandatory -mx-4 px-4 scroll-pl-4">
                    {group.products.map((product) => (
                      <div
                        key={product.id}
                        className="w-[min(82vw,20rem)] shrink-0 snap-start min-w-0"
                      >
                        <ProductCard product={product} />
                      </div>
                    ))}
                  </div>
                </section>
              ))}
            </div>
            <div className="hidden sm:grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              {featured.map((product) => (
                <ProductCard key={product.id} product={product} />
              ))}
            </div>
          </>
        )}
      </section>
    </div>
  );
}
