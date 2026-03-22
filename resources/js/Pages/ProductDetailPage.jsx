import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { Product } from '../lib/Product';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronLeft, IconChevronRight, IconChevronUp } from '../components/icons';

const ZOOM_SCALE = 3.5;
const ZOOM_PANEL_SIZE = 420;

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
  const [variantsExpanded, setVariantsExpanded] = useState(false);
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
      <div className="flex justify-end mb-4">
        <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
      </div>

      <div className="card card-border bg-base-100 shadow-lg overflow-visible">
        <div className="flex flex-col lg:flex-row">
          {/* Gallery + zoom — overflow-visible so zoom panel is not clipped */}
          <div className="flex flex-col lg:w-1/2 gap-3 p-4 bg-base-200/50 overflow-visible">
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
                {/* Amazon-style zoom: panel to the right of the image — background-image for exact positioning */}
                {zoomVisible && (
                  <div
                    className="hidden lg:block absolute z-10 top-0 left-full ml-2 w-[var(--zoom-size)] h-[var(--zoom-size)] border-2 border-primary bg-base-100 shadow-xl pointer-events-none overflow-hidden rounded-lg bg-no-repeat"
                    style={{
                      '--zoom-size': `${ZOOM_PANEL_SIZE}px`,
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
            {hasMultipleImages && (
              <div className="flex flex-wrap gap-2">
                {imageUrls.map((url, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => setSelectedImageIndex(i)}
                    aria-label={t('shop.product.select_image', { n: i + 1, m: imageUrls.length })}
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
            <div className="mt-2 space-y-0.5">
              {product.formattedListPrice && (
                <p className="text-lg text-base-content/60 line-through tabular-nums">{product.formattedListPrice}</p>
              )}
              <p className="text-primary text-xl font-semibold tabular-nums">{product.formattedPrice}</p>
              {product.discount_percent > 0 && (
                <p className="text-sm text-error font-medium">
                  {t('shop.product.discount_badge')}: −{Math.round(Number(product.discount_percent))}%
                </p>
              )}
            </div>

            {hasVariants && (
              <div className="mt-3" role="group" aria-label={t('shop.product.variant')}>
                <div
                  className={`collapse border border-base-300 rounded-lg bg-base-200/50 ${variantsExpanded ? 'collapse-open' : 'collapse-close'}`}
                >
                  <div
                    className="collapse-title min-h-0 py-2 pr-10 font-medium text-sm text-base-content/80 flex items-center gap-2"
                    role="button"
                    tabIndex={0}
                    aria-expanded={variantsExpanded}
                    aria-label={variantsExpanded ? t('shop.product.hide_variants') : t('shop.product.see_all_variants')}
                    onClick={() => setVariantsExpanded(!variantsExpanded)}
                    onKeyDown={(e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setVariantsExpanded((v) => !v);
                      }
                    }}
                  >
                    <span>{t('shop.product.variants_count', { count: product.variant_options.length })}</span>
                    <span className="text-primary">· {variantsExpanded ? t('shop.product.hide_variants') : t('shop.product.see_all_variants')}</span>
                    <IconChevronUp
                      className={`h-4 w-4 ml-auto shrink-0 transition-transform ${variantsExpanded ? '' : 'rotate-180'}`}
                      aria-hidden
                    />
                  </div>
                  <div className="collapse-content">
                    <ul className="flex flex-wrap gap-2 list-none p-0 m-0 pt-2" role="radiogroup" aria-label={t('shop.product.variant')}>
                      {product.variant_options.map((opt) => {
                        const isSelected = opt.id === product.id;
                        const thumbUrl = opt.image_url && String(opt.image_url).trim() ? opt.image_url : Product.fallbackImageUrl;
                        const label = opt.variant_label || opt.name || opt.code || '';
                        const priceStr = opt.formatted_price != null ? opt.formatted_price : (opt.price != null && Number(opt.price) >= 0
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
                              className={`
                                flex items-center gap-3 w-full min-w-0 max-w-[16rem] rounded-lg border-2 text-left transition-all p-2
                                ${isSelected
                                  ? 'border-primary bg-primary/10 ring-2 ring-primary/30'
                                  : 'border-base-300 bg-base-200 hover:border-primary/50 hover:bg-base-300'
                                }
                              `}
                            >
                              <div className="w-14 h-14 shrink-0 rounded overflow-hidden bg-base-300">
                                <img
                                  src={thumbUrl}
                                  alt=""
                                  className="object-cover w-full h-full"
                                  onError={(e) => { e.target.onerror = null; e.target.src = Product.fallbackImageUrl; }}
                                />
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

            {product.description && (
              <div className="mt-2">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.product.description')}</h2>
                <p className="text-base-content/80 text-sm mt-1">{product.description}</p>
              </div>
            )}

            {(product.features?.length > 0 || product.weight_kg != null || product.is_double_clutch || product.has_card || product.security_level || product.competitor_url) && (
              <div className="mt-3">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.product.specifications')}</h2>
                <ul className="mt-1 space-y-0.5 list-none p-0 m-0">
                  {product.features?.map((f, i) => (
                    <li key={`f-${i}`} className="text-sm text-base-content/80">
                      <span className="font-medium">{f.type}:</span> {f.value}
                    </li>
                  ))}
                  {product.weight_kg != null && (
                    <li className="text-sm text-base-content/80">
                      <span className="font-medium">{t('shop.product.weight')}:</span>{' '}
                      {new Intl.NumberFormat('ca-ES', { maximumFractionDigits: 3 }).format(product.weight_kg)} kg
                    </li>
                  )}
                  {product.is_double_clutch && (
                    <li className="text-sm text-base-content/80">
                      <span className="font-medium">{t('shop.product.double_clutch')}</span>
                    </li>
                  )}
                  {product.has_card && (
                    <li className="text-sm text-base-content/80">
                      <span className="font-medium">{t('shop.product.has_card')}</span>
                    </li>
                  )}
                  {product.security_level && (
                    <li className="text-sm text-base-content/80">
                      <span className="font-medium">{t('shop.product.security_level')}:</span>{' '}
                      {t(`shop.product.security_level.${product.security_level}`)}
                    </li>
                  )}
                  {product.competitor_url && (
                    <li className="text-sm text-base-content/80">
                      <a
                        href={product.competitor_url}
                        className="link link-primary"
                        target="_blank"
                        rel="noopener noreferrer"
                      >
                        {t('shop.product.competitor_link')}
                      </a>
                    </li>
                  )}
                </ul>
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
