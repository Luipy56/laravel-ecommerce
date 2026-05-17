import './HomePage.scss';
import React, { useEffect, useMemo, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';
import useDragScroll from '../hooks/useDragScroll';

function CategoryGrid({ group, t }) {
  const { scrollRef, wrapperRef } = useDragScroll();
  return (
    <div ref={wrapperRef} className="scroll-row-wrapper">
      <div ref={scrollRef} className="products-home-grid">
        {group.products.map((product) => (
          <ProductCard key={product.id} product={product} />
        ))}
        {group.categoryId != null && (
          <Link
            to={`/categories/${group.categoryId}/products`}
            className="slider-btn"
            aria-label={group.categoryName ?? t('shop.categories')}
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <defs>
                <linearGradient id={`chev-${group.key}`} x1="0" y1="0" x2="0" y2="1">
                  <stop offset="0%"   style={{ stopColor: 'var(--chev-from)' }} />
                  <stop offset="100%" style={{ stopColor: 'var(--chev-to)' }} />
                </linearGradient>
              </defs>
              <polyline
                points="9 18 15 12 9 6"
                stroke={`url(#chev-${group.key})`}
                strokeWidth="2.5"
                strokeLinecap="round"
                strokeLinejoin="round"
              />
            </svg>
          </Link>
        )}
      </div>
    </div>
  );
}

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

  const featuredQuery = useQuery({
    queryKey: ['products', 'featured'],
    queryFn: async ({ signal }) => {
      const r = await api.get('products/featured', { signal });
      if (!r.data.success) return [];
      return (r.data.data || []).map((p) => Product.fromApi(p));
    },
  });

  const featured = featuredQuery.data ?? [];
  const featuredByCategory = useMemo(() => groupFeaturedProductsByCategory(featured), [featured]);
  const loading = featuredQuery.isPending;

  useEffect(() => {
    document.title = t('shop.brand_name');
  }, [t]);

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
              <div className="hero__tagline">
                <span className="hero__tagline-prefix">{t('home.hero.tagline_prefix')}</span>
                <span className="text-rotate hero__rotating">
                  <span>
                    <span>{t('home.hero.tagline_w1')}</span>
                    <span>{t('home.hero.tagline_w2')}</span>
                    <span>{t('home.hero.tagline_w3')}</span>
                    <span>{t('home.hero.tagline_w4')}</span>
                    <span>{t('home.hero.tagline_w5')}</span>
                  </span>
                </span>
              </div>
              <Link to="/products" className="primary-btn">
                {t('home.hero.cta_products')}
              </Link>
            </div>
          </div>
        </div>
      </section>

      <section className="trending section">
        <div className="page-container">
          <div className="section-header">
            <h2 className="section-title">{t('shop.featured')}</h2>
          </div>

          {loading ? (
            <div className="trending__loading" role="status" aria-label={t('common.loading')}>
              <span className="loading loading-spinner loading-lg" />
            </div>
          ) : featured.length === 0 ? (
            <p className="trending__empty">{t('shop.featured_empty')}</p>
          ) : (
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
                  <CategoryGrid group={group} t={t} />
                </div>
              ))}
            </div>
          )}
        </div>
      </section>
    </div>
  );
}
