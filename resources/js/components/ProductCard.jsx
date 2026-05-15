import { useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useCart } from '../contexts/CartContext'
import { HiOutlinePhoto, HiOutlineEye } from 'react-icons/hi2'
import ProductPreviewModal from './ProductPreviewModal'

const FALLBACK_IMAGE = '/images/dummy.jpg'

/**
 * Unified product/pack card using rubenserra BEM design.
 * Accepts `product` or `pack`. If `onView` is provided, calls it on click;
 * otherwise opens an internal ProductPreviewModal.
 */
export default function ProductCard({ product, pack, onView }) {
  const { t } = useTranslation()
  const { addLine } = useCart()
  const [modalOpen, setModalOpen] = useState(false)

  const isPack = Boolean(pack)
  const item = isPack ? pack : product

  if (!item) return null

  const name = item.name ?? ''
  const imageUrl = isPack
    ? (pack.primaryImageUrl ?? pack.images?.[0]?.url ?? null)
    : (product.primaryImageUrl ?? null)

  const formattedPrice = isPack
    ? (pack.formattedPrice ?? '')
    : (product.formattedPrice ?? '')

  const discountPercent = !isPack && product.discount_percent > 0
    ? Math.round(Number(product.discount_percent))
    : 0

  const formattedListPrice = !isPack ? product.formattedListPrice : null

  const categoryName = !isPack ? (product.category?.name ?? '') : 'Pack'

  const handleCardClick = (e) => {
    if (e) {
      e.preventDefault()
      e.stopPropagation()
    }
    if (onView) {
      onView(item)
    } else {
      setModalOpen(true)
    }
  }

  const handleCardKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault()
      handleCardClick()
    }
  }

  const handleAdd = (e) => {
    e.stopPropagation()
    if (isPack) addLine(null, pack.id, 1)
    else addLine(product.id, null, 1)
  }

  const cardLabel = isPack
    ? `${name}. Pack. ${formattedPrice}`
    : `${name}. ${categoryName}. ${formattedPrice}`

  return (
    <>
      <div
        className="product-card border-base-300 product-card--interactive"
        onClick={handleCardClick}
        onKeyDown={handleCardKeyDown}
        role="button"
        tabIndex={0}
        aria-label={cardLabel}
      >
        <div className="product-card__media">
          {discountPercent > 0 && (
            <div
              className="product-card__discount bg-primary"
              aria-label={`Descompte del ${discountPercent}%`}
            >
              <p>-{discountPercent}%</p>
            </div>
          )}

          {isPack && (
            <div className="product-card__discount bg-secondary" aria-label="Pack">
              <p>Pack</p>
            </div>
          )}

          <div className={`product-card__image-box ${imageUrl ? 'product-card__image-box--has-image' : ''}`}>
            {imageUrl ? (
              <img
                src={imageUrl}
                alt={name}
                className="product-card__image"
                onError={(e) => { e.target.onerror = null; e.target.src = FALLBACK_IMAGE }}
              />
            ) : (
              <div className="product-card__empty bg-primary/10" aria-label={`Sense imatge per a ${name}`}>
                <HiOutlinePhoto className="product-card__empty-icon text-primary" aria-hidden="true" />
              </div>
            )}

            <div className="product-card__actions">
              <button
                type="button"
                className="product-card__action product-card__action--view"
                onClick={(e) => { e.stopPropagation(); handleCardClick() }}
                aria-label={`Veure ${name}`}
              >
                <HiOutlineEye className="product-card__action-icon" aria-hidden="true" />
                <span className="product-card__action-text">
                  {t('shop.product.view', 'Visualitzar')}
                </span>
              </button>
            </div>
          </div>
        </div>

        <div className="product-card__body">
          <p className="product-card__category text-base-400">{categoryName}</p>
          <span className="product-card__name">{name}</span>

          {!isPack && product.description && (
            <p className="product-card__desc text-base-400">
              {product.description}
            </p>
          )}

          <div className="product-card__bottom">
            <div className={`product-card__price-group ${discountPercent > 0 ? 'product-card__price-group--discount' : ''}`}>
              <p className="product-card__price">{formattedPrice}</p>
              {formattedListPrice && (
                <p className="product-card__old-price text-base-400">{formattedListPrice}</p>
              )}
            </div>
          </div>
        </div>
      </div>

      {modalOpen && !onView && (
        <ProductPreviewModal
          product={isPack ? undefined : product}
          pack={isPack ? pack : undefined}
          detailUrl={isPack ? `/packs/${pack.id}` : `/products/${product.id}`}
          onClose={() => setModalOpen(false)}
        />
      )}
    </>
  )
}
