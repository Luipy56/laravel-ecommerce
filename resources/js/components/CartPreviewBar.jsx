import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';

const FALLBACK_IMAGE = '/images/dummy.jpg';

export default function CartPreviewBar() {
  const { cart } = useCart();
  const navigate = useNavigate();
  const { t } = useTranslation();

  const lines = cart?.lines ?? [];
  if (!lines.length) return null;

  return (
    <div className="my-8 overflow-x-auto">
      <div className="flex items-center gap-3 px-4 py-3 border-2 rounded-lg bg-white border-[#F75211] w-fit min-w-full">
        {lines.map((line, idx) => {
          const imageUrl = line.product?.image_url ?? line.pack?.image_url ?? FALLBACK_IMAGE;
          const name = line.product?.name ?? line.pack?.name ?? '';
          return (
            <React.Fragment key={line.id}>
              {idx > 0 && (
                <span
                  className="text-[#F75211] font-bold text-lg select-none shrink-0"
                  aria-hidden="true"
                >
                  +
                </span>
              )}
              <img
                src={imageUrl}
                alt={name}
                className="w-20 h-20 rounded object-cover bg-base-200 shrink-0"
                onError={(e) => { e.currentTarget.src = FALLBACK_IMAGE; }}
              />
            </React.Fragment>
          );
        })}

        <span
          className="text-[#F75211] font-bold text-lg select-none shrink-0 ml-1"
          aria-hidden="true"
        >
          +
        </span>

        <button
          type="button"
          className="btn btn-square btn-outline shrink-0 border-2 border-[#F75211] text-[#F75211] hover:bg-[#F75211] hover:border-[#F75211] hover:text-white"
          onClick={() => navigate('/cart')}
          aria-label={t('shop.cart.go_to_cart', 'Anar al carret')}
        >
          <IconCart className="h-5 w-5" aria-hidden="true" />
        </button>
      </div>
    </div>
  );
}
