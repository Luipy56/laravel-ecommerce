import '../scss/main_shop.scss'
import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useQuery } from '@tanstack/react-query'
import { api } from '../api'
import ProductCard from '../components/ProductCard'
import ProductPreviewModal from '../components/ProductPreviewModal'
import {
  HiArrowRight,
} from 'react-icons/hi2'
import {
  FiTruck,
  FiShield,
  FiHeadphones,
  FiTool,
  FiLock,
  FiSettings,
} from 'react-icons/fi'

const productSkeletons = Array.from({ length: 5 })
const categorySkeletons = Array.from({ length: 4 })

export default function HomePage() {
  const { t } = useTranslation()
  const navigate = useNavigate()

  const [selectedProduct, setSelectedProduct] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const { data: featuredProducts = [], isLoading: isLoadingProducts } = useQuery({
    queryKey: ['featuredProducts'],
    queryFn: async ({ signal }) => {
      const r = await api.get('products/featured', { signal })
      return r.data?.data ?? r.data ?? []
    },
    staleTime: 1000 * 60 * 5,
    retry: 1,
  })

  const { data: categories = [], isLoading: isLoadingCategories } = useQuery({
    queryKey: ['categories'],
    queryFn: async ({ signal }) => {
      const r = await api.get('categories', { signal })
      return r.data?.data ?? r.data ?? []
    },
    staleTime: 1000 * 60 * 5,
    retry: 1,
  })

  const openModal = (product) => {
    setSelectedProduct(product)
    setIsModalOpen(true)
  }

  const closeModal = () => {
    setIsModalOpen(false)
    setSelectedProduct(null)
  }

  return (
    <div className="shop-home">
      <div className="shop-home__top">
        <div className="shop-home__container shop-home__container--hero">
          {/* Hero */}
          <section className="hero-box">
            <div className="hero-box__content">
              <p className="hero-box__eyebrow text-primary">
                {t('home.eyebrow', 'La millor botiga online')}
              </p>
              <h1 id="shop-hero-title" className="hero-box__title text-5xl lg:text-7xl font-black leading-tight tracking-tighter">
                <span className="hero-box__line hero-box__line--nowrap flex flex-wrap items-center gap-x-3 md:gap-x-4">
                  <span>{t('home.hero_prefix', 'El millor')}</span>
                  <span className="text-rotate text-primary duration-12000 inline-grid px-2 ml-2">
                    <span>
                      <span>{t('home.rotate_1', 'preu')}</span>
                      <span>{t('home.rotate_2', 'producte')}</span>
                      <span>{t('home.rotate_3', 'servei')}</span>
                      <span>{t('home.rotate_4', 'suport')}</span>
                    </span>
                  </span>
                </span>
                <span className="hero-box__line">{t('home.hero_suffix', 'en un sol lloc')}</span>
              </h1>
              <p className="hero-box__text text-base-400">
                {t('home.hero_text', 'Descobreix la nostra selecció de productes de qualitat. Garantia i confiança al millor preu.')}
              </p>
              <div className="hero-box__actions">
                <Link to="/products" className="btn btn-primary hero-box__button">
                  <p>{t('home.cta_products', 'Veure productes')}</p>
                  <HiArrowRight className="shop-icon" aria-hidden="true" />
                </Link>
                <Link to="/faq" className="btn btn-secondary hero-box__button">
                  <p>{t('home.cta_faq', 'FAQs')}</p>
                  <HiArrowRight className="shop-icon" aria-hidden="true" />
                </Link>
              </div>
            </div>

            <div className="hero-box__image-wrap">
              <div className="hero-orbit" aria-label={t('home.orbit_label', 'Serveis professionals')}>
                <div className="hero-orbit__outer-ring" aria-hidden="true" />
                <div className="hero-orbit__inner-ring" aria-hidden="true" />
                <div className="hero-orbit__center bg-primary text-base-100" aria-hidden="true">
                  <FiShield className="hero-orbit__center-icon" />
                </div>
                <div className="hero-orbit__items">
                  <div className="hero-orbit__item hero-orbit__item--unlock">
                    <div className="hero-orbit__card" tabIndex={0} aria-label={t('home.orbit_tools', 'Eines')}>
                      <FiTool />
                    </div>
                  </div>
                  <div className="hero-orbit__item hero-orbit__item--lock">
                    <div className="hero-orbit__card" tabIndex={0} aria-label={t('home.orbit_security', 'Seguretat')}>
                      <FiLock />
                    </div>
                  </div>
                  <div className="hero-orbit__item hero-orbit__item--settings">
                    <div className="hero-orbit__card" tabIndex={0} aria-label={t('home.orbit_settings', 'Configuració')}>
                      <FiSettings />
                    </div>
                  </div>
                  <div className="hero-orbit__item hero-orbit__item--shield">
                    <div className="hero-orbit__card" tabIndex={0} aria-label={t('home.orbit_shield', 'Garantia')}>
                      <FiShield />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          {/* Feature strip */}
          <section className="feature-list bg-base-100 border-base-300" aria-label={t('home.features_label', 'Avantatges')}>
            <div className="feature-list__item feature-list__item--line border-base-300">
              <FiTruck className="feature-list__icon text-primary" strokeWidth={1.8} aria-hidden="true" />
              <div>
                <h2 className="feature-list__title">{t('home.feature_delivery', 'Lliurament ràpid')}</h2>
                <p className="feature-list__text text-base-400">{t('home.feature_delivery_desc', 'Entrega en 24-48 hores')}</p>
              </div>
            </div>
            <div className="feature-list__item feature-list__item--line border-base-300">
              <FiShield className="feature-list__icon text-primary" strokeWidth={1.8} aria-hidden="true" />
              <div>
                <h2 className="feature-list__title">{t('home.feature_quality', 'Garantia de qualitat')}</h2>
                <p className="feature-list__text text-base-400">{t('home.feature_quality_desc', 'Productes seleccionats')}</p>
              </div>
            </div>
            <div className="feature-list__item">
              <FiHeadphones className="feature-list__icon text-primary" strokeWidth={1.8} aria-hidden="true" />
              <div>
                <h2 className="feature-list__title">{t('home.feature_support', 'Atenció al client')}</h2>
                <p className="feature-list__text text-base-400">{t('home.feature_support_desc', 'Suport professional')}</p>
              </div>
            </div>
          </section>

          {/* Featured products */}
          <section className="shop-section shop-section--products">
            <div className="section-head">
              <div>
                <p className="section-head__tag text-primary">{t('home.featured_tag', 'El més destacat')}</p>
                <h2 id="featured-products-title" className="section-head__title">
                  {t('home.featured_title', 'Productes destacats')}
                </h2>
              </div>
              <button
                onClick={() => navigate('/products')}
                type="button"
                className="section-link section-link--desktop text-primary"
              >
                {t('home.see_all', "Veure'ls tots")}
                <HiArrowRight className="shop-icon" aria-hidden="true" />
              </button>
            </div>

            <div className="products-grid">
              {isLoadingProducts
                ? productSkeletons.map((_, i) => (
                    <div className="product-card-skeleton" key={`ps-${i}`} aria-hidden="true">
                      <div className="skeleton product-card-skeleton__media" />
                      <div className="product-card-skeleton__body">
                        <div className="skeleton product-card-skeleton__line product-card-skeleton__line--tag" />
                        <div className="skeleton product-card-skeleton__line product-card-skeleton__line--title" />
                        <div className="skeleton product-card-skeleton__line" />
                        <div className="skeleton product-card-skeleton__line product-card-skeleton__line--short" />
                      </div>
                    </div>
                  ))
                : featuredProducts.length > 0
                  ? featuredProducts.map((product, index) => (
                      <div
                        className="shop-card-reveal"
                        key={product.id}
                        style={{ '--reveal-delay': `${Math.min(index, 5) * 70}ms` }}
                      >
                        <ProductCard product={product} onView={openModal} />
                      </div>
                    ))
                  : <p className="shop-empty">{t('home.no_products', 'Ara mateix no hi ha productes destacats')}</p>
              }
            </div>

            <button
              onClick={() => navigate('/products')}
              type="button"
              className="section-link section-link--mobile text-primary"
            >
              {t('home.see_all', "Veure'ls tots")}
              <HiArrowRight className="shop-icon" aria-hidden="true" />
            </button>
          </section>
        </div>
      </div>

      {/* Promo rotating banner */}
      <div className="promo-banner bg-primary h-48 grid place-items-center" aria-label={t('home.promo_label', 'Missatge destacat')}>
        <h3 className="promo-banner__text m-0 h-full w-full flex items-center justify-center px-4 text-base-100 font-black text-center leading-tight">
          <span className="promo-banner__rotator">
            <span>
              <span className="promo-banner__phrase">{t('home.promo_1', '"La qualitat que necessites, al preu que mereixes"')}</span>
              <span className="promo-banner__phrase">{t('home.promo_2', '"Compra segura i enviament ràpid"')}</span>
              <span className="promo-banner__phrase">{t('home.promo_3', '"Productes seleccionats per experts"')}</span>
              <span className="promo-banner__phrase">{t('home.promo_4', '"El teu expert de confiança, sempre a prop"')}</span>
              <span className="promo-banner__phrase">{t('home.promo_5', '"Satisfacció garantida en cada compra"')}</span>
            </span>
          </span>
        </h3>
      </div>

      {/* Categories */}
      <div className="shop-home__categories">
        <div className="shop-home__container shop-home__container--categories">
          <section>
            <div className="section-head">
              <div>
                <p className="section-head__tag text-primary">{t('home.categories_tag', 'Explora el catàleg')}</p>
                <h2 id="featured-categories-title" className="section-head__title">
                  {t('home.categories_title', 'Categories principals')}
                </h2>
              </div>
              <button
                onClick={() => navigate('/products')}
                type="button"
                className="section-link section-link--desktop text-primary"
              >
                {t('home.see_all_categories', 'Veure productes')}
                <HiArrowRight className="shop-icon" aria-hidden="true" />
              </button>
            </div>

            <div className="categories-grid">
              {isLoadingCategories
                ? categorySkeletons.map((_, i) => (
                    <div className="category-card-skeleton" key={`cs-${i}`} aria-hidden="true">
                      <div className="skeleton category-card-skeleton__media" />
                      <div className="category-card-skeleton__body">
                        <div className="skeleton category-card-skeleton__line category-card-skeleton__line--title" />
                        <div className="skeleton category-card-skeleton__line category-card-skeleton__line--link" />
                      </div>
                    </div>
                  ))
                : categories.length > 0
                  ? categories.slice(0, 8).map((category, index) => (
                      <div
                        className="shop-card-reveal"
                        key={category.id}
                        style={{ '--reveal-delay': `${Math.min(index, 5) * 70}ms` }}
                      >
                        <Link
                          to={`/categories/${category.id}/products`}
                          className="product-card border-base-300 flex flex-col overflow-hidden"
                          aria-label={category.name}
                        >
                          <div className="product-card__media">
                            {category.image_path ? (
                              <img
                                src={`/storage/${category.image_path}`}
                                alt={category.name}
                                className="product-card__image"
                              />
                            ) : (
                              <div className="product-card__empty bg-primary/10">
                                <HiArrowRight className="product-card__empty-icon text-primary" />
                              </div>
                            )}
                          </div>
                          <div className="product-card__body">
                            <span className="product-card__name">{category.name}</span>
                          </div>
                        </Link>
                      </div>
                    ))
                  : <p className="shop-empty">{t('home.no_categories', 'No hi ha categories disponibles')}</p>
              }
            </div>

            <button
              onClick={() => navigate('/products')}
              type="button"
              className="section-link section-link--mobile text-primary"
            >
              {t('home.see_all_categories', 'Veure productes')}
              <HiArrowRight className="shop-icon" aria-hidden="true" />
            </button>
          </section>
        </div>
      </div>

      {/* Contact CTA banner */}
      <div className="contact-banner bg-primary" aria-labelledby="contact-banner-title">
        <div className="contact-banner__shine" aria-hidden="true" />
        <div className="contact-banner__content">
          <h3 id="contact-banner-title" className="contact-banner__title text-base-100">
            {t('home.contact_title', 'Necessites ajuda amb la teva comanda?')}
          </h3>
          <p className="contact-banner__text text-base-100">
            {t('home.contact_text', "L'equip està preparat per ajudar-te. Contacta'ns i t'assessorarem sense compromís.")}
          </p>
          <div className="contact-banner__trust">
            <div className="contact-banner__stat">
              <span className="contact-banner__stat-num">24h</span>
              <span className="contact-banner__stat-label">{t('home.stat_response', 'resposta ràpida')}</span>
            </div>
            <div className="contact-banner__stat-divider" aria-hidden="true" />
            <div className="contact-banner__stat">
              <span className="contact-banner__stat-num">100%</span>
              <span className="contact-banner__stat-label">{t('home.stat_personal', 'personalitzat')}</span>
            </div>
            <div className="contact-banner__stat-divider" aria-hidden="true" />
            <div className="contact-banner__stat">
              <span className="contact-banner__stat-num">+5</span>
              <span className="contact-banner__stat-label">{t('home.stat_years', "anys d'experiència")}</span>
            </div>
          </div>
          <button
            onClick={() => navigate('/custom-solution')}
            className="btn btn-secondary contact-banner__button mt-4"
          >
            <span>{t('home.contact_cta', 'Solucions Personalitzades')}</span>
            <HiArrowRight className="shop-icon" aria-hidden="true" />
          </button>
        </div>
      </div>

      {selectedProduct && (
        <ProductPreviewModal
          product={selectedProduct}
          isOpen={isModalOpen}
          onClose={closeModal}
        />
      )}
    </div>
  )
}
