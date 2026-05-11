import './HomePage.scss';
import React, { useMemo, useState } from 'react';
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
  const [heroImageFailed, setHeroImageFailed] = useState(false);

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
    <div className="home-page">
      <section className={`hero${heroImageFailed ? ' hero--gradient' : ''}`}>
        {!heroImageFailed && (
          <img
            src="/images/hero.jpg"
            alt=""
            className="hero__image"
            onError={() => setHeroImageFailed(true)}
          />
        )}
        <div className="hero__overlay">
          <div className="page-container">
            <div className="hero__content">
              <h1>{t('home.hero.title')}</h1>
              <p>{t('home.hero.tagline')}</p>
              <Link to="/products" className="primary-btn">
                {t('home.hero.cta_products')}
              </Link>
            </div>
          </div>
        </div>
      </section>

      {categories.length > 0 && (
        <section className="categories section">
          <div className="page-container">
            <h2 className="section-title">{t('shop.categories')}</h2>
            <div className="categories__list">
              {categories.map((c) => (
                <Link key={c.id} to={`/categories/${c.id}/products`} className="tag">
                  {c.name}
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      <section className="trending section">
        <div className="page-container">
          <div className="section-header">
            <h2 className="section-title">{t('shop.featured')}</h2>
            <Link to="/products" className="slider-btn" aria-label={t('shop.featured')} onClick={() => window.scrollTo(0, 0)}>
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><polyline points="9 18 15 12 9 6" /></svg>
            </Link>
          </div>

          {loading ? (
            <div className="trending__loading">
              <span className="loading loading-spinner loading-lg" />
            </div>
          ) : featured.length === 0 ? (
            <p className="trending__empty">{t('shop.featured_empty')}</p>
          ) : (
            <>
              <div className="trending__categories">
                {featuredByCategory.map((group) => (
                  <div
                    key={group.key}
                    className="trending__category-group"
                    aria-labelledby={`home-featured-cat-${group.key}`}
                  >
                    <h3
                      id={`home-featured-cat-${group.key}`}
                      className="trending__category-title"
                    >
                      {group.categoryName ?? t('shop.featured_uncategorized')}
                    </h3>
                    <div className="products-row products-row--scroll">
                      {group.products.map((product) => (
                        <ProductCard key={product.id} product={product} />
                      ))}
                    </div>
                  </div>
                ))}
              </div>
              <div className="products-grid">
                {featured.map((product) => (
                  <ProductCard key={product.id} product={product} />
                ))}
              </div>
            </>
          )}
        </div>
      </section>
    </div>
  );
}
