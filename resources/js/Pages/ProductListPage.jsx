import '../scss/main_shop.scss'
import { useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { useQuery } from '@tanstack/react-query'
import { api } from '../api'
import { Product } from '../lib/Product'
import ProductCard from '../components/ProductCard'
import ProductPreviewModal from '../components/ProductPreviewModal'
import {
  HiArrowLeft,
  HiOutlineAdjustmentsHorizontal,
  HiOutlineFunnel,
  HiXMark,
} from 'react-icons/hi2'

const productSkeletons = Array.from({ length: 10 })

export default function ProductListPage() {
  const { t } = useTranslation()
  const { id: categoryId } = useParams()

  const [selectedCategories, setSelectedCategories] = useState(
    categoryId ? [categoryId] : []
  )
  const [selectedFeatures, setSelectedFeatures] = useState([])
  const [selectedProduct, setSelectedProduct] = useState(null)
  const [isModalOpen, setIsModalOpen] = useState(false)

  const { data: productsData, isLoading: isLoadingProducts } = useQuery({
    queryKey: ['products-list', categoryId],
    queryFn: async ({ signal }) => {
      const params = {}
      if (categoryId) params.category_id = categoryId
      const r = await api.get('products', { params, signal })
      const rawList = r.data?.data ?? r.data ?? []
      return Array.isArray(rawList) ? rawList.map((p) => Product.fromApi(p)) : []
    },
    staleTime: 1000 * 60 * 5,
    retry: 1,
  })

  const { data: categoriesData, isLoading: isLoadingCategories } = useQuery({
    queryKey: ['categories'],
    queryFn: async ({ signal }) => {
      const r = await api.get('categories', { signal })
      return r.data?.data ?? r.data ?? []
    },
    staleTime: 1000 * 60 * 5,
    retry: 1,
  })

  const { data: featureTypesData, isLoading: isLoadingFeatures } = useQuery({
    queryKey: ['feature-types'],
    queryFn: async ({ signal }) => {
      const r = await api.get('feature-names', { signal })
      return r.data?.data ?? r.data ?? []
    },
    staleTime: 1000 * 60 * 5,
    retry: 1,
  })

  const products = productsData ?? []
  const categories = categoriesData ?? []
  const featureTypes = featureTypesData ?? []
  const loading = isLoadingProducts || isLoadingCategories || isLoadingFeatures

  const openModal = (product) => {
    setSelectedProduct(product)
    setIsModalOpen(true)
  }

  const closeModal = () => {
    setIsModalOpen(false)
    setSelectedProduct(null)
  }

  const toggleCategory = (id) => {
    setSelectedCategories((prev) =>
      prev.includes(String(id))
        ? prev.filter((c) => c !== String(id))
        : [...prev, String(id)]
    )
  }

  const getFeatureKey = (typeName, value) => `${typeName}-${value}`

  const toggleFeature = (key) => {
    setSelectedFeatures((prev) =>
      prev.includes(key) ? prev.filter((k) => k !== key) : [...prev, key]
    )
  }

  const clearFilters = () => {
    setSelectedCategories(categoryId ? [categoryId] : [])
    setSelectedFeatures([])
  }

  const filteredProducts = products.filter((product) => {
    const categoryMatch =
      selectedCategories.length === 0 ||
      selectedCategories.includes(String(product.category?.id))

    if (selectedFeatures.length === 0) return categoryMatch

    const featuresByType = {}
    selectedFeatures.forEach((key) => {
      const dashIdx = key.indexOf('-')
      const typeName = key.slice(0, dashIdx)
      const value = key.slice(dashIdx + 1)
      if (!featuresByType[typeName]) featuresByType[typeName] = []
      featuresByType[typeName].push(value)
    })

    const featuresMatch = Object.entries(featuresByType).every(
      ([typeName, values]) =>
        product.features?.some(
          (f) => (f.type?.name ?? f.type ?? '') === typeName && values.includes(f.value)
        )
    )

    return categoryMatch && featuresMatch
  })

  return (
    <div className="products-page">
      <div className="products-page__container">
        <div className="products-page__body">
          <div className="products-top">
            <div>
              <Link
                to="/"
                className="link link-hover text-primary mb-2 flex items-center gap-2 cursor-pointer"
              >
                <HiArrowLeft className="size-5" aria-hidden="true" />
                <p>{t('nav.back_home', 'Tornar a l\'inici')}</p>
              </Link>
              <p className="products-top__tag text-primary">{t('products.catalogue', 'Catàleg')}</p>
              <h1 id="products-page-title" className="products-top__title">
                {t('products.title', 'Productes')}
              </h1>
            </div>

            <div className="products-top__actions">
              <p className="products-top__count text-base-400">
                {loading
                  ? t('products.loading', 'Carregant productes')
                  : t('products.count', 'Mostrant {{count}} productes', { count: filteredProducts.length })}
              </p>
              <button
                type="button"
                className="btn products-top__filters-button"
                disabled={loading}
                onClick={() => document.getElementById('products-filters-modal').showModal()}
                aria-label={t('products.open_filters', 'Obrir filtres')}
              >
                <HiOutlineAdjustmentsHorizontal className="filters-box__icon" aria-hidden="true" />
                {t('products.filters', 'Filtres')}
              </button>
            </div>
          </div>

          <div className="products-layout">
            <div>
              <div className="products-list">
                {loading
                  ? productSkeletons.map((_, i) => (
                      <div className="product-card-skeleton" key={`sk-${i}`} aria-hidden="true">
                        <div className="skeleton product-card-skeleton__media" />
                        <div className="product-card-skeleton__body">
                          <div className="skeleton product-card-skeleton__line product-card-skeleton__line--tag" />
                          <div className="skeleton product-card-skeleton__line product-card-skeleton__line--title" />
                          <div className="skeleton product-card-skeleton__line" />
                          <div className="skeleton product-card-skeleton__line product-card-skeleton__line--short" />
                        </div>
                      </div>
                    ))
                  : filteredProducts.length > 0
                    ? filteredProducts.map((product) => (
                        <ProductCard key={product.id} product={product} onView={openModal} />
                      ))
                    : <p className="products-empty">{t('products.empty', 'Ara no hi ha productes disponibles')}</p>
                }
              </div>
            </div>
          </div>

          {!loading && (
            <dialog id="products-filters-modal" className="modal modal-bottom sm:modal-middle">
              <div className="modal-box filters-modal">
                <div className="filters-box">
                  <div className="filters-box__head">
                    <div className="filters-box__head-content">
                      <HiOutlineFunnel className="filters-box__icon" aria-hidden="true" />
                      <h2 className="filters-box__title">{t('products.filters', 'Filtres')}</h2>
                    </div>
                    <div className="modal-action filters-box__modal-close">
                      <button
                        onClick={clearFilters}
                        className="btn btn-ghost btn-sm mr-2"
                      >
                        {t('products.no_filters', 'Sense filtres')}
                      </button>
                      <form method="dialog">
                        <button aria-label={t('products.close_filters', 'Tancar filtres')}>
                          <HiXMark className="filters-box__icon filters-box__close-icon" aria-hidden="true" />
                        </button>
                      </form>
                    </div>
                  </div>

                  <div className="filters-box__content">
                    {/* Categories filter */}
                    <div className="collapse collapse-arrow filters-box__section border-base-300">
                      <input type="checkbox" defaultChecked />
                      <div className="collapse-title filters-box__section-title">
                        <h4 className="filters-box__label text-primary">
                          {t('products.categories', 'Categories')}
                        </h4>
                      </div>
                      <div className="collapse-content filters-box__section-body">
                        <div className="filters-box__list">
                          {categories.map((cat) => (
                            <label key={cat.id} className="filters-box__item">
                              <input
                                type="checkbox"
                                className="checkbox checkbox-sm border-base-300"
                                checked={selectedCategories.includes(String(cat.id))}
                                onChange={() => toggleCategory(cat.id)}
                              />
                              <span className="filters-box__item-name">
                                {cat.name.charAt(0).toUpperCase() + cat.name.slice(1)}
                              </span>
                            </label>
                          ))}
                        </div>
                      </div>
                    </div>

                    {/* Features filter */}
                    {featureTypes.length > 0 && (
                      <div className="collapse collapse-arrow filters-box__section border-base-300">
                        <input type="checkbox" defaultChecked />
                        <div className="collapse-title filters-box__section-title">
                          <h4 className="filters-box__label text-primary">
                            {t('products.features', 'Característiques')}
                          </h4>
                        </div>
                        <div className="collapse-content filters-box__section-body">
                          {featureTypes.map((ft) => (
                            <div key={ft.name ?? ft.id} className="filters-box__type-group">
                              <div className="divider">
                                {(ft.name ?? '').charAt(0).toUpperCase() + (ft.name ?? '').slice(1)}
                              </div>
                              <div className="filters-box__list">
                                {(ft.features ?? []).map((feature) => {
                                  const key = getFeatureKey(ft.name, feature.value)
                                  return (
                                    <label key={key} className="filters-box__item">
                                      <input
                                        type="checkbox"
                                        className="checkbox checkbox-sm border-base-300"
                                        checked={selectedFeatures.includes(key)}
                                        onChange={() => toggleFeature(key)}
                                      />
                                      <span className="filters-box__item-name">
                                        {(feature.value ?? '').charAt(0).toUpperCase() + (feature.value ?? '').slice(1)}
                                      </span>
                                    </label>
                                  )
                                })}
                              </div>
                            </div>
                          ))}
                        </div>
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </dialog>
          )}
        </div>
      </div>

      {selectedProduct && (
        <ProductPreviewModal
          product={selectedProduct}
          isOpen={isModalOpen}
          onClose={closeModal}
          detailUrl={`/products/${selectedProduct.id}`}
        />
      )}
    </div>
  )
}
