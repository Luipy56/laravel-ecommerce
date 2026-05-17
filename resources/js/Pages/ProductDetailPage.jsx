import './ProductDetailPage.scss';
import React, { useState, useRef, useCallback, useEffect } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQuery } from '@tanstack/react-query';
import { api } from '../api';
import { Product } from '../lib/Product';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronUp, IconWarning } from '../components/icons';
import FavoriteToggle from '../components/FavoriteToggle';
import ReviewsSection from '../components/ReviewsSection';
import CartPreviewBar from '../components/CartPreviewBar';
import { usePublicShopSettings } from '../hooks/usePublicShopSettings';
import { catalogFeatureTypeLabel } from '../lib/catalogFeatureTypeLabel';

const ZOOM_SCALE = 3.5;
const ZOOM_PANEL_SIZE = 420;

export default function ProductDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const { t } = useTranslation();
  const [qty, setQty] = useState(1);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [zoomVisible, setZoomVisible] = useState(false);
  const [zoomPos, setZoomPos] = useState({ x: 0.5, y: 0.5 });
  const [variantsExpanded, setVariantsExpanded] = useState(false);
  const imageRef = useRef(null);
  const galleryRef = useRef(null);
  const { addLine } = useCart();
  const { data: publicSettings } = usePublicShopSettings();

  // Paint the page white (override the gray storefront background for this page only)
  useEffect(() => {
    const main = document.querySelector('main');
    if (!main) return;
    const prev = { bg: main.style.background, pl: main.style.paddingLeft, pr: main.style.paddingRight, pt: main.style.paddingTop, pb: main.style.paddingBottom };
    main.style.background = 'white';
    main.style.paddingLeft = '16px';
    main.style.paddingRight = '16px';
    main.style.paddingTop = '16px';
    main.style.paddingBottom = '24px';
    return () => {
      main.style.background = prev.bg;
      main.style.paddingLeft = prev.pl;
      main.style.paddingRight = prev.pr;
      main.style.paddingTop = prev.pt;
      main.style.paddingBottom = prev.pb;
    };
  }, []);

  const productQuery = useQuery({
    queryKey: ['product', 'detail', id],
    queryFn: async ({ signal }) => {
      const r = await api.get(`products/${id}`, { signal });
      if (!r.data?.success) throw new Error('Product response not successful');
      return Product.fromApi(r.data.data);
    },
    enabled: id != null && id !== '',
    staleTime: 60_000,
  });
  const product = productQuery.data;

  const handleAdd = () => addLine(product.id, null, qty);

  const imageUrls = product?.imageUrls ?? [];
  const mainImageUrl = imageUrls[selectedImageIndex] ?? Product.fallbackImageUrl;
  const hasMultipleImages = imageUrls.length > 1;

  const handleZoomMove = useCallback((e) => {
    const containerEl = galleryRef.current;
    const imageEl = imageRef.current;
    if (!containerEl || !imageEl) return;
    const rect = containerEl.getBoundingClientRect();
    const nw = imageEl.naturalWidth || 0;
    const nh = imageEl.naturalHeight || 0;
    const mouseX = e.clientX - rect.left;
    const mouseY = e.clientY - rect.top;
    let x = 0.5;
    let y = 0.5;
    if (nw > 0 && nh > 0 && rect.width > 0 && rect.height > 0) {
      const scale = Math.min(rect.width / nw, rect.height / nh);
      const displayW = nw * scale;
      const displayH = nh * scale;
      const contentLeft = (rect.width - displayW) / 2;
      const contentTop = (rect.height - displayH) / 2;
      x = displayW > 0 ? (mouseX - contentLeft) / displayW : 0.5;
      y = displayH > 0 ? (mouseY - contentTop) / displayH : 0.5;
    } else {
      x = rect.width > 0 ? mouseX / rect.width : 0.5;
      y = rect.height > 0 ? mouseY / rect.height : 0.5;
    }
    setZoomPos({ x: Math.max(0, Math.min(1, x)), y: Math.max(0, Math.min(1, y)) });
  }, []);

  const hasVariants = product?.variant_options?.length > 1;

  const isLowStock =
    publicSettings?.show_low_stock_badge &&
    publicSettings.low_stock_threshold > 0 &&
    product?.stock != null &&
    Number(product.stock) <= publicSettings.low_stock_threshold;

  // ── Loading / error ──────────────────────────────────────────────────────────
  if (productQuery.isPending) {
    return (
      <div className="product-detail">
        <div className="product-detail__back">
          <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="flex justify-center py-12" aria-live="polite" aria-busy="true">
          <span className="loading loading-spinner loading-lg" />
        </div>
      </div>
    );
  }
  if (productQuery.isError || !product) {
    return (
      <div className="product-detail">
        <div className="product-detail__back">
          <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <p className="text-error" role="alert">{t('common.error')}</p>
      </div>
    );
  }

  return (
    <div className="product-detail">
      <div className="product-detail__back">
        <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
      </div>

      <div className="product-detail__card">

        {/* ── LEFT: media block (thumbs + image in one white card) ───────── */}
        <div className="product-detail__media">
          {/* Thumbnail strip — vertical on desktop, horizontal on mobile */}
          {hasMultipleImages && (
            <div className="product-detail__thumbs">
              {imageUrls.map((url, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => setSelectedImageIndex(i)}
                  aria-label={t('shop.product.select_image', { n: i + 1, m: imageUrls.length })}
                  className={`product-detail__thumb${selectedImageIndex === i ? ' product-detail__thumb--active' : ''}`}
                >
                  <img
                    src={url}
                    alt=""
                    onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }}
                  />
                </button>
              ))}
            </div>
          )}

          {/* Main image */}
          <div
            className="product-detail__image-wrap"
            ref={galleryRef}
            onMouseEnter={() => setZoomVisible(true)}
            onMouseLeave={() => setZoomVisible(false)}
            onMouseMove={handleZoomMove}
          >
            <div className="product-detail__image-inner">
              <img
                ref={imageRef}
                src={mainImageUrl}
                alt={product.name}
                draggable={false}
                onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }}
              />
            </div>

            {/* Amazon-style zoom panel — floats to the right of the media block */}
            {zoomVisible && (
              <div
                className="hidden lg:block absolute z-20 top-0 left-full ml-3 border-2 border-primary bg-white shadow-xl pointer-events-none overflow-hidden rounded-xl"
                style={{
                  width: `${ZOOM_PANEL_SIZE}px`,
                  height: `${ZOOM_PANEL_SIZE}px`,
                  backgroundImage: `url(${mainImageUrl})`,
                  backgroundSize: `${ZOOM_SCALE * 100}%`,
                  backgroundPosition: `${100 * (0.5 - zoomPos.x * ZOOM_SCALE) / (1 - ZOOM_SCALE)}% ${100 * (0.5 - zoomPos.y * ZOOM_SCALE) / (1 - ZOOM_SCALE)}%`,
                }}
                role="img"
                aria-label={t('shop.product.image_zoom')}
              />
            )}
          </div>
        </div>

        {/* ── RIGHT: Info panel — no background, sits on page ────────────── */}
        <div className="product-detail__info">

          {product.category?.name && (
            <Link
              to={`/categories/${product.category.id}/products`}
              className="product-detail__category"
            >
              {product.category.name}
            </Link>
          )}

          <h1 className="product-detail__name">{product.name}</h1>

          {product.code && (
            <p className="product-detail__code">
              {t('shop.product.code')}: {product.code}
            </p>
          )}

          <hr className="product-detail__divider" />

          {/* Price */}
          <div className="product-detail__price-row">
            {product.formattedListPrice && (
              <span className="product-detail__old-price">{product.formattedListPrice}</span>
            )}
            <span className="product-detail__price">{product.formattedPrice}</span>
            {product.discount_percent > 0 && (
              <span className="product-detail__discount">
                −{Math.round(Number(product.discount_percent))}%
              </span>
            )}
          </div>

          {/* Low stock warning */}
          {isLowStock && (
            <div className="alert alert-warning alert-soft text-sm py-2" role="status">
              <IconWarning className="h-5 w-5 shrink-0" aria-hidden="true" />
              <span>{t('shop.product.low_stock_warning')}</span>
            </div>
          )}

          {/* Variants */}
          {hasVariants && (
            <div role="group" aria-label={t('shop.product.variant')}>
              <div className={`collapse border border-base-300 rounded-lg bg-base-200/50 ${variantsExpanded ? 'collapse-open' : 'collapse-close'}`}>
                <div
                  className="collapse-title min-h-0 py-2 pr-10 font-medium text-sm text-base-content/80 flex items-center gap-2"
                  role="button"
                  tabIndex={0}
                  aria-expanded={variantsExpanded}
                  onClick={() => setVariantsExpanded(!variantsExpanded)}
                  onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); setVariantsExpanded((v) => !v); }
                  }}
                >
                  <span>{t('shop.product.variants_count', { count: product.variant_options.length })}</span>
                  <span className="text-primary">· {variantsExpanded ? t('shop.product.hide_variants') : t('shop.product.see_all_variants')}</span>
                  <IconChevronUp className={`h-4 w-4 ml-auto shrink-0 transition-transform ${variantsExpanded ? '' : 'rotate-180'}`} aria-hidden />
                </div>
                <div className="collapse-content">
                  <ul className="flex flex-wrap gap-2 list-none p-0 m-0 pt-2" role="radiogroup">
                    {product.variant_options.map((opt) => {
                      const isSelected = opt.id === product.id;
                      const thumbUrl = opt.image_url && String(opt.image_url).trim() ? opt.image_url : Product.fallbackImageUrl;
                      const label = opt.variant_label || opt.name || opt.code || '';
                      const priceStr = opt.formatted_price != null ? opt.formatted_price
                        : (opt.price != null && Number(opt.price) >= 0
                          ? new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(Number(opt.price))
                          : '');
                      return (
                        <li key={opt.id} className="list-none">
                          <button
                            type="button"
                            role="radio"
                            aria-checked={isSelected}
                            aria-label={opt.code ? `${label} (${opt.code}) ${priceStr}` : `${label} ${priceStr}`}
                            onClick={() => { if (!isSelected) navigate(`/products/${opt.id}`); }}
                            className={`flex items-center gap-3 w-full min-w-0 max-w-[16rem] rounded-lg border-2 text-left transition-all p-2 ${
                              isSelected
                                ? 'border-primary bg-primary/10 ring-2 ring-primary/30'
                                : 'border-base-300 bg-base-200 hover:border-primary/50 hover:bg-base-300'
                            }`}
                          >
                            <div className="w-14 h-14 shrink-0 rounded overflow-hidden bg-base-300">
                              <img src={thumbUrl} alt="" className="object-cover w-full h-full"
                                onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }} />
                            </div>
                            <div className="min-w-0 flex-1">
                              {label && <span className="block text-sm font-medium truncate" title={label}>{label}</span>}
                              {priceStr && <span className="block text-sm text-primary font-semibold">{priceStr}</span>}
                            </div>
                          </button>
                        </li>
                      );
                    })}
                  </ul>
                </div>
              </div>
            </div>
          )}

          {/* Description */}
          {product.description && (
            <div>
              <p className="product-detail__section-label">{t('shop.product.description')}</p>
              <p className="product-detail__description">{product.description}</p>
            </div>
          )}

          {/* Specs */}
          {(product.features?.length > 0 || product.weight_kg != null || product.is_double_clutch || product.has_card || product.security_level) && (
            <div>
              <p className="product-detail__section-label">{t('shop.product.specifications')}</p>
              <ul className="product-detail__specs">
                {product.features?.map((f, i) => {
                  const typeLabel = catalogFeatureTypeLabel(f, t);
                  return (
                    <li key={`f-${i}`}>
                      {typeLabel ? <><span className="font-medium">{typeLabel}:</span> {f.value ?? ''}</> : <span>{f.value ?? ''}</span>}
                    </li>
                  );
                })}
                {product.weight_kg != null && (
                  <li><span className="font-medium">{t('shop.product.weight')}:</span>{' '}
                    {new Intl.NumberFormat('ca-ES', { maximumFractionDigits: 3 }).format(product.weight_kg)} kg
                  </li>
                )}
                {product.is_double_clutch && <li><span className="font-medium">{t('shop.product.double_clutch')}</span></li>}
                {product.has_card && <li><span className="font-medium">{t('shop.product.has_card')}</span></li>}
                {product.security_level && (
                  <li><span className="font-medium">{t('shop.product.security_level')}:</span>{' '}
                    {t(`shop.product.security_level.${product.security_level}`)}
                  </li>
                )}
              </ul>
            </div>
          )}

          {/* Extra keys */}
          {product.is_extra_keys_available && (
            <div className="product-detail__extra-keys">
              <p className="font-medium text-sm">{t('shop.product.extra_keys_available')}</p>
              {product.formattedExtraKeyPrice && (
                <p className="text-sm text-primary font-semibold mt-0.5">
                  {t('shop.product.extra_key_price')}: {product.formattedExtraKeyPrice}
                </p>
              )}
            </div>
          )}

          {/* ── Actions ─────────────────────────────────────────────────── */}
          <div className="product-detail__actions">
            <div className="product-detail__qty">
              <span>{t('shop.quantity')}</span>
              <input
                type="number"
                min={1}
                max={99}
                value={qty}
                onChange={(e) => setQty(Math.max(1, parseInt(e.target.value, 10) || 1))}
                aria-label={t('shop.quantity')}
              />
            </div>

            <button
              type="button"
              className="product-detail__add-btn"
              onClick={handleAdd}
            >
              <IconCart className="h-5 w-5 shrink-0" aria-hidden="true" />
              {t('shop.cart.add')}
            </button>

            <div className="product-detail__fav">
              <FavoriteToggle productId={product.id} />
            </div>
          </div>
        </div>
      </div>

      <CartPreviewBar />

      <div style={{ marginTop: '80px' }}>
        <ReviewsSection productId={id} />
      </div>
    </div>
  );
}
