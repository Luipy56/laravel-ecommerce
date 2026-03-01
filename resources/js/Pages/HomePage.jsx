import React, { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { Product } from '../lib/Product';
import ProductCard from '../components/ProductCard';

export default function HomePage() {
  const { t } = useTranslation();
  const [categories, setCategories] = useState([]);
  const [featured, setFeatured] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const ac = new AbortController();
    Promise.all([
      api.get('categories', { signal: ac.signal }),
      api.get('products/featured', { signal: ac.signal }),
    ])
      .then(([catRes, featRes]) => {
        if (catRes.data.success) setCategories(catRes.data.data || []);
        if (featRes.data.success) setFeatured((featRes.data.data || []).map((p) => Product.fromApi(p)));
      })
      .catch((err) => { if (err.name !== 'AbortError') setFeatured([]); })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, []);

  return (
    <div className="space-y-10">
      <section className="hero hero-gradient rounded-box text-primary-content min-h-[200px] sm:min-h-[240px]">
        <div className="hero-content text-center max-w-2xl px-4 py-8">
          <h1 className="text-3xl sm:text-4xl md:text-5xl font-bold tracking-tight">
            {t('home.hero.title')}
          </h1>
          <p className="mt-2 text-primary-content/90 text-base sm:text-lg">
            {t('home.hero.tagline')}
          </p>
          <div className="mt-6 flex flex-wrap justify-center gap-3">
            <Link to="/products" className="btn btn-secondary btn-md sm:btn-lg text-secondary-content">
              {t('home.hero.cta_products')}
            </Link>
            <Link to="/custom-solution" className="btn btn-ghost btn-md sm:btn-lg border border-primary-content/30 text-primary-content hover:bg-primary-content/10 hover:border-primary-content/50">
              {t('home.hero.cta_custom')}
            </Link>
          </div>
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
          <p className="text-base-content/70 py-8">{t('shop.cart.empty')}</p>
        ) : (
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            {featured.map((product) => (
              <ProductCard key={product.id} product={product} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
