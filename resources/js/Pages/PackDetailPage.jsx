import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronLeft, IconChevronRight } from '../components/icons';
import FavoriteToggle from '../components/FavoriteToggle';
import ReviewsSection from '../components/ReviewsSection';

const FALLBACK_IMAGE = '/images/dummy.jpg';
const ZOOM_SCALE = 3.5;
const ZOOM_PANEL_SIZE = 420;

function formatEur(amount) {
  return new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(amount);
}

function ProductMiniCard({ item }) {
  const product = item.product;
  if (!product) return null;

  const name = product.name ?? product.code ?? '';
  const price = product.price != null ? formatEur(Number(product.price)) : null;
  const imgUrl = product.image_url ?? FALLBACK_IMAGE;

  return (
    <Link
      to={`/products/${product.id}`}
      className="flex items-center gap-3 p-2.5 rounded-lg bg-base-100 border border-base-200 hover:border-primary/40 hover:bg-primary/5 transition-colors group"
    >
      <div className="w-12 h-12 shrink-0 rounded-md overflow-hidden bg-base-200">
        <img
          src={imgUrl}
          alt={name}
          className="w-full h-full object-contain"
          onError={(e) => {
            e.target.onerror = null;
            e.target.src = FALLBACK_IMAGE;
          }}
        />
      </div>
      <div className="min-w-0 flex-1">
        <p className="text-sm font-medium text-base-content group-hover:text-primary transition-colors leading-tight line-clamp-2">
          {name}
        </p>
        {product.code && product.name && (
          <p className="text-xs text-base-content/50 font-mono mt-0.5">{product.code}</p>
        )}
      </div>
      {price && (
        <span className="text-sm font-semibold text-base-content/70 shrink-0 tabular-nums">
          {price}
        </span>
      )}
    </Link>
  );
}

export default function PackDetailPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const { addLine } = useCart();
  const [pack, setPack] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState(1);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);
  const [zoomVisible, setZoomVisible] = useState(false);
  const [zoomPos, setZoomPos] = useState({ x: 0.5, y: 0.5 });
  const imageRef = useRef(null);
  const galleryRef = useRef(null);

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

  useEffect(() => {
    const ac = new AbortController();
    api
      .get(`packs/${id}`, { signal: ac.signal })
      .then((r) => {
        if (r.data.success) setPack(r.data.data);
      })
      .catch((err) => {
        if (err.name !== 'AbortError') setPack(null);
      })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, [id]);

  if (loading) {
    return (
      <div className="flex justify-center py-16">
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }
  if (!pack) {
    return <p className="text-error text-center py-12">{t('common.error')}</p>;
  }

  const imageUrls = pack.images?.length > 0
    ? pack.images.map((img) => img.url)
    : [FALLBACK_IMAGE];
  const mainImageUrl = imageUrls[selectedImageIndex] ?? FALLBACK_IMAGE;
  const hasMultipleImages = imageUrls.length > 1;

  const price = Number(pack.price) || 0;
  const formattedPrice = formatEur(price);

  const items = pack.items ?? [];

  const totalIfSeparate = items.reduce((sum, item) => {
    return sum + (item.product?.price != null ? Number(item.product.price) : 0);
  }, 0);
  const savings = totalIfSeparate - price;
  const hasSavings = totalIfSeparate > 0 && savings > 0.005;

  const handleAdd = () => {
    addLine(null, pack.id, qty);
  };

  return (
    <div className="mx-auto w-full min-w-0 max-w-6xl px-2 sm:px-4 pb-12">
      {/* Back link */}
      <div className="flex justify-start mb-4 pt-2">
        <Link to="/products" className="btn btn-ghost btn-sm gap-1 text-base-content/60 hover:text-base-content">
          <IconChevronLeft className="h-4 w-4" />
          {t('common.back')}
        </Link>
      </div>

      {/* Main card */}
      <div className="card bg-base-100 shadow-lg border border-base-200 overflow-visible">
        <div className="flex flex-col lg:flex-row">

          {/* ── Gallery ── */}
          <div className="flex flex-col lg:w-1/2 gap-3 p-4 bg-base-200/40 overflow-visible lg:rounded-l-2xl">
            <div className="flex flex-col sm:flex-row gap-3">
              <div
                className="relative flex-1 aspect-square max-h-[380px] bg-base-200 rounded-xl"
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
                <div className="absolute inset-0 rounded-xl overflow-hidden">
                  <img
                    ref={imageRef}
                    src={mainImageUrl}
                    alt={pack.name}
                    className="object-contain w-full h-full select-none"
                    draggable={false}
                    onError={(e) => {
                      e.target.onerror = null;
                      e.target.src = FALLBACK_IMAGE;
                    }}
                  />
                </div>
                {zoomVisible && (
                  <div
                    className="hidden lg:block absolute z-10 top-0 left-full ml-2 w-[var(--zoom-size)] h-[var(--zoom-size)] border-2 border-primary bg-base-100 shadow-xl pointer-events-none overflow-hidden rounded-xl bg-no-repeat"
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
              <>
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
                        onError={(e) => {
                          e.target.onerror = null;
                          e.target.src = FALLBACK_IMAGE;
                        }}
                      />
                    </button>
                  ))}
                </div>
                <p className="text-sm text-base-content/60">
                  {t('shop.product.image_n_of_m', { n: selectedImageIndex + 1, m: imageUrls.length })}
                </p>
              </>
            )}
          </div>

          {/* ── Info panel ── */}
          <div className="card-body lg:w-1/2 flex flex-col p-6 gap-4">

            {/* Header: badge + title */}
            <div>
              <div className="flex items-center gap-2 mb-2">
                <span className="badge badge-primary badge-soft font-semibold text-xs tracking-wide uppercase">
                  {t('shop.pack')}
                </span>
                {pack.contains_keys && (
                  <span className="badge badge-warning badge-soft text-xs">
                    🔑 {t('admin.packs.contains_keys')}
                  </span>
                )}
              </div>
              <h1 className="text-2xl sm:text-3xl font-bold tracking-tight text-base-content leading-tight">
                {pack.name}
              </h1>
            </div>

            {/* Price + savings */}
            <div className="flex flex-col gap-1.5">
              <div className="flex flex-wrap items-end gap-3">
                <span className="text-3xl font-extrabold text-primary tabular-nums">
                  {formattedPrice}
                </span>
                {hasSavings && (
                  <span className="text-sm text-base-content/60 line-through tabular-nums">
                    {formatEur(totalIfSeparate)}
                  </span>
                )}
              </div>
              {hasSavings && (
                <div className="inline-flex items-center gap-1.5 bg-success/10 text-success border border-success/20 rounded-lg px-3 py-1.5 w-fit">
                  <svg className="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
                  </svg>
                  <span className="text-sm font-semibold">
                    {t('shop.pack.savings', { amount: formatEur(savings) })}
                  </span>
                </div>
              )}
            </div>

            {/* Description */}
            {pack.description && (
              <div>
                <p className="text-base-content/75 text-sm leading-relaxed whitespace-pre-wrap">
                  {pack.description}
                </p>
              </div>
            )}

            {/* Pack contents block */}
            {items.length > 0 && (
              <div className="rounded-xl border border-base-200 bg-base-50 overflow-hidden">
                <div className="flex items-center justify-between px-4 py-2.5 bg-base-200/60 border-b border-base-200">
                  <h2 className="text-sm font-semibold text-base-content flex items-center gap-2">
                    <svg className="h-4 w-4 text-primary" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M2 4.5A2.5 2.5 0 014.5 2h11A2.5 2.5 0 0118 4.5v.5H2v-.5zM2 7h16v8.5A2.5 2.5 0 0115.5 18h-11A2.5 2.5 0 012 15.5V7z" />
                    </svg>
                    {t('shop.pack.contents')}
                  </h2>
                  <span className="badge badge-ghost badge-sm tabular-nums">{items.length}</span>
                </div>

                {hasSavings && (
                  <div className="px-4 py-2 bg-base-200/30 border-b border-base-200 flex items-center justify-between gap-2 text-xs text-base-content/60">
                    <span>{t('shop.pack.original_total')}</span>
                    <span className="font-semibold tabular-nums text-base-content/70">
                      {formatEur(totalIfSeparate)}
                    </span>
                  </div>
                )}

                <div className="flex flex-col gap-1.5 p-3">
                  {items.map((item) => (
                    <ProductMiniCard key={item.product_id} item={item} />
                  ))}
                </div>
              </div>
            )}

            {/* Add to cart */}
            <div className="flex flex-wrap items-center gap-3 mt-auto pt-2 border-t border-base-200">
              <label className="flex items-center gap-2">
                <span className="text-sm font-medium text-base-content/70">{t('shop.quantity')}</span>
                <input
                  type="number"
                  min={1}
                  max={99}
                  value={qty}
                  onChange={(e) => setQty(Math.max(1, parseInt(e.target.value, 10) || 1))}
                  className="input input-bordered input-sm w-20 tabular-nums"
                />
              </label>
              <FavoriteToggle packId={pack.id} />
              <button
                type="button"
                className="btn btn-primary gap-2 flex-1 sm:flex-none sm:min-w-40"
                onClick={handleAdd}
              >
                <IconCart className="h-4 w-4 shrink-0" aria-hidden="true" />
                {t('shop.cart.add')}
              </button>
            </div>
          </div>
        </div>
      </div>

      {/* Reviews */}
      <div className="mt-8">
        <ReviewsSection packId={pack.id} />
      </div>
    </div>
  );
}
