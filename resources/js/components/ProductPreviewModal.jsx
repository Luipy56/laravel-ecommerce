import React, { useEffect, useRef } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import { IconX, IconCart, IconWarning } from './icons';
import { usePublicShopSettings } from '../hooks/usePublicShopSettings';

const FALLBACK_IMAGE = '/images/dummy.jpg';

/**
 * Intermediate preview modal shown when clicking a ProductCard.
 * Shows image, name, price, stock warning, description, and features.
 * "View more info" navigates to the full detail page.
 * Mounted conditionally by ProductCard so DOM stays clean.
 */
export default function ProductPreviewModal({ product, pack, detailUrl, onClose }) {
  const { t } = useTranslation();
  const { addLine } = useCart();
  const { data: publicSettings } = usePublicShopSettings();
  const dialogRef = useRef(null);

  const isPack = Boolean(pack);

  const name = isPack ? pack.name : product.name;
  const imageUrl = isPack
    ? (pack.primaryImageUrl ?? pack.images?.[0]?.url ?? FALLBACK_IMAGE)
    : (product.primaryImageUrl ?? FALLBACK_IMAGE);
  const formattedPrice = isPack
    ? (pack.formattedPrice ?? (pack.price != null
        ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(pack.price))
        : ''))
    : product.formattedPrice;

  const isLowStock =
    !isPack &&
    publicSettings?.show_low_stock_badge &&
    publicSettings.low_stock_threshold > 0 &&
    product.stock != null &&
    Number(product.stock) <= publicSettings.low_stock_threshold;

  const packProductNames = isPack && Array.isArray(pack.items)
    ? pack.items
        .map((i) => (i.product && (i.product.name ?? i.product.title)) || null)
        .filter(Boolean)
    : [];

  useEffect(() => {
    const el = dialogRef.current;
    if (!el) return;
    el.showModal();
    const handleClose = () => onClose?.();
    el.addEventListener('close', handleClose);
    return () => el.removeEventListener('close', handleClose);
  }, [onClose]);

  const handleAdd = (e) => {
    e.stopPropagation();
    if (isPack) addLine(null, pack.id, 1);
    else addLine(product.id, null, 1);
  };

  const handleClose = () => dialogRef.current?.close();

  return (
    <dialog
      ref={dialogRef}
      className="modal modal-bottom sm:modal-middle"
      aria-label={name}
    >
      <div className="modal-box p-0 max-w-[68rem] w-full overflow-hidden flex flex-col sm:flex-row max-h-[92dvh] sm:max-h-[88vh]">

        {/* Close × */}
        <button
          type="button"
          className="btn btn-sm btn-circle btn-ghost absolute top-2 right-2 z-20 bg-base-100/80 backdrop-blur-sm"
          onClick={handleClose}
          aria-label={t('common.close')}
        >
          <IconX className="h-4 w-4" aria-hidden="true" />
        </button>

        {/* Image panel — square crop */}
        <div className="relative sm:w-1/2 shrink-0 bg-base-200">
          {isPack && (
            <span className="absolute top-3 left-3 z-10 bg-primary text-primary-content text-xs font-bold px-2 py-0.5 rounded-md">
              {t('shop.pack')}
            </span>
          )}
          {!isPack && product.discount_percent > 0 && (
            <span className="absolute top-3 left-3 z-10 bg-error text-error-content text-xs font-bold px-2 py-0.5 rounded-md">
              −{Math.round(Number(product.discount_percent))}%
            </span>
          )}
          <img
            src={imageUrl}
            alt={name}
            className="w-full aspect-square sm:h-full sm:aspect-auto object-cover"
            onError={(e) => { e.target.onerror = null; e.target.src = FALLBACK_IMAGE; }}
          />
        </div>

        {/* Content panel — scrollable independently on mobile */}
        <div className="flex flex-col flex-1 overflow-y-auto p-5 gap-3 min-h-0">

          <h2 className="text-lg font-bold leading-snug pr-6">{name}</h2>

          {/* Price row */}
          <div className="flex items-baseline gap-2 flex-wrap">
            {!isPack && product.formattedListPrice && (
              <span className="text-base-content/50 text-sm line-through tabular-nums">
                {product.formattedListPrice}
              </span>
            )}
            <span className="text-primary font-bold text-xl tabular-nums">{formattedPrice}</span>
            {!isPack && product.discount_percent > 0 && (
              <span className="text-error text-sm font-medium">
                −{Math.round(Number(product.discount_percent))}%
              </span>
            )}
          </div>

          {/* Low stock warning */}
          {isLowStock && (
            <div className="alert alert-warning alert-soft text-sm py-2" role="status">
              <IconWarning className="h-4 w-4 shrink-0" aria-hidden="true" />
              <span>{t('shop.product.low_stock_warning')}</span>
            </div>
          )}

          {/* Description */}
          {!isPack && product.description && (
            <p className="text-base-content/80 text-sm leading-relaxed">
              {product.description}
            </p>
          )}

          {/* Features (all) or pack product names */}
          {isPack
            ? packProductNames.length > 0 && (
                <ul className="list-none p-0 m-0 space-y-1">
                  {packProductNames.map((productName, i) => (
                    <li key={i} className="text-sm text-base-content/80 flex items-center gap-2">
                      <span className="w-1.5 h-1.5 rounded-full bg-primary shrink-0" aria-hidden="true" />
                      {productName}
                    </li>
                  ))}
                </ul>
              )
            : product.features?.length > 0 && (
                <div>
                  <p className="text-xs font-semibold text-base-content/50 uppercase tracking-wider mb-1.5">
                    {t('shop.product.specifications')}
                  </p>
                  <ul className="list-none p-0 m-0 space-y-1">
                    {product.features.map((f, i) => (
                      <li key={f.id ?? i} className="text-sm text-base-content/80">
                        {(f.type ?? f.name) != null && String(f.type ?? f.name).trim() !== '' ? (
                          <>
                            <span className="font-medium">{f.type ?? f.name}:</span>{' '}
                            {f.value ?? ''}
                          </>
                        ) : (
                          f.value ?? ''
                        )}
                      </li>
                    ))}
                  </ul>
                </div>
              )}

          {/* Spacer so actions stick to bottom when content is short */}
          <div className="flex-1" />

          {/* Action row: "Ver más" left (smaller), cart button right (card style) */}
          <div className="pt-2 flex items-center justify-between gap-3">
            <Link
              to={detailUrl}
              className="btn btn-sm btn-outline btn-primary"
              onClick={handleClose}
            >
              {t('shop.product.view_more_info')}
            </Link>
            <button
              type="button"
              className="btn btn-primary btn-square rounded-xl shrink-0"
              onClick={handleAdd}
              aria-label={t('shop.cart.add')}
            >
              <IconCart className="h-4 w-4" aria-hidden="true" />
            </button>
          </div>
        </div>
      </div>

      {/* Backdrop click closes */}
      <form method="dialog" className="modal-backdrop">
        <button type="submit">{t('common.close')}</button>
      </form>
    </dialog>
  );
}
