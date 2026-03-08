import React from 'react';
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
  const wantsInstallation = !!line.is_installation_requested;
  const isInstallable = line.product?.is_installable || line.pack?.is_installable || false;
  const installationPrice = line.product?.installation_price ?? line.pack?.installation_price ?? null;
  const isExtraKeysAvailable = !!line.product?.is_extra_keys_available;
  const extraKeyUnitPrice = line.product?.extra_key_unit_price ?? null;
  const extraKeysQty = line.extra_keys_qty ?? 0;
  const features = line.product?.features ?? [];

  const handleIncludeChange = () => {
    updateLine(line.id, { quantity: line.quantity, included: !isIncluded });
  };

  const handleInstallChange = () => {
    if (!isInstallable) return;
    updateLine(line.id, { quantity: line.quantity, is_installation_requested: !wantsInstallation });
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
            <img src={imageUrl} alt="" className="w-full h-full object-cover" />
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
        <div className="flex flex-col items-center gap-0.5">
          <input
            type="checkbox"
            className="checkbox checkbox-sm checkbox-primary"
            checked={wantsInstallation}
            onChange={handleInstallChange}
            disabled={!isInstallable}
            aria-label={t('shop.cart.install_for_me')}
            title={!isInstallable ? t('shop.product.installation_available') : undefined}
          />
          {(installationPrice != null && installationPrice !== '') && (
            <span className="text-xs text-base-content/70">{Number(installationPrice).toFixed(2)} €/u</span>
          )}
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
  const { cart, loading, updateLine, removeLine } = useCart();

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" />
      </div>
    );
  }

  if (!cart.lines?.length) {
    return (
      <div className="max-w-5xl mx-auto">
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
    <div className="max-w-5xl mx-auto">
      <div className="mb-4">
        <PageTitle className="mb-0">{t('shop.cart')}</PageTitle>
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
                <th className="text-center w-24">{t('shop.cart.install_for_me')}</th>
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

        <div className="flex flex-col sm:flex-row justify-center sm:justify-end items-center gap-4 p-4 bg-base-100 border-t border-base-300">
          <p className="flex items-center gap-2 whitespace-nowrap">
            <span className="font-semibold text-base-content">{t('shop.total')}:</span>
            <span className="text-2xl font-bold text-primary">{Number(cart.total).toFixed(2)} €</span>
          </p>
          <div className="flex gap-2 justify-center sm:justify-end">
            <Link to="/checkout" className="btn btn-primary">
              {t('shop.checkout')}
            </Link>
          </div>
        </div>
      </div>
    </div>
  );
}
