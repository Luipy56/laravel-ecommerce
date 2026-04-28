import React from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Product } from '../lib/Product';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';

const FALLBACK_IMAGE = '/images/dummy.jpg';

/**
 * Shared card for products and packs in list and featured grids.
 * Accepts either `product` or `pack`. Whole card links to detail; add button adds to cart.
 * Packs show a "Pack" badge and at least 2 product names from the pack.
 */
export default function ProductCard({ product, pack }) {
  const { t } = useTranslation();
  const { addLine } = useCart();

  const isPack = Boolean(pack);
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

  const name = isPack ? pack.name : product.name;
  const imageUrl = isPack
    ? (pack.primaryImageUrl ?? pack.images?.[0]?.url ?? FALLBACK_IMAGE)
    : product.primaryImageUrl;
  const formattedPrice = isPack
    ? (pack.formattedPrice ?? (pack.price != null ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(pack.price)) : ''))
    : product.formattedPrice;

  const packProductNames = isPack && Array.isArray(pack.items)
    ? pack.items
        .map((i) => (i.product && (i.product.name ?? i.product.title)) || null)
        .filter(Boolean)
        .slice(0, 2)
    : [];

  return (
    <div
      className="card card-border bg-base-100 shadow transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer relative"
      draggable
      onDragStart={handleDragStart}
    >
      <Link to={detailUrl} className="block rounded-box overflow-hidden">
        <figure className="h-40 bg-base-200 relative">
          {isPack && (
            <span className="badge badge-primary badge-sm absolute top-2 left-2 z-10">
              {t('shop.pack')}
            </span>
          )}
          {!isPack && product.discount_percent > 0 && (
            <span className="badge badge-error badge-sm absolute top-2 right-2 z-10 font-bold">
              −{Math.round(Number(product.discount_percent))}%
            </span>
          )}
          <img
            src={imageUrl}
            alt={name}
            className="object-cover w-full h-full"
            onError={(e) => {
              e.target.onerror = null;
              e.target.src = FALLBACK_IMAGE;
            }}
          />
        </figure>
        <div className="card-body p-4 flex flex-col">
          <h3 className="card-title text-base line-clamp-2">{name}</h3>
          {isPack ? (
            packProductNames.length > 0 && (
              <ul className="text-sm text-base-content/80 space-y-0.5 mt-1 line-clamp-2" aria-label={t('shop.product.specifications')}>
                {packProductNames.map((productName, i) => (
                  <li key={i}>{productName}</li>
                ))}
              </ul>
            )
          ) : (
            product.features?.length > 0 && (
              <ul className="text-sm text-base-content/80 space-y-0.5 mt-1 line-clamp-2" aria-label={t('shop.product.specifications')}>
                {product.features.slice(0, 2).map((f, i) => (
                  <li key={f.id ?? i}>
                    {(f.type ?? f.name) != null && String(f.type ?? f.name).trim() !== '' ? (
                      <span><span className="font-medium">{f.type ?? f.name}:</span> {f.value ?? ''}</span>
                    ) : (
                      <span>{f.value ?? ''}</span>
                    )}
                  </li>
                ))}
              </ul>
            )
          )}
          <div className="flex flex-col items-start gap-0.5 mt-auto pt-2 pr-12">
            {!isPack && product.formattedListPrice && (
              <span className="text-sm text-base-content/60 line-through tabular-nums">{product.formattedListPrice}</span>
            )}
            <span className="text-primary font-semibold tabular-nums">{formattedPrice}</span>
          </div>
        </div>
      </Link>
      <button
        type="button"
        className="btn btn-primary btn-sm gap-1.5 px-3 min-h-8 shrink-0 absolute bottom-4 right-4 z-10"
        onClick={handleAdd}
        aria-label={t('shop.cart.add')}
      >
        <IconCart className="h-4 w-4 shrink-0" aria-hidden="true" />
        <span className="text-sm font-bold leading-none" aria-hidden="true">+</span>
      </button>
    </div>
  );
}
