import React, { useCallback, useEffect, useState } from 'react';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

const PLACEHOLDER_IMAGE = '/images/dummy.jpg';

function getStatusBadgeClass(status) {
  switch (status) {
    case 'pending':
    case 'awaiting_payment':
    case 'awaiting_installation_price':
    case 'installation_pending':
      return 'badge-outline badge-warning';
    case 'in_transit':
    case 'sent':
      return 'badge-outline badge-success';
    case 'installation_confirmed':
      return 'badge-outline badge-info';
    default:
      return 'badge-outline badge-neutral';
  }
}

function lineDisplayName(line) {
  if (line.product) return line.product.name + (line.product.code ? ` (${line.product.code})` : '');
  if (line.pack) return line.pack.name;
  return '';
}

function lineTargetUrl(line) {
  if (line.product_id) return `/admin/products/${line.product_id}`;
  if (line.pack_id) return `/admin/packs/${line.pack_id}`;
  return null;
}

/** “Llaves iguales”: Yes/No for packs with keys; otherwise No (never blank). */
function keysSameLabel(line, t) {
  if (line.pack?.contains_keys) {
    return line.keys_all_same ? t('common.yes') : t('common.no');
  }
  return t('common.no');
}

/** Pack lines have `pack_id` or nested `pack`; everything else is treated as product lines. */
function partitionOrderLines(lines) {
  const list = lines || [];
  const packs = list.filter((l) => l.pack_id || l.pack);
  const products = list.filter((l) => !(l.pack_id || l.pack));
  return { products, packs };
}

/** Single desktop table so Productos and Packs share identical column widths. */
function OrderLinesDesktopTable({ productLines, packLines, navigate, t }) {
  const sections = [];
  if (productLines.length > 0) {
    sections.push({ id: 'products', titleKey: 'admin.orders.lines_products', lines: productLines });
  }
  if (packLines.length > 0) {
    sections.push({ id: 'packs', titleKey: 'admin.orders.lines_packs', lines: packLines });
  }
  if (sections.length === 0) return null;
  return (
    <div className="overflow-x-auto hidden sm:block min-w-0">
      <table className="table table-zebra table-sm w-full min-w-[720px] [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap">
        <thead>
          <tr>
            <th className="w-14" aria-label={t('admin.products.images')} />
            <th>{t('admin.orders.line_name')}</th>
            <th className="text-center">{t('admin.orders.line_quantity')}</th>
            <th className="text-end">{t('admin.orders.line_unit_price')}</th>
            <th className="text-end">{t('admin.orders.line_extra_keys_price')}</th>
            <th className="text-center w-24 min-w-[6rem]">{t('admin.orders.keys_same')}</th>
            <th className="text-end">{t('admin.orders.line_total')}</th>
          </tr>
        </thead>
        <tbody>
          {sections.map((section) => (
            <React.Fragment key={section.id}>
              <tr className="bg-base-200/90 hover:bg-base-200/90">
                <td colSpan={7} className="font-semibold text-sm py-2.5">
                  {t(section.titleKey)}
                </td>
              </tr>
              {section.lines.map((line) => {
                const extraKeysTotal = line.extra_keys_qty > 0 && line.extra_key_unit_price != null
                  ? line.extra_keys_qty * line.extra_key_unit_price
                  : null;
                const targetUrl = lineTargetUrl(line);
                return (
                  <tr
                    key={line.id}
                    role={targetUrl ? 'button' : undefined}
                    tabIndex={targetUrl ? 0 : undefined}
                    onClick={targetUrl ? () => navigate(targetUrl) : undefined}
                    onKeyDown={targetUrl ? (e) => {
                      if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        navigate(targetUrl);
                      }
                    } : undefined}
                    className={targetUrl ? 'cursor-pointer hover:bg-base-200/50' : ''}
                  >
                    <td>
                      <div className="avatar">
                        <div className="mask mask-squircle w-10 h-10 bg-base-300">
                          <img
                            src={line.image_url || PLACEHOLDER_IMAGE}
                            alt=""
                            className="object-cover w-full h-full"
                          />
                        </div>
                      </div>
                    </td>
                    <td>
                      <span className="font-medium">{lineDisplayName(line)}</span>
                      {line.extra_keys_qty > 0 && (
                        <div className="flex flex-wrap gap-1 mt-0.5">
                          <span className="badge badge-sm badge-ghost">+{line.extra_keys_qty} {t('admin.orders.extra_keys')}</span>
                        </div>
                      )}
                    </td>
                    <td className="text-center tabular-nums">{line.quantity}</td>
                    <td className="text-end tabular-nums">{line.unit_price != null ? `${Number(line.unit_price).toFixed(2)} €` : ''}</td>
                    <td className="text-end tabular-nums">{extraKeysTotal != null ? `${Number(extraKeysTotal).toFixed(2)} €` : ''}</td>
                    <td className="text-center w-24 min-w-[6rem]">{keysSameLabel(line, t)}</td>
                    <td className="text-end font-medium tabular-nums">{line.line_total != null ? `${Number(line.line_total).toFixed(2)} €` : ''}</td>
                  </tr>
                );
              })}
            </React.Fragment>
          ))}
        </tbody>
      </table>
    </div>
  );
}

function OrderLinesMobileCards({ lines, t }) {
  if (lines.length === 0) return null;
  return (
    <div className="flex flex-col gap-3 sm:hidden">
      {lines.map((line) => {
        const targetUrl = lineTargetUrl(line);
        const cardContent = (
          <div className="card-body p-4 flex flex-row items-center gap-3 min-w-0">
            <div className="avatar shrink-0">
              <div className="mask mask-squircle w-14 h-14 bg-base-300">
                <img
                  src={line.image_url || PLACEHOLDER_IMAGE}
                  alt=""
                  className="object-cover w-full h-full"
                />
              </div>
            </div>
            <div className="min-w-0 flex-1">
              <p className="font-medium truncate">{lineDisplayName(line)}</p>
              {line.extra_keys_qty > 0 && (
                <div className="flex flex-wrap gap-1 mt-0.5">
                  <span className="badge badge-sm badge-ghost">+{line.extra_keys_qty} {t('admin.orders.extra_keys')}</span>
                </div>
              )}
              <p className="text-sm text-base-content/70 mt-0.5">
                {t('admin.orders.keys_same')}: {keysSameLabel(line, t)}
              </p>
              <div className="flex flex-wrap items-center gap-2 mt-1 text-sm">
                <span className="tabular-nums">{t('admin.orders.line_quantity')}: {line.quantity}</span>
                <span className="tabular-nums font-medium">{line.line_total != null ? `${Number(line.line_total).toFixed(2)} €` : ''}</span>
              </div>
            </div>
          </div>
        );
        return targetUrl ? (
          <Link
            key={line.id}
            to={targetUrl}
            className="card card-border bg-base-200/50 hover:bg-base-200 border-base-300"
          >
            {cardContent}
          </Link>
        ) : (
          <div key={line.id} className="card card-border bg-base-200/50 border-base-300">
            {cardContent}
          </div>
        );
      })}
    </div>
  );
}

export default function AdminOrderShowPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const { showSuccess } = useAdminToast();
  const { id } = useParams();
  const [order, setOrder] = useState(null);
  const [loaded, setLoaded] = useState(false);
  const [loadError, setLoadError] = useState('');
  const [labelModalOpen, setLabelModalOpen] = useState(false);
  const [installationModalOpen, setInstallationModalOpen] = useState(false);
  const [installationAmount, setInstallationAmount] = useState('');
  const [installationSubmitting, setInstallationSubmitting] = useState(false);
  const [installationModalError, setInstallationModalError] = useState('');
  const [inTransitMailModalOpen, setInTransitMailModalOpen] = useState(false);
  const [inTransitMailEta, setInTransitMailEta] = useState('today');
  const [inTransitMailSubmitting, setInTransitMailSubmitting] = useState(false);
  const [inTransitMailError, setInTransitMailError] = useState('');

  const fetchOrder = useCallback(async () => {
    if (!id) return;
    try {
      const { data } = await api.get(`admin/orders/${id}`);
      if (data.success) setOrder(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoaded(true);
    }
  }, [id, navigate, t]);

  useEffect(() => {
    fetchOrder();
  }, [fetchOrder]);

  if (loadError) {
    return (
      <div className="space-y-4">
        <div className="flex justify-end">
          <Link to="/admin/orders" className="btn btn-ghost btn-sm">{t('common.back')}</Link>
        </div>
        <div className="alert alert-error">{loadError}</div>
      </div>
    );
  }

  if (!loaded || !order) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  const isOrder = order.kind === 'order';
  const shippingAddress = order.addresses?.find((a) => a.type === 'shipping');
  const installationAddress = order.addresses?.find((a) => a.type === 'installation');
  const showSetInstallationPrice =
    isOrder && order.installation_requested && order.status === 'awaiting_installation_price';
  const showInTransitCustomerMail = isOrder && order.status === 'in_transit';
  const { products: productLines, packs: packLines } = partitionOrderLines(order.lines);

  const handleInstallationModalOpen = () => {
    setInstallationModalError('');
    setInstallationAmount('');
    setInstallationModalOpen(true);
  };

  const handleSetInstallationPrice = async (e) => {
    e.preventDefault();
    setInstallationModalError('');
    const n = parseFloat(String(installationAmount).replace(',', '.'));
    if (Number.isNaN(n) || n < 0) {
      setInstallationModalError(t('common.error'));
      return;
    }
    setInstallationSubmitting(true);
    try {
      const { data } = await api.put(`admin/orders/${id}`, {
        status: order.status,
        installation_price: n,
      });
      if (data.success) {
        showSuccess(t('common.saved'));
        setInstallationModalOpen(false);
        setInstallationAmount('');
        await fetchOrder();
      } else setInstallationModalError(data.message || t('common.error'));
    } catch (err) {
      const msg = err.response?.data?.message
        || err.response?.data?.errors?.installation_price?.[0]
        || t('common.error');
      setInstallationModalError(msg);
    } finally {
      setInstallationSubmitting(false);
    }
  };

  const handleInTransitMailModalOpen = () => {
    setInTransitMailError('');
    setInTransitMailEta('today');
    setInTransitMailModalOpen(true);
  };

  const handleSendInTransitCustomerMail = async () => {
    setInTransitMailError('');
    setInTransitMailSubmitting(true);
    try {
      const { data } = await api.post(`admin/orders/${id}/notify-in-transit-mail`, {
        delivery_eta: inTransitMailEta,
      });
      if (data.success) {
        showSuccess(t('admin.orders.notify_in_transit_mail_success'));
        setInTransitMailModalOpen(false);
      } else {
        setInTransitMailError(data.message || t('common.error'));
      }
    } catch (err) {
      const msg = err.response?.data?.message
        || err.response?.data?.errors?.delivery_eta?.[0]
        || t('common.error');
      setInTransitMailError(msg);
    } finally {
      setInTransitMailSubmitting(false);
    }
  };

  const handlePrintLabel = () => {
    if (!shippingAddress) return;
    const addressLines = [shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ');
    const linesSummary = (order.lines || []).map((l) => `${lineDisplayName(l)} × ${l.quantity}`).join('\n');
    const orderDateStr = order.order_date ? new Date(order.order_date).toLocaleDateString() : '';
    const doc = `
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>${t('admin.orders.shipping_label_title')} #${order.id}</title>
<style>
  body { font-family: system-ui, sans-serif; font-size: 14px; line-height: 1.4; padding: 16px; max-width: 400px; }
  .ref { font-size: 18px; font-weight: bold; margin-bottom: 12px; }
  .ship-to { margin-bottom: 12px; }
  .ship-to strong { display: block; font-size: 10px; text-transform: uppercase; color: #666; margin-bottom: 4px; }
  .items { margin-top: 16px; border-top: 1px solid #ddd; padding-top: 12px; font-size: 12px; white-space: pre-line; }
  .date { margin-top: 8px; color: #666; font-size: 12px; }
</style></head>
<body>
  <div class="ref">${t('admin.orders.shipping_label_ref')} #${order.id}</div>
  <div class="ship-to">
    <strong>${t('admin.orders.shipping_label_ship_to')}</strong>
    ${order.client?.login_email ? `<div>${order.client.login_email}</div>` : ''}
    <div>${addressLines}</div>
    ${shippingAddress.note ? `<div style="margin-top:6px;font-size:12px;color:#666">${shippingAddress.note.replace(/</g, '&lt;')}</div>` : ''}
  </div>
  <div class="date">${t('admin.orders.order_date')}: ${orderDateStr}</div>
  <div class="items">${linesSummary || ''}</div>
</body></html>`;
    const w = window.open('', '_blank', 'width=420,height=600');
    if (w) {
      w.document.write(doc);
      w.document.close();
      w.focus();
      setTimeout(() => w.print(), 150);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-wrap items-center justify-between gap-2">
        <PageTitle className="mb-0">
          {t('admin.orders.title')} #{order.id}
          <span className="ml-2 badge badge-ghost text-sm font-normal">{t(`admin.orders.kind_${order.kind}`)}</span>
        </PageTitle>
        <div className="flex gap-2">
          <Link to="/admin/orders" className="btn btn-ghost btn-sm shrink-0">{t('common.back')}</Link>
          {isOrder && shippingAddress && (
            <button type="button" className="btn btn-outline btn-sm shrink-0" onClick={() => setLabelModalOpen(true)} aria-label={t('admin.orders.shipping_label_button')}>
              {t('admin.orders.shipping_label_button')}
            </button>
          )}
          {showInTransitCustomerMail && (
            <button type="button" className="btn btn-outline btn-sm shrink-0" onClick={handleInTransitMailModalOpen}>
              {t('admin.orders.notify_in_transit_mail_button')}
            </button>
          )}
          {showSetInstallationPrice && (
            <button type="button" className="btn btn-secondary btn-sm shrink-0" onClick={handleInstallationModalOpen}>
              {t('admin.orders.set_installation_price')}
            </button>
          )}
          {isOrder && (
            <Link to={`/admin/orders/${id}/edit`} className="btn btn-primary btn-sm shrink-0">{t('common.edit')}</Link>
          )}
        </div>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-base">{t('admin.orders.client_and_dates')}</h2>
            <dl className="grid grid-cols-1 gap-3">
              {order.client && (
                <div>
                  <dt className="text-sm text-base-content/70">{t('admin.orders.client')}</dt>
                  <dd><Link to={`/admin/clients/${order.client.id}`} className="link link-hover">{order.client.login_email}</Link></dd>
                  {order.client.identification && <dd className="text-sm text-base-content/70">{order.client.identification}</dd>}
                </div>
              )}
              <div><dt className="text-sm text-base-content/70">{t('admin.orders.kind')}</dt><dd>{t(`admin.orders.kind_${order.kind}`)}</dd></div>
              {isOrder && order.status && <div><dt className="text-sm text-base-content/70">{t('admin.orders.status')}</dt><dd><span className={`badge badge-sm ${getStatusBadgeClass(order.status)}`}>{t(`admin.orders.status_${order.status}`)}</span></dd></div>}
              {order.order_date && <div><dt className="text-sm text-base-content/70">{t('admin.orders.order_date')}</dt><dd>{new Date(order.order_date).toLocaleString()}</dd></div>}
              {order.shipping_date && <div><dt className="text-sm text-base-content/70">{t('admin.orders.shipping_date')}</dt><dd>{new Date(order.shipping_date).toLocaleString()}</dd></div>}
              {order.shipping_price != null && <div><dt className="text-sm text-base-content/70">{t('admin.orders.shipping_price')}</dt><dd>{Number(order.shipping_price).toFixed(2)} €</dd></div>}
              {isOrder && order.installation_requested && (
                <>
                  <div><dt className="text-sm text-base-content/70">{t('admin.orders.installation')}</dt><dd>{t('common.yes')}</dd></div>
                  {order.installation_status && (
                    <div><dt className="text-sm text-base-content/70">{t('admin.orders.installation_status_label')}</dt><dd>{t(`admin.orders.install_status_${order.installation_status}`)}</dd></div>
                  )}
                  {order.installation_price != null && (
                    <div><dt className="text-sm text-base-content/70">{t('admin.orders.installation_fee_label')}</dt><dd>{Number(order.installation_price).toFixed(2)} €</dd></div>
                  )}
                </>
              )}
            </dl>
          </div>
        </div>

        {(shippingAddress || installationAddress) && (
          <div className="card bg-base-100 shadow border border-base-200">
            <div className="card-body">
              <h2 className="card-title text-base">{t('admin.orders.addresses')}</h2>
              <div className="space-y-4">
                {shippingAddress && (
                  <div>
                    <h3 className="text-sm font-semibold text-base-content/70">{t('admin.orders.address_shipping')}</h3>
                    <p className="whitespace-pre-wrap">{[shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ')}</p>
                    {shippingAddress.note && <p className="text-sm text-base-content/70 mt-1">{shippingAddress.note}</p>}
                  </div>
                )}
                {installationAddress && (
                  <div>
                    <h3 className="text-sm font-semibold text-base-content/70">{t('admin.orders.address_installation')}</h3>
                    <p className="whitespace-pre-wrap">{[installationAddress.street, installationAddress.city, installationAddress.province, installationAddress.postal_code].filter(Boolean).join(', ')}</p>
                    {installationAddress.note && <p className="text-sm text-base-content/70 mt-1">{installationAddress.note}</p>}
                  </div>
                )}
              </div>
            </div>
          </div>
        )}
      </div>

      <div className="card bg-base-100 shadow border border-base-200">
        <div className="card-body">
          <h2 className="font-semibold text-lg border-b border-base-300 pb-2 mb-4">{t('admin.orders.lines')}</h2>
          {productLines.length === 0 && packLines.length === 0 ? (
            <p className="text-base-content/70 py-4">{t('admin.orders.no_lines')}</p>
          ) : (
            <div className="space-y-6">
              <OrderLinesDesktopTable
                productLines={productLines}
                packLines={packLines}
                navigate={navigate}
                t={t}
              />
              <div className="flex flex-col gap-6 sm:hidden">
                {productLines.length > 0 && (
                  <section className="space-y-3" aria-labelledby="admin-order-lines-products-heading">
                    <h3 id="admin-order-lines-products-heading" className="text-base font-semibold text-base-content">
                      {t('admin.orders.lines_products')}
                    </h3>
                    <OrderLinesMobileCards lines={productLines} t={t} />
                  </section>
                )}
                {packLines.length > 0 && (
                  <section className="space-y-3" aria-labelledby="admin-order-lines-packs-heading">
                    <h3 id="admin-order-lines-packs-heading" className="text-base font-semibold text-base-content">
                      {t('admin.orders.lines_packs')}
                    </h3>
                    <OrderLinesMobileCards lines={packLines} t={t} />
                  </section>
                )}
              </div>
            </div>
          )}
          <div className="pt-4 mt-4 border-t border-base-200 w-full">
            <p className="text-lg font-semibold tabular-nums text-end">
              {t('admin.orders.total')}: {order.total != null ? Number(order.total).toFixed(2) : '0.00'} €
            </p>
          </div>
        </div>
      </div>

      {order.payments && order.payments.length > 0 && (
        <div className="card bg-base-100 shadow border border-base-200">
          <div className="card-body">
            <h2 className="card-title text-base">{t('admin.orders.payments')}</h2>
            <div className="overflow-x-auto">
              <table className="table table-zebra table-sm [&_th]:whitespace-nowrap [&_td]:whitespace-nowrap">
                <thead>
                  <tr>
                    <th>{t('admin.orders.payment_status')}</th>
                    <th>{t('admin.orders.payment_gateway')}</th>
                    <th>{t('admin.orders.payment_method')}</th>
                    <th className="text-end">{t('admin.orders.payment_amount')}</th>
                    <th>{t('admin.orders.payment_paid_at')}</th>
                    <th>{t('admin.orders.payment_reference')}</th>
                    <th>{t('admin.orders.payment_failure')}</th>
                  </tr>
                </thead>
                <tbody>
                  {order.payments.map((p) => {
                    const methodKey = `admin.orders.payment_${p.payment_method}`;
                    const methodLabel = t(methodKey) !== methodKey ? t(methodKey) : (p.payment_method ?? '');
                    const statusKey = p.status ? `admin.orders.payment_status_${p.status}` : '';
                    const statusLabel = statusKey && t(statusKey) !== statusKey ? t(statusKey) : (p.status ?? '');
                    return (
                      <tr key={p.id}>
                        <td><span className="badge badge-sm badge-ghost whitespace-nowrap">{statusLabel}</span></td>
                        <td className="font-mono text-xs">{p.gateway ?? ''}</td>
                        <td>{methodLabel}</td>
                        <td className="text-end font-medium tabular-nums">{Number(p.amount).toFixed(2)} {p.currency === 'EUR' ? '€' : p.currency}</td>
                        <td>{p.paid_at ? new Date(p.paid_at).toLocaleString() : ''}</td>
                        <td className="font-mono text-sm max-w-[12rem] truncate" title={p.gateway_reference ?? ''}>{p.gateway_reference ?? ''}</td>
                        <td className="text-sm max-w-xs">
                          {[p.failure_code, p.failure_message].filter(Boolean).join(' · ')}
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      )}

      <div className="text-sm text-base-content/70">
        {t('admin.orders.created_at')}: {order.created_at ? new Date(order.created_at).toLocaleString() : ''}
        {order.updated_at && <> · {t('admin.orders.updated_at')}: {new Date(order.updated_at).toLocaleString()}</>}
      </div>

      {/* Installation price (awaiting quote) */}
      <dialog
        className={`modal ${installationModalOpen ? 'modal-open' : ''}`}
        aria-label={t('admin.orders.set_installation_price_modal_title')}
      >
        <div className="modal-box max-w-md">
          <h3 className="font-bold text-lg">{t('admin.orders.set_installation_price_modal_title')}</h3>
          <p className="text-sm text-base-content/70 py-2">{t('admin.orders.set_installation_price_hint')}</p>
          <form onSubmit={handleSetInstallationPrice} className="space-y-4">
            {installationModalError && (
              <div role="alert" className="alert alert-error text-sm">{installationModalError}</div>
            )}
            <label className="form-field w-full">
              <span className="form-label">{t('admin.orders.installation_price_amount')}</span>
              <input
                type="number"
                step="0.01"
                min="0"
                className="input input-bordered w-full"
                value={installationAmount}
                onChange={(e) => setInstallationAmount(e.target.value)}
                required
                autoFocus
                aria-label={t('admin.orders.installation_price_amount')}
              />
            </label>
            <div className="modal-action">
              <button
                type="button"
                className="btn btn-ghost"
                onClick={() => setInstallationModalOpen(false)}
              >
                {t('common.close')}
              </button>
              <button type="submit" className="btn btn-primary" disabled={installationSubmitting}>
                {installationSubmitting ? t('common.loading') : t('common.save')}
              </button>
            </div>
          </form>
        </div>
        <form method="dialog" className="modal-backdrop" onSubmit={() => setInstallationModalOpen(false)}>
          <button type="submit" aria-label={t('common.close')}>{t('common.close')}</button>
        </form>
      </dialog>

      {/* Shipping label */}
      {/* In-transit: notify customer by email */}
      <dialog
        className={`modal ${inTransitMailModalOpen ? 'modal-open' : ''}`}
        aria-label={t('admin.orders.notify_in_transit_mail_modal_title')}
      >
        <div className="modal-box max-w-md">
          <h3 className="font-bold text-lg">{t('admin.orders.notify_in_transit_mail_modal_title')}</h3>
          <p className="text-sm text-base-content/70 py-2">{t('admin.orders.notify_in_transit_mail_modal_intro')}</p>
          {inTransitMailError && (
            <div role="alert" className="alert alert-error text-sm mb-3">{inTransitMailError}</div>
          )}
          <label className="form-control w-full">
            <div className="label pt-0">
              <span className="label-text">{t('admin.orders.notify_in_transit_mail_eta_label')}</span>
            </div>
            <select
              className="select select-bordered w-full select-sm sm:select-md"
              value={inTransitMailEta}
              onChange={(e) => setInTransitMailEta(e.target.value)}
              aria-label={t('admin.orders.notify_in_transit_mail_eta_label')}
            >
              <option value="today">{t('admin.orders.notify_in_transit_mail_eta_today')}</option>
              <option value="few_days">{t('admin.orders.notify_in_transit_mail_eta_few_days')}</option>
              <option value="unknown">{t('admin.orders.notify_in_transit_mail_eta_unknown')}</option>
            </select>
          </label>
          <div className="modal-action">
            <button type="button" className="btn btn-ghost" onClick={() => setInTransitMailModalOpen(false)}>
              {t('common.cancel')}
            </button>
            <button
              type="button"
              className="btn btn-primary"
              disabled={inTransitMailSubmitting}
              onClick={handleSendInTransitCustomerMail}
            >
              {inTransitMailSubmitting ? t('common.loading') : t('admin.orders.notify_in_transit_mail_send')}
            </button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop" onSubmit={() => setInTransitMailModalOpen(false)}>
          <button type="submit" aria-label={t('common.close')}>{t('common.close')}</button>
        </form>
      </dialog>

      <dialog className={`modal ${labelModalOpen ? 'modal-open' : ''}`} aria-label={t('admin.orders.shipping_label_title')}>
        <div className="modal-box max-w-md">
          <h3 className="font-bold text-lg">{t('admin.orders.shipping_label_title')} #{order.id}</h3>
          {shippingAddress && (
            <div className="py-4">
              <div className="rounded-lg border border-base-300 bg-base-200 p-4 text-sm">
                <p className="font-semibold text-base-content/70 text-xs uppercase tracking-wide mb-1">{t('admin.orders.shipping_label_ship_to')}</p>
                {order.client?.login_email && <p>{order.client.login_email}</p>}
                <p className="mt-1">{[shippingAddress.street, shippingAddress.city, shippingAddress.province, shippingAddress.postal_code].filter(Boolean).join(', ')}</p>
                {shippingAddress.note && <p className="mt-2 text-base-content/70">{shippingAddress.note}</p>}
                <p className="mt-3 text-base-content/70">{t('admin.orders.order_date')}: {order.order_date ? new Date(order.order_date).toLocaleDateString() : ''}</p>
                <p className="mt-1 font-mono text-xs">{(order.lines || []).map((l) => `${lineDisplayName(l)} × ${l.quantity}`).join(' · ') || ''}</p>
              </div>
            </div>
          )}
          <div className="modal-action">
            <button type="button" className="btn btn-ghost" onClick={() => setLabelModalOpen(false)}>{t('common.close')}</button>
            <button type="button" className="btn btn-primary" onClick={() => { handlePrintLabel(); setLabelModalOpen(false); }}>{t('admin.orders.shipping_label_print')}</button>
          </div>
        </div>
        <form method="dialog" className="modal-backdrop" onSubmit={() => setLabelModalOpen(false)}>
          <button type="submit" aria-label={t('common.close')}>{t('common.close')}</button>
        </form>
      </dialog>
    </div>
  );
}
