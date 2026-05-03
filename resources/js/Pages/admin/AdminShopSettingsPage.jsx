import React, { useCallback, useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { useQueryClient } from '@tanstack/react-query';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';
import {
  ADMIN_INDEX_COLUMN_REGISTRY,
  ADMIN_INDEX_TABLE_META,
  ADMIN_INDEX_TABLE_ORDER,
} from '../../config/adminIndexColumnsRegistry';
import AdminIndexColumnsFieldset from '../../components/admin/AdminIndexColumnsFieldset';
import AdminSettingsCollapseSection from '../../components/admin/AdminSettingsCollapseSection';
import {
  adminShopSettingsQueryKey,
  buildAdminIndexColumnsPayload,
  columnOrderAndPrefsFromServer,
} from '../../hooks/useAdminShopSettingsQuery';

function parseIdList(text) {
  const parts = String(text || '')
    .split(/[\s,;]+/)
    .map((s) => s.trim())
    .filter(Boolean);
  const ids = parts.map((p) => parseInt(p, 10)).filter((n) => Number.isFinite(n) && n > 0);
  return [...new Set(ids)];
}

function idsToText(ids) {
  if (!Array.isArray(ids) || ids.length === 0) return '';
  return ids.join(', ');
}

function defaultInstallationRows() {
  return [
    { max_merchandise_eur: '250', fee_eur: '90' },
    { max_merchandise_eur: '500', fee_eur: '120' },
    { max_merchandise_eur: '1000', fee_eur: '180' },
  ];
}

function parseInstallationFromApi(inst) {
  if (!inst || typeof inst !== 'object') {
    return { quote: '1000', tiers: defaultInstallationRows() };
  }
  const quote = inst.quote_when_merchandise_above_eur;
  const tiers = Array.isArray(inst.tiers)
    ? inst.tiers.map((row) => ({
        max_merchandise_eur: String(row.max_merchandise_eur ?? ''),
        fee_eur: String(row.fee_eur ?? ''),
      }))
    : defaultInstallationRows();
  return { quote: String(quote ?? '1000'), tiers: tiers.length ? tiers : defaultInstallationRows() };
}

export default function AdminShopSettingsPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const queryClient = useQueryClient();
  const { showSuccess } = useAdminToast();
  const [loadError, setLoadError] = useState('');
  const [saveError, setSaveError] = useState('');
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [recalculating, setRecalculating] = useState(false);

  const [lowStockEnabled, setLowStockEnabled] = useState(false);
  const [lowStockThreshold, setLowStockThreshold] = useState(10);
  const [lowStockBlacklistEnabled, setLowStockBlacklistEnabled] = useState(false);
  const [lowStockBlacklistText, setLowStockBlacklistText] = useState('');

  const [overstockEnabled, setOverstockEnabled] = useState(false);
  const [overstockThreshold, setOverstockThreshold] = useState(100);
  const [overstockBlacklistEnabled, setOverstockBlacklistEnabled] = useState(false);
  const [overstockBlacklistText, setOverstockBlacklistText] = useState('');

  const [acceptPersonalizedSolutions, setAcceptPersonalizedSolutions] = useState(true);
  const [adminListDefaultPeriod, setAdminListDefaultPeriod] = useState('week');

  const [featuredMaxManual, setFeaturedMaxManual] = useState(0);
  const [featuredMaxLowStock, setFeaturedMaxLowStock] = useState(0);
  const [featuredMaxOverstock, setFeaturedMaxOverstock] = useState(0);

  const [shippingFlatEur, setShippingFlatEur] = useState('9');
  const [installationQuoteAbove, setInstallationQuoteAbove] = useState('1000');
  const [installationTiers, setInstallationTiers] = useState(() => defaultInstallationRows());

  const [columnPrefs, setColumnPrefs] = useState({});
  const [columnOrder, setColumnOrder] = useState({});

  const applyPayload = useCallback((d) => {
    setLowStockEnabled(!!d.low_stock_enabled);
    setLowStockThreshold(Number(d.low_stock_threshold) || 0);
    setLowStockBlacklistEnabled(!!d.low_stock_blacklist_enabled);
    setLowStockBlacklistText(idsToText(d.low_stock_blacklist_product_ids));
    setOverstockEnabled(!!d.overstock_enabled);
    setOverstockThreshold(Number(d.overstock_threshold) || 0);
    setOverstockBlacklistEnabled(!!d.overstock_blacklist_enabled);
    setOverstockBlacklistText(idsToText(d.overstock_blacklist_product_ids));
    setAcceptPersonalizedSolutions(d.accept_personalized_solutions !== false);
    setAdminListDefaultPeriod(['week', 'month', 'year', 'all'].includes(d.admin_list_default_period) ? d.admin_list_default_period : 'week');
    setFeaturedMaxManual(Number(d.featured_max_manual) || 0);
    setFeaturedMaxLowStock(Number(d.featured_max_low_stock) || 0);
    setFeaturedMaxOverstock(Number(d.featured_max_overstock) || 0);
    setShippingFlatEur(String(d.shipping_flat_eur ?? '9'));
    const { quote, tiers } = parseInstallationFromApi(d.installation_auto_pricing);
    setInstallationQuoteAbove(quote);
    setInstallationTiers(tiers);
    const { columnPrefs: cp, columnOrder: co } = columnOrderAndPrefsFromServer(d.admin_index_columns);
    setColumnPrefs(cp);
    setColumnOrder(co);
  }, []);

  const installationTierMismatch = useMemo(() => {
    const quote = parseFloat(String(installationQuoteAbove).replace(',', '.'));
    if (!Number.isFinite(quote) || installationTiers.length === 0) return false;
    const last = installationTiers[installationTiers.length - 1];
    const lastMax = parseFloat(String(last.max_merchandise_eur).replace(',', '.'));
    if (!Number.isFinite(lastMax)) return true;
    return Math.abs(lastMax - quote) > 0.02;
  }, [installationQuoteAbove, installationTiers]);

  const fetchSettings = useCallback(async () => {
    setLoadError('');
    setLoading(true);
    try {
      const { data } = await api.get('admin/settings');
      if (data.success && data.data) applyPayload(data.data);
      else setLoadError(t('common.error'));
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setLoadError(err.response?.data?.message || t('common.error'));
    } finally {
      setLoading(false);
    }
  }, [applyPayload, navigate, t]);

  useEffect(() => {
    fetchSettings();
  }, [fetchSettings]);

  const buildPutBody = () => {
    const quote = parseFloat(String(installationQuoteAbove).replace(',', '.')) || 0;
    const tiers = installationTiers
      .map((r) => ({
        max_merchandise_eur: parseFloat(String(r.max_merchandise_eur).replace(',', '.')) || 0,
        fee_eur: parseFloat(String(r.fee_eur).replace(',', '.')) || 0,
      }))
      .filter((r) => r.max_merchandise_eur > 0);
    const ship = Math.min(99999.99, Math.max(0, parseFloat(String(shippingFlatEur).replace(',', '.')) || 0));
    return {
      low_stock_enabled: lowStockEnabled,
      low_stock_threshold: Math.max(0, parseInt(String(lowStockThreshold), 10) || 0),
      low_stock_blacklist_enabled: lowStockBlacklistEnabled,
      low_stock_blacklist_product_ids: parseIdList(lowStockBlacklistText),
      overstock_enabled: overstockEnabled,
      overstock_threshold: Math.max(0, parseInt(String(overstockThreshold), 10) || 0),
      overstock_blacklist_enabled: overstockBlacklistEnabled,
      overstock_blacklist_product_ids: parseIdList(overstockBlacklistText),
      accept_personalized_solutions: acceptPersonalizedSolutions,
      admin_list_default_period: adminListDefaultPeriod,
      featured_max_manual: Math.max(0, parseInt(String(featuredMaxManual), 10) || 0),
      featured_max_low_stock: Math.max(0, parseInt(String(featuredMaxLowStock), 10) || 0),
      featured_max_overstock: Math.max(0, parseInt(String(featuredMaxOverstock), 10) || 0),
      shipping_flat_eur: ship,
      installation_auto_pricing: {
        quote_when_merchandise_above_eur: quote,
        tiers,
      },
      admin_index_columns: buildAdminIndexColumnsPayload(columnPrefs, columnOrder),
    };
  };

  const handleSave = async (e) => {
    e.preventDefault();
    setSaveError('');
    setSaving(true);
    try {
      const { data } = await api.put('admin/settings', buildPutBody());
      if (data.success && data.data) {
        applyPayload(data.data);
        queryClient.invalidateQueries({ queryKey: adminShopSettingsQueryKey });
        showSuccess(t('common.saved'));
      } else {
        setSaveError(t('common.error'));
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else {
        const errs = err.response?.data?.errors;
        const first =
          err.response?.data?.message ||
          (errs && typeof errs === 'object' ? Object.values(errs).flat().find(Boolean) : null);
        setSaveError(first || t('common.error'));
      }
    } finally {
      setSaving(false);
    }
  };

  const handleRecalculate = async () => {
    setSaveError('');
    setRecalculating(true);
    try {
      const { data } = await api.post('admin/settings/recalculate-trending');
      if (data.success) {
        showSuccess(t('admin.settings.recalculate_done', { count: data.data?.updated_count ?? 0 }));
      } else {
        setSaveError(t('common.error'));
      }
    } catch (err) {
      if (err.response?.status === 401) navigate('/admin/login');
      else setSaveError(err.response?.data?.message || t('common.error'));
    } finally {
      setRecalculating(false);
    }
  };

  const addInstallationTier = () => {
    setInstallationTiers((rows) => [...rows, { max_merchandise_eur: '', fee_eur: '' }]);
  };

  const removeInstallationTier = (index) => {
    setInstallationTiers((rows) => rows.filter((_, i) => i !== index));
  };

  const updateInstallationTier = (index, field, value) => {
    setInstallationTiers((rows) =>
      rows.map((row, i) => (i === index ? { ...row, [field]: value } : row))
    );
  };

  if (loading) {
    return (
      <div className="flex justify-center py-12">
        <span className="loading loading-spinner loading-lg" aria-hidden="true" />
      </div>
    );
  }

  if (loadError) {
    return <div className="alert alert-error pt-4 sm:pt-6">{loadError}</div>;
  }

  return (
    <div className="space-y-6 pt-4 sm:pt-6 min-w-0">
      <div className="flex flex-wrap items-start justify-between gap-4">
        <PageTitle>{t('admin.settings.title')}</PageTitle>
        <button
          type="button"
          className="btn btn-primary btn-sm sm:btn-md shrink-0"
          onClick={handleRecalculate}
          disabled={recalculating || saving}
        >
          {recalculating ? t('common.loading') : t('admin.settings.recalculate_trending')}
        </button>
      </div>

      {saveError ? <div className="alert alert-error text-sm">{saveError}</div> : null}

      <form onSubmit={handleSave} className="space-y-4 sm:space-y-6 min-w-0">
        <AdminSettingsCollapseSection
          title={t('admin.settings.section_home')}
          subtitle={t('admin.settings.section_home_subtitle')}
          defaultOpen
        >
          <div className="space-y-4 min-w-0 px-1 pb-1">
            <div className="divider my-1">{t('admin.settings.section_general')}</div>

            <p className="text-sm font-medium">{t('admin.settings.featured_limits_title')}</p>
            <p className="text-xs text-base-content/60">{t('admin.settings.featured_max_hint')}</p>
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.featured_max_manual')}</span>
              <input
                type="number"
                min={0}
                className="input input-bordered input-sm sm:input-md w-full"
                value={featuredMaxManual}
                onChange={(e) => setFeaturedMaxManual(e.target.value)}
              />
            </label>

            <div className="divider my-1">{t('admin.settings.low_stock')}</div>
            <label className="label w-full min-w-0 cursor-pointer items-start justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary shrink-0"
                checked={lowStockEnabled}
                onChange={(e) => setLowStockEnabled(e.target.checked)}
              />
              <span className="label-text min-w-0 flex-1">{t('admin.settings.low_stock_enabled')}</span>
            </label>
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.low_stock_threshold')}</span>
              <input
                type="number"
                min={0}
                className="input input-bordered input-sm sm:input-md w-full"
                value={lowStockThreshold}
                onChange={(e) => setLowStockThreshold(e.target.value)}
              />
            </label>
            <label className="label w-full min-w-0 cursor-pointer items-start justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary shrink-0"
                checked={lowStockBlacklistEnabled}
                onChange={(e) => setLowStockBlacklistEnabled(e.target.checked)}
              />
              <span className="label-text min-w-0 flex-1">{t('admin.settings.blacklist_enabled')}</span>
            </label>
            <label className="form-field w-full max-w-xl">
              <span className="label-text">{t('admin.settings.blacklist_ids_hint')}</span>
              <textarea
                className="textarea textarea-bordered w-full font-mono text-sm min-h-[4.5rem]"
                value={lowStockBlacklistText}
                onChange={(e) => setLowStockBlacklistText(e.target.value)}
                placeholder="1, 2, 3"
              />
            </label>
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.featured_max_low_stock')}</span>
              <input
                type="number"
                min={0}
                className="input input-bordered input-sm sm:input-md w-full"
                value={featuredMaxLowStock}
                onChange={(e) => setFeaturedMaxLowStock(e.target.value)}
              />
            </label>

            <div className="divider my-1">{t('admin.settings.overstock')}</div>
            <label className="label w-full min-w-0 cursor-pointer items-start justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary shrink-0"
                checked={overstockEnabled}
                onChange={(e) => setOverstockEnabled(e.target.checked)}
              />
              <span className="label-text min-w-0 flex-1">{t('admin.settings.overstock_enabled')}</span>
            </label>
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.overstock_threshold')}</span>
              <input
                type="number"
                min={0}
                className="input input-bordered input-sm sm:input-md w-full"
                value={overstockThreshold}
                onChange={(e) => setOverstockThreshold(e.target.value)}
              />
            </label>
            <label className="label w-full min-w-0 cursor-pointer items-start justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary shrink-0"
                checked={overstockBlacklistEnabled}
                onChange={(e) => setOverstockBlacklistEnabled(e.target.checked)}
              />
              <span className="label-text min-w-0 flex-1">{t('admin.settings.overstock_blacklist_enabled')}</span>
            </label>
            <label className="form-field w-full max-w-xl">
              <span className="label-text">{t('admin.settings.blacklist_ids_hint')}</span>
              <textarea
                className="textarea textarea-bordered w-full font-mono text-sm min-h-[4.5rem]"
                value={overstockBlacklistText}
                onChange={(e) => setOverstockBlacklistText(e.target.value)}
                placeholder="1, 2, 3"
              />
            </label>
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.featured_max_overstock')}</span>
              <input
                type="number"
                min={0}
                className="input input-bordered input-sm sm:input-md w-full"
                value={featuredMaxOverstock}
                onChange={(e) => setFeaturedMaxOverstock(e.target.value)}
              />
            </label>
          </div>
        </AdminSettingsCollapseSection>

        <AdminSettingsCollapseSection
          title={t('admin.settings.section_personalized')}
          subtitle={t('admin.settings.section_personalized_subtitle')}
        >
          <div className="space-y-4 min-w-0 px-1 pb-1">
            <label className="label w-full min-w-0 cursor-pointer items-start justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary shrink-0"
                checked={acceptPersonalizedSolutions}
                onChange={(e) => setAcceptPersonalizedSolutions(e.target.checked)}
              />
              <span className="label-text min-w-0 flex-1">{t('admin.settings.accept_personalized_solutions')}</span>
            </label>
          </div>
        </AdminSettingsCollapseSection>

        <AdminSettingsCollapseSection
          title={t('admin.settings.section_list_defaults')}
          subtitle={t('admin.settings.section_list_defaults_help')}
        >
          <div className="space-y-4 min-w-0 px-1 pb-1">
            <label className="form-field max-w-xs">
              <span className="label-text">{t('admin.settings.list_default_period_label')}</span>
              <select
                className="select select-bordered select-sm sm:select-md w-full"
                value={adminListDefaultPeriod}
                onChange={(e) => setAdminListDefaultPeriod(e.target.value)}
              >
                <option value="week">{t('admin.settings.period_week')}</option>
                <option value="month">{t('admin.settings.period_month')}</option>
                <option value="year">{t('admin.settings.period_year')}</option>
                <option value="all">{t('admin.settings.period_all')}</option>
              </select>
            </label>
          </div>
        </AdminSettingsCollapseSection>

        <AdminSettingsCollapseSection
          title={t('admin.settings.section_closed_prices')}
          subtitle={t('admin.settings.section_closed_prices_help')}
        >
          <div className="space-y-6 min-w-0 px-1 pb-1">
            <div>
              <p className="text-sm font-medium">{t('admin.settings.shipping_flat_title')}</p>
              <p className="text-xs text-base-content/60 mt-1">{t('admin.settings.shipping_flat_help')}</p>
              <label className="form-field max-w-xs mt-2">
                <span className="label-text">{t('admin.settings.shipping_flat_eur')}</span>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className="input input-bordered input-sm sm:input-md w-full"
                  value={shippingFlatEur}
                  onChange={(e) => setShippingFlatEur(e.target.value)}
                />
              </label>
            </div>

            <div>
              <p className="text-sm font-medium">{t('admin.settings.installation_auto_title')}</p>
              <p className="text-xs text-base-content/60 mt-1">{t('admin.settings.installation_auto_help')}</p>
              <label className="form-field max-w-xs mt-2">
                <span className="label-text">{t('admin.settings.installation_quote_above_eur')}</span>
                <input
                  type="number"
                  min={0}
                  step="0.01"
                  className="input input-bordered input-sm sm:input-md w-full"
                  value={installationQuoteAbove}
                  onChange={(e) => setInstallationQuoteAbove(e.target.value)}
                />
              </label>
              <p className="text-xs text-base-content/60 mt-2">{t('admin.settings.installation_tiers_hint')}</p>
              {installationTierMismatch ? (
                <div role="status" className="alert alert-warning text-sm mt-2 py-2">
                  {t('admin.settings.installation_tier_mismatch_warning')}
                </div>
              ) : null}
              <div className="mt-3 overflow-x-auto">
                <table className="table table-sm table-zebra border border-base-200 rounded-box">
                  <thead>
                    <tr>
                      <th>{t('admin.settings.installation_tier_max_merchandise')}</th>
                      <th>{t('admin.settings.installation_tier_fee')}</th>
                      <th className="w-24 text-end">{t('admin.settings.installation_tier_actions')}</th>
                    </tr>
                  </thead>
                  <tbody>
                    {installationTiers.map((row, index) => (
                      <tr key={index}>
                        <td>
                          <input
                            type="number"
                            min={0.01}
                            step="0.01"
                            className="input input-bordered input-sm w-full min-w-[6rem]"
                            value={row.max_merchandise_eur}
                            onChange={(e) => updateInstallationTier(index, 'max_merchandise_eur', e.target.value)}
                            aria-label={t('admin.settings.installation_tier_max_merchandise')}
                          />
                        </td>
                        <td>
                          <input
                            type="number"
                            min={0}
                            step="0.01"
                            className="input input-bordered input-sm w-full min-w-[6rem]"
                            value={row.fee_eur}
                            onChange={(e) => updateInstallationTier(index, 'fee_eur', e.target.value)}
                            aria-label={t('admin.settings.installation_tier_fee')}
                          />
                        </td>
                        <td className="text-end">
                          <button
                            type="button"
                            className="btn btn-ghost btn-xs text-error"
                            onClick={() => removeInstallationTier(index)}
                            disabled={installationTiers.length <= 1}
                          >
                            {t('admin.settings.installation_tier_remove')}
                          </button>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
              <button type="button" className="btn btn-outline btn-sm mt-2" onClick={addInstallationTier}>
                {t('admin.settings.installation_tier_add')}
              </button>
            </div>

            {/* Sin implementar — solo UI: envío por código postal */}
            <div>
              <p className="text-sm font-medium">{t('admin.settings.shipping_postal_title')}</p>
              <div role="status" className="alert alert-info text-sm mt-2">
                <span>{t('admin.settings.shipping_postal_not_implemented')}</span>
              </div>
            </div>
          </div>
        </AdminSettingsCollapseSection>

        <AdminSettingsCollapseSection
          title={t('admin.settings.index_columns_title')}
          subtitle={t('admin.settings.index_columns_subtitle')}
        >
          <div className="space-y-6 min-w-0 px-1 pb-1">
            {ADMIN_INDEX_TABLE_ORDER.map((tableId) => {
              const cols = ADMIN_INDEX_COLUMN_REGISTRY[tableId] ?? [];
              const titleKey = ADMIN_INDEX_TABLE_META[tableId]?.titleKey;
              if (!titleKey || cols.length === 0) return null;
              return (
                <AdminIndexColumnsFieldset
                  key={tableId}
                  tableId={tableId}
                  cols={cols}
                  title={t(titleKey)}
                  columnPrefs={columnPrefs}
                  setColumnPrefs={setColumnPrefs}
                  columnOrder={columnOrder}
                  setColumnOrder={setColumnOrder}
                  t={t}
                />
              );
            })}
          </div>
        </AdminSettingsCollapseSection>

        <div className="flex justify-end">
          <button type="submit" className="btn btn-primary btn-sm sm:btn-md" disabled={saving || recalculating}>
            {saving ? t('common.loading') : t('common.save')}
          </button>
        </div>
      </form>
    </div>
  );
}
