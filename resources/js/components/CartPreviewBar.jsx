import React from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import { IconCart } from './icons';

const FALLBACK_IMAGE = '/images/dummy.jpg';

const GRADIENT = 'linear-gradient(to right, #F75211, #8B2400)';

// Full-bleed horizontal line that breaks out of any padded container
function GradientLine() {
  return (
    <div
      style={{
        background: GRADIENT,
        height: '4.5px',
        width: '100vw',
        marginLeft: 'calc(50% - 50vw)',
      }}
    />
  );
}

export default function CartPreviewBar() {
  const { cart } = useCart();
  const navigate = useNavigate();
  const { t } = useTranslation();

  const lines = cart?.lines ?? [];
  if (!lines.length) return null;

  return (
    <div className="my-8">
      <GradientLine />

      <div className="overflow-x-auto py-6">
        <div className="flex items-center gap-5 px-4">
          {lines.map((line, idx) => {
            const imageUrl = line.product?.image_url ?? line.pack?.image_url ?? FALLBACK_IMAGE;
            const name = line.product?.name ?? line.pack?.name ?? '';
            return (
              <React.Fragment key={line.id}>
                {idx > 0 && (
                  <span
                    className="font-bold text-4xl select-none shrink-0"
                    style={{ background: GRADIENT, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}
                    aria-hidden="true"
                  >
                    +
                  </span>
                )}
                <img
                  src={imageUrl}
                  alt={name}
                  className="w-[200px] h-[200px] rounded-lg object-cover bg-base-200 shrink-0"
                  onError={(e) => { e.currentTarget.src = FALLBACK_IMAGE; }}
                />
              </React.Fragment>
            );
          })}

          <span
            className="font-bold text-4xl select-none shrink-0"
            style={{ background: GRADIENT, WebkitBackgroundClip: 'text', WebkitTextFillColor: 'transparent' }}
            aria-hidden="true"
          >
            +
          </span>

          <button
            type="button"
            className="btn btn-square shrink-0 w-[200px] h-[200px] rounded-lg border-2 bg-white hover:bg-[#F75211] hover:border-[#F75211] hover:text-white border-[#F75211] text-[#F75211]"
            onClick={() => navigate('/cart')}
            aria-label={t('shop.cart.go_to_cart', 'Anar al carret')}
          >
            <IconCart className="h-16 w-16" aria-hidden="true" />
          </button>
        </div>
      </div>

      <GradientLine />
    </div>
  );
}
