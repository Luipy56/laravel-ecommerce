import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';
import CatalogCardImage from './CatalogCardImage';

const FALLBACK_IMAGE = '/images/dummy.jpg';

/**
 * Card for a pack in catalog lists. Links to pack detail; add button adds pack to cart.
 */
export default function PackCard({ pack }) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { addLine } = useCart();
  const [imageHovered, setImageHovered] = useState(false);

  const primaryImage = pack.images?.[0];
  const imageUrl = pack.primaryImageUrl ?? primaryImage?.url ?? FALLBACK_IMAGE;
  const imageContentType = primaryImage?.content_type ?? null;
  const hasDiscount = pack.discount_percent != null && Number(pack.discount_percent) > 0;
  const formattedPrice = pack.formattedPrice ?? (pack.price != null
    ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(pack.price))
    : '');
  const formattedListPrice = hasDiscount && pack.list_price != null
    ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(pack.list_price))
    : null;

  const goToPack = () => navigate(`/packs/${pack.id}`);

  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    addLine(null, pack.id, 1);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      goToPack();
    }
  };

  return (
    <div
      role="link"
      tabIndex={0}
      className="card card-border bg-base-100 shadow transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer"
      onClick={goToPack}
      onKeyDown={handleKeyDown}
      onMouseEnter={() => setImageHovered(true)}
      onMouseLeave={() => setImageHovered(false)}
      draggable
      onDragStart={(e) => {
        e.dataTransfer.setData('application/x-pack-id', String(pack.id));
        e.dataTransfer.effectAllowed = 'copy';
      }}
    >
      <figure className="h-40 bg-base-200 relative">
        {hasDiscount && (
          <span className="product-card__discount-badge">
            −{Math.round(Number(pack.discount_percent))}%
          </span>
        )}
        <CatalogCardImage
          src={imageUrl}
          alt={pack.name}
          className="object-cover w-full h-full"
          contentType={imageContentType}
          animate={imageHovered}
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = FALLBACK_IMAGE;
          }}
        />
      </figure>
      <div className="card-body p-4 flex flex-col">
        <h3 className="card-title text-base line-clamp-2">{pack.name}</h3>
        <div className="flex items-center justify-between gap-2 mt-auto pt-2">
          <div className="product-card__prices">
            {formattedListPrice && (
              <span className="product-card__old-price">{formattedListPrice}</span>
            )}
            <p className="text-primary font-semibold">{formattedPrice}</p>
          </div>
          <button
            type="button"
            className="btn btn-primary btn-sm gap-1.5 px-3 min-h-8 shrink-0"
            onClick={handleAdd}
            aria-label={t('shop.cart.add')}
          >
            <IconCart className="h-4 w-4 shrink-0" aria-hidden="true" />
            <span className="text-sm font-bold leading-none" aria-hidden="true">+</span>
          </button>
        </div>
      </div>
    </div>
  );
}
