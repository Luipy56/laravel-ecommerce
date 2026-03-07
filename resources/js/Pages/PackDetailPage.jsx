import React, { useEffect, useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../api';
import { useCart } from '../contexts/CartContext';
import { IconCart, IconChevronLeft, IconChevronRight } from '../components/icons';

const FALLBACK_IMAGE = '/images/dummy.jpg';

export default function PackDetailPage() {
  const { id } = useParams();
  const { t } = useTranslation();
  const { addLine } = useCart();
  const [pack, setPack] = useState(null);
  const [loading, setLoading] = useState(true);
  const [qty, setQty] = useState(1);
  const [selectedImageIndex, setSelectedImageIndex] = useState(0);

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
    <div className="max-w-6xl mx-auto px-2 sm:px-4">
      <div className="flex justify-end mb-4">
        <Link to="/products" className="btn btn-ghost btn-sm">
          {t('common.back')}
        </Link>
      </div>

      <div className="card card-border bg-base-100 shadow-lg overflow-hidden">
        <div className="flex flex-col lg:flex-row">
          {/* Gallery — same layout as ProductDetailPage */}
          <div className="flex flex-col lg:w-1/2 gap-3 p-4 bg-base-200/50">
            <div className="relative flex-1 aspect-square max-h-[360px] bg-base-200 rounded-lg overflow-hidden">
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
              <img
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
            {hasMultipleImages && (
              <>
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
