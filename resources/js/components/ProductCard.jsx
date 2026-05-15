import './ProductCard.scss';
import React, { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';
import FavoriteToggle from './FavoriteToggle';
import { usePublicShopSettings } from '../hooks/usePublicShopSettings';
import ProductPreviewModal from './ProductPreviewModal';
import { catalogFeatureTypeLabel } from '../lib/catalogFeatureTypeLabel';

const FALLBACK_IMAGE = '/images/dummy.jpg';

/**
 * Shared card for products and packs in list and featured grids.
 * Accepts either `product` or `pack`. Clicking the card opens a preview modal;
 * "View more info" inside the modal navigates to the detail page.
 * Packs show a "Pack" badge and at least 2 product names from the pack.
 */
export default function ProductCard({ product, pack }) {
  const { t } = useTranslation();
  const { addLine } = useCart();
  const { data: publicSettings } = usePublicShopSettings();
  const [modalOpen, setModalOpen] = useState(false);

  const isPack = Boolean(pack);
  const isLowStock =
    !isPack &&
    publicSettings?.show_low_stock_badge &&
    publicSettings.low_stock_threshold > 0 &&
    product.stock != null &&
    Number(product.stock) <= publicSettings.low_stock_threshold;
  const detailUrl = isPack ? `/packs/${pack.id}` : `/products/${product.id}`;

  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    if (isPack) addLine(null, pack.id, 1);
    else addLine(product.id, null, 1);
  };

  const handleDragStart = (e) => {
    if (isPack) {
      e.dataTransfer.setData('application/x-pack-id', String(pack.id));
    } else {
      e.dataTransfer.setData('application/x-product-id', String(product.id));
    }
    e.dataTransfer.effectAllowed = 'copy';
  };

  const openModal = () => setModalOpen(true);

  const handleCardKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      openModal();
    }
  };

  const name = isPack ? pack.name : product.name;
  const imageUrl = isPack
    ? (pack.primaryImageUrl ?? pack.images?.[0]?.url ?? FALLBACK_IMAGE)
    : product.primaryImageUrl;
  const formattedPrice = isPack
    ? (pack.formattedPrice ?? (pack.price != null
        ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(pack.price))
        : ''))
    : product.formattedPrice;

  const packProductNames = isPack && Array.isArray(pack.items)
    ? pack.items
        .map((i) => (i.product && (i.product.name ?? i.product.title)) || null)
        .filter(Boolean)
        .slice(0, 2)
    : [];

  return (
    <>
      <div
        className={`product-card${isPack ? ' product-card--pack' : ''}`}
        role="button"
        tabIndex={0}
        onClick={openModal}
        onKeyDown={handleCardKeyDown}
        draggable
        onDragStart={handleDragStart}
        aria-label={name}
      >
        <div className="product-card__image">
          {isPack && (
            <span className="product-card__pack-badge">{t('shop.pack')}</span>
          )}
          {!isPack && product.discount_percent > 0 && (
            <span className="product-card__discount-badge">
              −{Math.round(Number(product.discount_percent))}%
            </span>
          )}
          {isLowStock && (
            <span className="product-card__low-stock-badge">
              {t('shop.product.low_stock')}
            </span>
          )}
          {/* stopPropagation so favorite toggle doesn't open the modal */}
          <div
            className="product-card__favorite"
            onClick={(e) => e.stopPropagation()}
            onKeyDown={(e) => e.stopPropagation()}
          >
            <FavoriteToggle
              productId={isPack ? undefined : product.id}
              packId={isPack ? pack.id : undefined}
            />
          </div>
          <img
            src={imageUrl}
            alt={name}
            onError={(e) => {
              e.target.onerror = null;
              e.target.src = FALLBACK_IMAGE;
            }}
          />
        </div>
        <div className="product-card__info">
          <h3>{name}</h3>
          {isPack ? (
            packProductNames.length > 0 && (
              <ul className="product-card__features" aria-label={t('shop.product.specifications')}>
                {packProductNames.map((productName, i) => (
                  <li key={i}>{productName}</li>
                ))}
              </ul>
            )
          ) : (
            product.features?.length > 0 && (
              <ul className="product-card__features" aria-label={t('shop.product.specifications')}>
                {product.features.slice(0, 2).map((f, i) => {
                  const typeLabel = catalogFeatureTypeLabel(f, t);
                  return (
                    <li key={f.id ?? i}>
                      {typeLabel ? (
                        <span>
                          <span className="product-card__feature-label">{typeLabel}:</span>{' '}
                          {f.value ?? ''}
                        </span>
                      ) : (
                        <span>{f.value ?? ''}</span>
                      )}
                    </li>
                  );
                })}
              </ul>
            )
          )}
          <div className="product-card__footer">
            <div className="product-card__prices">
              {!isPack && product.formattedListPrice && (
                <span className="product-card__old-price">{product.formattedListPrice}</span>
              )}
              <span className="price">{formattedPrice}</span>
            </div>
            <button
              type="button"
              className="cart-btn"
              onClick={handleAdd}
              aria-label={t('shop.cart.add')}
            >
              <IconCart className="h-4 w-4" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>

      {modalOpen && (
        <ProductPreviewModal
          product={isPack ? undefined : product}
          pack={isPack ? pack : undefined}
          detailUrl={detailUrl}
          onClose={() => setModalOpen(false)}
        />
      )}
    </>
  );
}
