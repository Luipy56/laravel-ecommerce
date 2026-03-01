import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Product } from '../lib/Product';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';

/**
 * Shared product card for list and featured grids.
 * Whole card links to product detail; add button adds to cart without navigating.
 */
export default function ProductCard({ product }) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { addLine } = useCart();

  const goToProduct = () => navigate(`/products/${product.id}`);

  const handleAdd = (e) => {
    e.preventDefault();
    e.stopPropagation();
    addLine(product.id, null, 1);
  };

  const handleKeyDown = (e) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      goToProduct();
    }
  };

  return (
    <div
      role="link"
      tabIndex={0}
      className="card card-border bg-base-100 shadow transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 cursor-pointer"
      onClick={goToProduct}
      onKeyDown={handleKeyDown}
      draggable
      onDragStart={(e) => {
        e.dataTransfer.setData('application/x-product-id', String(product.id));
        e.dataTransfer.effectAllowed = 'copy';
      }}
    >
      <figure className="h-40 bg-base-200">
        <img
          src={product.primaryImageUrl}
          alt={product.name}
          className="object-cover w-full h-full"
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = Product.fallbackImageUrl;
          }}
        />
      </figure>
      <div className="card-body p-4 flex flex-col">
        <h3 className="card-title text-base line-clamp-2">{product.name}</h3>
        <div className="flex items-center justify-between gap-2 mt-auto pt-2">
          <p className="text-primary font-semibold">{product.formattedPrice}</p>
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
