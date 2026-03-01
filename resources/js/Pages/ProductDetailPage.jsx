import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { Product } from '../lib/Product';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronLeft, IconChevronRight } from '../components/icons';

const ZOOM_SCALE = 2.5;
const ZOOM_PANEL_SIZE = 280;

export default function ProductDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const [product, setProduct] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState(1);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [zoomVisible, setZoomVisible] = useState(false);
  const [zoomPos, setZoomPos] = useState({ x: 0.5, y: 0.5 });
  const imageRef = useRef(null);
  const galleryRef = useRef(null);
  const { addLine } = useCart();

  useEffect(() => {
    const ac = new AbortController();
    api.get(`products/${id}`, { signal: ac.signal })
      .then((r) => {
        if (r.data.success) setProduct(Product.fromApi(r.data.data));
      })
      .catch((err) => { if (err.name !== 'AbortError') setProduct(null); })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, [id]);

  const handleAdd = () => {
    addLine(product.id, null, qty);
  };

  const imageUrls = product?.imageUrls ?? [];
  const mainImageUrl = imageUrls[selectedImageIndex] ?? Product.fallbackImageUrl;
  const hasMultipleImages = imageUrls.length > 1;

  const handleZoomMove = useCallback((e) => {
    const el = imageRef.current;
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const x = (e.clientX - rect.left) / rect.width;
    const y = (e.clientY - rect.top) / rect.height;
    setZoomPos({
      x: Math.max(0, Math.min(1, x)),
      y: Math.max(0, Math.min(1, y)),
    });
  }, []);

  const hasVariants = product?.variant_options?.length > 1;

  if (loading) return <div className="flex justify-center py-12"><span className="loading loading-spinner loading-lg" /></div>;
  if (!product) return <p className="text-error">{t('common.error')}</p>;

  return (
    <div className="max-w-6xl mx-auto px-2 sm:px-4">
      <Link to="/products" className="btn btn-ghost btn-sm mb-4">{t('common.back')}</Link>

      <div className="card card-border bg-base-100 shadow-lg overflow-hidden">
        <div className="flex flex-col lg:flex-row">
          {/* Gallery + zoom */}
          <div className="flex flex-col lg:w-1/2 gap-3 p-4 bg-base-200/50">
            <div className="flex flex-col sm:flex-row gap-3">
              <div
                className="relative flex-1 aspect-square max-h-[360px] bg-base-200 rounded-lg"
                onMouseEnter={() => setZoomVisible(true)}
                onMouseLeave={() => setZoomVisible(false)}
                onMouseMove={handleZoomMove}
                ref={galleryRef}
              >
                {hasMultipleImages && (
                  <>
                    <button
                      type="button"
                      className="absolute left-2 top-1/2 -translate-y-1/2 z-[5] btn btn-circle btn-sm btn-ghost bg-base-100/90 hover:bg-base-100 shadow"
                      onClick={() => setSelectedImageIndex((i) => (i - 1 + imageUrls.length) % imageUrls.length)}
                      aria-label={t('shop.pagination.prev')}
                    >
                      <IconChevronLeft className="h-5 w-5" />
                    </button>
                    <button
                      type="button"
                      className="absolute right-2 top-1/2 -translate-y-1/2 z-[5] btn btn-circle btn-sm btn-ghost bg-base-100/90 hover:bg-base-100 shadow"
                      onClick={() => setSelectedImageIndex((i) => (i + 1) % imageUrls.length)}
                      aria-label={t('shop.pagination.next')}
                    >
                      <IconChevronRight className="h-5 w-5" />
                    </button>
                  </>
                )}
                <div className="absolute inset-0 rounded-lg overflow-hidden">
                  <img
                    ref={imageRef}
                    src={mainImageUrl}
                    alt={product.name}
                    className="object-contain w-full h-full select-none"
                    draggable={false}
                    onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }}
                  />
                </div>
                {/* Amazon-style zoom: panel to the right of the image */}
                {zoomVisible && (
                  <div
                    className="hidden lg:block absolute z-10 top-0 left-full ml-2 w-[var(--zoom-size)] h-[var(--zoom-size)] border-2 border-primary bg-base-100 shadow-xl pointer-events-none overflow-hidden rounded-lg"
                    style={{ '--zoom-size': `${ZOOM_PANEL_SIZE}px` }}
                  >
                    <img
                      src={mainImageUrl}
                      alt=""
                      className="absolute select-none"
                      style={{
                        width: `${ZOOM_SCALE * 100}%`,
                        height: `${ZOOM_SCALE * 100}%`,
                        left: `${(0.5 - zoomPos.x * ZOOM_SCALE) * 100}%`,
                        top: `${(0.5 - zoomPos.y * ZOOM_SCALE) * 100}%`,
                      }}
                      draggable={false}
                    />
                  </div>
                )}
              </div>
            </div>
            {hasMultipleImages && (
              <div className="flex flex-wrap gap-2">
                {imageUrls.map((url, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => setSelectedImageIndex(i)}
                    className={`w-14 h-14 rounded-lg overflow-hidden border-2 shrink-0 transition-all ${
                      selectedImageIndex === i ? 'border-primary ring-2 ring-primary/30' : 'border-base-300 hover:border-base-content/30'
                    }`}
                  >
                    <img
                      src={url}
                      alt=""
                      className="object-cover w-full h-full"
                      onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }}
                    />
                  </button>
                ))}
              </div>
            )}
            {hasMultipleImages && (
              <p className="text-sm text-base-content/70">
                {t('shop.product.image_n_of_m', { n: selectedImageIndex + 1, m: imageUrls.length })}
              </p>
            )}
          </div>

          {/* Info card — aligned with ProductCard style */}
          <div className="card-body lg:w-1/2 flex flex-col p-6">
            {product.category?.name && (
              <Link
                to={`/products?category_id=${product.category.id}`}
                className="text-sm text-primary hover:underline w-fit"
              >
                {product.category.name}
              </Link>
            )}
            <h1 className="card-title text-xl sm:text-2xl mt-1">{product.name}</h1>
            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-base-content/80">
              {product.code && (
                <span>
                  {t('shop.product.code')}: <span className="font-mono">{product.code}</span>
                </span>
              )}
            </div>
            <p className="text-primary text-xl font-semibold mt-2">{product.formattedPrice}</p>

            {hasVariants && (
              <div className="mt-3" role="group" aria-label={t('shop.product.variant')}>
                <p className="text-sm font-medium text-base-content/80 mb-2">{t('shop.product.variant')}</p>
                <ul className="flex flex-wrap gap-2 list-none p-0 m-0" role="radiogroup" aria-label={t('shop.product.variant')}>
                  {product.variant_options.map((opt) => {
                    const isSelected = opt.id === product.id;
                    return (
                      <li key={opt.id} className="contents">
                        <button
                          type="button"
                          role="radio"
                          aria-checked={isSelected}
                          aria-label={opt.code ? `${opt.name} (${opt.code})` : opt.name}
                          onClick={() => { if (!isSelected) navigate(`/products/${opt.id}`); }}
                          className={`
                            min-h-10 px-4 py-2 rounded-lg border-2 text-left transition-all
                            ${isSelected
                              ? 'border-primary bg-primary/10 ring-2 ring-primary/30 font-medium'
                              : 'border-base-300 bg-base-200 hover:border-primary/50 hover:bg-base-300'
                            }
                          `}
                        >
                          <span className="block text-sm truncate max-w-[10rem] sm:max-w-[14rem]" title={opt.name}>{opt.name}</span>
                          {opt.code && <span className="block text-xs text-base-content/60 font-mono">{opt.code}</span>}
                        </button>
                      </li>
                    );
                  })}
                </ul>
              </div>
            )}

            {product.description && (
              <div className="mt-2">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.product.description')}</h2>
                <p className="text-base-content/80 text-sm mt-1">{product.description}</p>
              </div>
            )}

            {product.features?.length > 0 && (
              <div className="mt-3">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.product.specifications')}</h2>
                <ul className="mt-1 space-y-0.5">
                  {product.features.map((f, i) => (
                    <li key={i} className="text-sm text-base-content/80">
                      <span className="font-medium">{f.type}:</span> {f.value}
                    </li>
                  ))}
                </ul>
              </div>
            )}

            {product.is_installable && (
              <div className="mt-3 p-3 rounded-lg bg-base-200/80">
                <p className="text-sm font-medium text-base-content">{t('shop.product.installation_available')}</p>
                {product.formattedInstallationPrice && (
                  <p className="text-sm text-primary font-semibold">{t('shop.product.installation_price')}: {product.formattedInstallationPrice}</p>
                )}
              </div>
            )}

            {product.is_extra_keys_available && (
              <div className="mt-3 p-3 rounded-lg bg-base-200/80">
                <p className="text-sm font-medium text-base-content">{t('shop.product.extra_keys_available')}</p>
                {product.formattedExtraKeyPrice && (
                  <p className="text-sm text-primary font-semibold">{t('shop.product.extra_key_price')}: {product.formattedExtraKeyPrice}</p>
                )}
              </div>
            )}

            <div className="flex flex-wrap items-center gap-4 mt-auto pt-6">
              <label className="flex items-center gap-2">
                <span className="text-sm font-medium">{t('shop.quantity')}</span>
                <input
                  type="number"
                  min={1}
                  max={99}
                  value={qty}
                  onChange={(e) => setQty(Math.max(1, parseInt(e.target.value, 10) || 1))}
                  className="input input-bordered input-sm w-20"
                />
              </label>
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
      </div>
    </div>
  );
}
