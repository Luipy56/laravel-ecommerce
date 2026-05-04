import React, { useEffect, useState, useRef, useCallback } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronLeft, IconChevronRight } from '../components/icons';
import FavoriteToggle from '../components/FavoriteToggle';

const FALLBACK_IMAGE = '/images/dummy.jpg';
const ZOOM_SCALE = 3.5;
const ZOOM_PANEL_SIZE = 420;

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
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }
  if (!pack) {
    return <p className="text-error">{t('common.error')}</p>;
  }

  const imageUrls = pack.images?.length > 0
    ? pack.images.map((img) => img.url)
    : [FALLBACK_IMAGE];
  const mainImageUrl = imageUrls[selectedImageIndex] ?? FALLBACK_IMAGE;
  const hasMultipleImages = imageUrls.length > 1;

  const price = Number(pack.price) || 0;
  const formattedPrice = new Intl.NumberFormat('ca-ES', { style: 'currency', currency: 'EUR' }).format(price);

  const handleAdd = () => {
    addLine(null, pack.id, qty);
  };

  const items = pack.items ?? [];

  return (
    <div className="mx-auto w-full min-w-0 max-w-6xl px-2 sm:px-4">
      <div className="flex justify-end mb-4">
        <Link to="/products" className="btn btn-ghost btn-sm">
          {t('common.back')}
        </Link>
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
                  alt={pack.name}
                  className="object-contain w-full h-full select-none"
                  draggable={false}
                  onError={(e) => {
                    e.target.onerror = null;
                    e.target.src = FALLBACK_IMAGE;
                  }}
                />
              </div>
              {/* Amazon-style zoom: panel to the right of the image */}
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
                <p className="text-sm text-base-content/70">
                  {t('shop.product.image_n_of_m', { n: selectedImageIndex + 1, m: imageUrls.length })}
                </p>
              </>
            )}
          </div>

          {/* Info — aligned with ProductDetailPage */}
          <div className="card-body lg:w-1/2 flex flex-col p-6">
            <h1 className="card-title text-xl sm:text-2xl">{pack.name}</h1>
            <p className="text-primary text-xl font-semibold mt-2">{formattedPrice}</p>

            {pack.description && (
              <div className="mt-2">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.product.description')}</h2>
                <p className="text-base-content/80 text-sm mt-1 whitespace-pre-wrap">{pack.description}</p>
              </div>
            )}

            {items.length > 0 && (
              <div className="mt-3">
                <h2 className="text-sm font-semibold text-base-content/80">{t('shop.pack.contents')}</h2>
                <ul className="mt-1 space-y-0.5 list-none p-0 m-0">
                  {items.map((item) => {
                    const product = item.product;
                    const name = product?.name ?? '';
                    const code = product?.code ?? '';
                    const productId = product?.id ?? item.product_id;
                    const label = name || code || '';
                    return (
                      <li key={item.product_id} className="text-sm text-base-content/80 flex items-center gap-2">
                        {productId ? (
                          <Link
                            to={`/products/${productId}`}
                            className="text-primary hover:underline font-medium"
                          >
                            {label}
                          </Link>
                        ) : (
                          <span className="font-medium">{label}</span>
                        )}
                        {code && name && (
                          <span className="font-mono text-base-content/60">
                            ({code})
                          </span>
                        )}
                      </li>
                    );
                  })}
                </ul>
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
              <FavoriteToggle packId={pack.id} />
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
