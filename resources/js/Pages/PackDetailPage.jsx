import './PackDetailPage.scss';
import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useCart } from '../contexts/CartContext';
import { IconCart } from '../components/icons';
import FavoriteToggle from '../components/FavoriteToggle';
import ReviewsSection from '../components/ReviewsSection';
import CatalogCardImage from '../components/CatalogCardImage';
import { useDocumentMeta } from '../hooks/useDocumentMeta';

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
          onError={(e) => { e.target.onerror = null; e.target.src = FALLBACK_IMAGE; }}
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
  const [hoveredThumbIndex, setHoveredThumbIndex] = useState(null);
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
    let x = 0.5, y = 0.5;
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

  useEffect(() => {
    const ac = new AbortController();
    api
      .get(`packs/${id}`, { signal: ac.signal })
      .then((r) => { if (r.data.success) setPack(r.data.data); })
      .catch((err) => { if (err.name !== 'AbortError') setPack(null); })
      .finally(() => setLoading(false));
    return () => ac.abort();
  }, [id]);

  useDocumentMeta(
    pack?.name ?? undefined,
    pack?.description ? String(pack.description).slice(0, 200) : undefined,
  );

  if (loading) {
    return (
      <div className="product-detail-page">
        <div className="pack-detail__back">
          <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="flex justify-center py-12" aria-live="polite" aria-busy="true">
          <span className="loading loading-spinner loading-lg" />
        </div>
      </div>
    );
  }
  if (!pack) {
    return (
      <div className="product-detail-page">
        <div className="pack-detail__back">
          <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <p className="text-error" role="alert">{t('common.error')}</p>
      </div>
    );
  }

  const galleryImages = pack.images?.length > 0
    ? pack.images
    : [{ url: FALLBACK_IMAGE, content_type: null }];
  const imageUrls = galleryImages.map((img) => img.url);
  const mainImageUrl = imageUrls[selectedImageIndex] ?? FALLBACK_IMAGE;
  const hasMultipleImages = imageUrls.length > 1;

  const price = Number(pack.price) || 0;
  const listPrice = pack.list_price != null ? Number(pack.list_price) : null;
  const discountPercent = pack.discount_percent != null && Number(pack.discount_percent) > 0
    ? Number(pack.discount_percent) : null;
  const formattedPrice = formatEur(price);
  const formattedListPrice = listPrice != null ? formatEur(listPrice) : null;

  const items = pack.items ?? [];
  const totalIfSeparate = items.reduce((sum, item) =>
    sum + (item.product?.price != null ? Number(item.product.price) : 0), 0);
  const savings = totalIfSeparate - price;
  const hasSavings = totalIfSeparate > 0 && savings > 0.005;

  const handleAdd = () => addLine(null, pack.id, qty);

  return (
    <div className="product-detail-page">
      <div className="pack-detail">
      <div className="pack-detail__back">
        <Link to="/products" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
      </div>

      <div className="pack-detail__card">

        {/* ── LEFT: media (thumbs + image) ──────────────────────────────── */}
        <div className="pack-detail__media">

          {/* Vertical thumbnail strip */}
          {hasMultipleImages && (
            <div className="pack-detail__thumbs">
              {galleryImages.map((img, i) => (
                <button
                  key={i}
                  type="button"
                  onClick={() => setSelectedImageIndex(i)}
                  onMouseEnter={() => setHoveredThumbIndex(i)}
                  onMouseLeave={() => setHoveredThumbIndex(null)}
                  aria-label={t('shop.product.select_image', { n: i + 1, m: imageUrls.length })}
                  className={`pack-detail__thumb${selectedImageIndex === i ? ' pack-detail__thumb--active' : ''}`}
                >
                  <CatalogCardImage
                    src={img.url}
                    contentType={img.content_type}
                    alt=""
                    animate={hoveredThumbIndex === i}
                    className="object-cover w-full h-full"
                    onError={(e) => { e.target.onerror = null; e.target.src = FALLBACK_IMAGE; }}
                  />
                </button>
              ))}
            </div>
          )}

          {/* Main image */}
          <div
            className="pack-detail__image-wrap"
            ref={galleryRef}
            onMouseEnter={() => setZoomVisible(true)}
            onMouseLeave={() => setZoomVisible(false)}
            onMouseMove={handleZoomMove}
          >
            <div className="pack-detail__image-inner">
              <img
                ref={imageRef}
                src={mainImageUrl}
                alt={pack.name}
                draggable={false}
                onError={(e) => { e.target.onerror = null; e.target.src = FALLBACK_IMAGE; }}
              />
            </div>

            {/* Zoom panel */}
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

        {/* ── RIGHT: info panel ─────────────────────────────────────────── */}
        <div className="pack-detail__info">

          {/* Badge row */}
          <div className="pack-detail__badge-row">
            <span className="badge badge-primary badge-soft font-semibold text-xs tracking-wide uppercase">
              {t('shop.pack')}
            </span>
            {pack.contains_keys && (
              <span className="badge badge-warning badge-soft text-xs">
                🔑 {t('admin.packs.contains_keys')}
              </span>
            )}
            {discountPercent != null && (
              <span className="pack-detail__discount">
                −{Math.round(discountPercent)}%
              </span>
            )}
          </div>

          <h1 className="pack-detail__name">{pack.name}</h1>

          <hr className="pack-detail__divider" />

          {/* Price */}
          <div className="pack-detail__price-row">
            {formattedListPrice && (
              <span className="pack-detail__old-price">{formattedListPrice}</span>
            )}
            {!formattedListPrice && hasSavings && (
              <span className="pack-detail__old-price">{formatEur(totalIfSeparate)}</span>
            )}
            <span className="pack-detail__price">{formattedPrice}</span>
          </div>

          {/* Savings callout */}
          {hasSavings && (
            <div className="pack-detail__savings">
              <svg className="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clipRule="evenodd" />
              </svg>
              {t('shop.pack.savings', { amount: formatEur(savings) })}
            </div>
          )}

          {/* Description */}
          {pack.description && (
            <p className="pack-detail__description">{pack.description}</p>
          )}

          {/* Pack contents */}
          {items.length > 0 && (
            <div className="pack-detail__contents">
              <div className="pack-detail__contents-header">
                <span className="flex items-center gap-2">
                  <svg className="h-4 w-4 text-primary shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M2 4.5A2.5 2.5 0 014.5 2h11A2.5 2.5 0 0118 4.5v.5H2v-.5zM2 7h16v8.5A2.5 2.5 0 0115.5 18h-11A2.5 2.5 0 012 15.5V7z" />
                  </svg>
                  {t('shop.pack.contents')}
                </span>
                <span className="badge badge-ghost badge-sm tabular-nums">{items.length}</span>
              </div>
              {hasSavings && (
                <div className="pack-detail__contents-original">
                  <span>{t('shop.pack.original_total')}</span>
                  <span className="font-semibold tabular-nums">{formatEur(totalIfSeparate)}</span>
                </div>
              )}
              <div className="pack-detail__contents-list">
                {items.map((item) => (
                  <ProductMiniCard key={item.product_id} item={item} />
                ))}
              </div>
            </div>
          )}

          {/* Actions */}
          <div className="pack-detail__actions">
            <div className="pack-detail__qty">
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
              className="pack-detail__add-btn"
              onClick={handleAdd}
            >
              <IconCart className="h-4 w-4 shrink-0" aria-hidden="true" />
              {t('shop.cart.add')}
            </button>
            <div className="pack-detail__fav">
              <FavoriteToggle packId={pack.id} />
            </div>
          </div>
        </div>
      </div>

      <div className="product-detail__reviews">
        <ReviewsSection packId={pack.id} />
      </div>
      </div>
    </div>
  );
}
