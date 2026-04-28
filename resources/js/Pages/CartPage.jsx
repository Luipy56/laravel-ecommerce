import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useCart } from '../contexts/CartContext';
import PageTitle from '../components/PageTitle';

function CartLine({ line, updateLine, removeLine, t }) {
  const isProduct = !!line.product;
  const isPack = !!line.pack;
  const packContainsKeys = !!line.pack?.contains_keys;
  const canChooseKeysDifferent = isPack && packContainsKeys;
  const keysAllSame = !!line.keys_all_same;
  const name = line.product?.name ?? line.pack?.name;
  const imageUrl = line.product?.image_url ?? line.pack?.image_url ?? '/images/dummy.jpg';
  const isIncluded = line.is_included !== false;
  const isExtraKeysAvailable = !!line.product?.is_extra_keys_available;
  const extraKeyUnitPrice = line.product?.extra_key_unit_price ?? null;
  const extraKeysQty = line.extra_keys_qty ?? 0;
  const features = line.product?.features ?? [];

  const handleIncludeChange = () => {
    updateLine(line.id, { quantity: line.quantity, included: !isIncluded });
  };

  const handleQuantityChange = (e) => {
    const v = parseInt(e.target.value, 10);
    if (!Number.isNaN(v)) updateLine(line.id, Math.max(0, Math.min(99, v)));
  };

  const handleExtraKeysChange = (e) => {
    const v = parseInt(e.target.value, 10);
    if (!Number.isNaN(v)) {
      updateLine(line.id, {
        quantity: line.quantity,
        extra_keys_qty: Math.max(0, Math.min(99, v)),
      });
    }
  };

  const handleKeysAllDifferentChange = () => {
    if (!canChooseKeysDifferent) return;
    updateLine(line.id, { quantity: line.quantity, keys_all_same: !keysAllSame });
  };

  const detailUrl = isProduct ? `/products/${line.product.id}` : `/packs/${line.pack.id}`;

  return (
    <tr className={isIncluded ? '' : 'opacity-60 saturate-0'}>
      <td className="align-middle text-center">
        <input
          type="checkbox"
          className="checkbox checkbox-sm checkbox-primary"
          checked={isIncluded}
          onChange={handleIncludeChange}
          aria-label={t('shop.cart.include')}
        />
      </td>
      <td className="align-middle">
        <Link
          to={detailUrl}
          className="flex items-center gap-3 no-underline text-base-content hover:text-primary transition-colors block"
        >
          <figure className="mask mask-squircle w-16 h-16 shrink-0 bg-base-300 flex items-center justify-center overflow-hidden">
            <img
              src={imageUrl}
              alt={name ? t('shop.cart.line_image_alt', { name }) : ''}
              className="w-full h-full object-cover"
            />
          </figure>
          <div>
            <p className="font-medium">{name}</p>
            {features.length > 0 && (
              <p className="text-sm text-base-content/70 mt-0.5">
                {features.map((f) => `${f.name}: ${f.value}`).join(' · ')}
              </p>
            )}
          </div>
        </Link>
      </td>
      <td className="text-center align-middle whitespace-nowrap">{Number(line.unit_price).toFixed(2)} €</td>
      <td className="align-middle">
        <div className="flex justify-center">
          <input
            type="number"
            min={1}
            max={99}
            value={line.quantity}
            onChange={handleQuantityChange}
            className="input input-bordered input-sm w-20 text-center"
          />
        </div>
      </td>
      <td className="align-middle text-center">
        {isExtraKeysAvailable ? (
          <div className="flex flex-col items-center gap-0.5">
            <input
              type="number"
              min={0}
              max={99}
              value={extraKeysQty}
              onChange={handleExtraKeysChange}
              className="input input-bordered input-sm w-16 text-center"
              aria-label={t('shop.cart.extra_keys')}
            />
            {extraKeyUnitPrice != null && (
              <span className="text-xs text-base-content/70">{Number(extraKeyUnitPrice).toFixed(2)} €/u</span>
            )}
          </div>
        ) : (
          ''
        )}
      </td>
      <td className="align-middle text-center">
        <input
          type="checkbox"
          className="checkbox checkbox-sm checkbox-primary"
          checked={keysAllSame}
          onChange={handleKeysAllDifferentChange}
          disabled={!canChooseKeysDifferent}
          aria-label={t('shop.cart.keys_all_same')}
          title={!canChooseKeysDifferent ? t('shop.cart.keys_all_same_only_packs') : undefined}
        />
      </td>
      <td className="text-right font-medium align-middle whitespace-nowrap">{Number(line.line_total).toFixed(2)} €</td>
      <td className="align-middle text-center">
        <button
          type="button"
          className="btn btn-ghost btn-sm btn-circle"
          onClick={() => removeLine(line.id)}
          aria-label={t('common.delete')}
        >
          ✕
        </button>
      </td>
    </tr>
  );
}

export default function CartPage() {
  const { t } = useTranslation();
  const { cart, loading, updateLine, removeLine, setInstallationRequested } = useCart();
  const [installSaving, setInstallSaving] = useState(false);
  const shipFlatCart = Number(cart.shipping_flat_eur ?? 9);
  const installationFeeCart =
    cart.installation_requested && cart.installation_fee_eur != null ? Number(cart.installation_fee_eur) : 0;
  const cartGrandTotal = Number(cart.total) + shipFlatCart + installationFeeCart;
  const installationTooltip = cart.installation_quote_required
    ? t('shop.cart.installation_quote_only_hint')
    : t('shop.cart.installation_tiers_hint');

  const onInstallationChange = async (e) => {
    const checked = e.target.checked;
    setInstallSaving(true);
    try {
      await setInstallationRequested(checked);
    } finally {
      setInstallSaving(false);
    }
  };

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }

  if (!cart.lines?.length) {
    return (
      <div className="mx-auto w-full min-w-0 max-w-5xl">
        <div className="mb-4">
          <PageTitle className="mb-0">{t('shop.cart')}</PageTitle>
        </div>
        <div className="text-center py-12">
          <p className="text-xl text-base-content/70 mb-4">{t('shop.cart.empty')}</p>
        <Link to="/products" className="btn btn-primary">
          {t('shop.products')}
        </Link>
        </div>
      </div>
    );
  }

  return (
    <div className="mx-auto w-full min-w-0 max-w-5xl">
      <div className="mb-4">
        <PageTitle className="mb-0">{t('shop.cart')}</PageTitle>
      </div>

      <div className="card bg-base-100 shadow border border-base-300 rounded-2xl mb-4 p-4">
        <label className="flex flex-wrap items-center gap-3 cursor-pointer">
          <input
            type="checkbox"
            className="checkbox checkbox-primary checkbox-sm shrink-0"
            checked={!!cart.installation_requested}
            onChange={onInstallationChange}
            disabled={installSaving}
            aria-label={t('shop.cart.request_installation')}
          />
          <span className="font-medium">{t('shop.cart.request_installation')}</span>
          <span className="tooltip tooltip-bottom shrink-0 max-w-[min(100vw-2rem,24rem)] whitespace-normal text-left inline-block" data-tip={installationTooltip}>
            <button
              type="button"
              className="btn btn-ghost btn-xs btn-circle font-serif italic"
              aria-label={installationTooltip}
            >
              i
            </button>
          </span>
        </label>
      </div>

      <div className="card bg-base-100 shadow border border-base-300 overflow-hidden rounded-2xl">
        <div className="overflow-x-auto">
          <table className="table">
            <thead>
              <tr className="bg-base-100 border-b border-base-300">
                <th className="w-12 text-center" aria-label={t('shop.cart.include')} />
                <th>{t('shop.products')}</th>
                <th className="text-center whitespace-nowrap">{t('shop.price')}</th>
                <th className="text-center whitespace-nowrap">{t('shop.quantity')}</th>
                <th className="text-center whitespace-nowrap">{t('shop.cart.extra_keys')}</th>
                <th className="text-center whitespace-nowrap" title={t('shop.cart.keys_all_same_tooltip')}>{t('shop.cart.keys_all_same')}</th>
                <th className="text-right whitespace-nowrap">{t('shop.total')}</th>
                <th className="w-12 text-center" />
              </tr>
            </thead>
            <tbody>
              {cart.lines.map((line) => (
                <CartLine
                  key={line.id}
                  line={line}
                  updateLine={updateLine}
                  removeLine={removeLine}
                  t={t}
                />
              ))}
            </tbody>
          </table>
        </div>

        <div className="flex flex-col sm:flex-row justify-center sm:justify-end items-stretch sm:items-end gap-4 p-4 bg-base-100 border-t border-base-300">
          <div className="text-sm sm:text-end space-y-1">
            <p className="m-0 text-base-content/80">
              <span className="font-medium text-base-content">{t('shop.subtotal')}:</span>{' '}
              <span className="tabular-nums">{Number(cart.total).toFixed(2)} €</span>
            </p>
            <p className="m-0 text-base-content/80">
              <span className="font-medium text-base-content">{t('shop.shipping_flat')}:</span>{' '}
              <span className="tabular-nums">{shipFlatCart.toFixed(2)} €</span>
            </p>
            {installationFeeCart > 0 ? (
              <p className="m-0 text-base-content/80">
                <span className="font-medium text-base-content">{t('shop.order.installation_fee')}:</span>{' '}
                <span className="tabular-nums">{installationFeeCart.toFixed(2)} €</span>
              </p>
            ) : null}
            <p className="m-0 text-lg font-bold text-primary">
              <span className="font-semibold text-base-content">
                {installationFeeCart > 0 ? t('checkout.total_due_estimate') : t('shop.total_with_shipping')}:
              </span>{' '}
              <span className="tabular-nums">
                {(installationFeeCart > 0 ? cartGrandTotal : Number(cart.total_with_shipping ?? cart.total + shipFlatCart)).toFixed(2)} €
              </span>
            </p>
          </div>
          <div className="flex gap-2 justify-center sm:justify-end sm:items-end">
            <Link to="/checkout" className="btn btn-primary">
              {t('shop.checkout')}
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
