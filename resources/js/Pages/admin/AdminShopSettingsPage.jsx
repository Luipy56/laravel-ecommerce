import React, { useCallback, useEffect, useState } from 'react';
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
import {
  adminShopSettingsQueryKey,
  buildAdminIndexColumnsPayload,
  columnPrefsFromServer,
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

  const [columnPrefs, setColumnPrefs] = useState({});

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
    setColumnPrefs(columnPrefsFromServer(d.admin_index_columns));
  }, []);

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

  const buildPutBody = () => ({
    low_stock_enabled: lowStockEnabled,
    low_stock_threshold: Math.max(0, parseInt(String(lowStockThreshold), 10) || 0),
    low_stock_blacklist_enabled: lowStockBlacklistEnabled,
    low_stock_blacklist_product_ids: parseIdList(lowStockBlacklistText),
    overstock_enabled: overstockEnabled,
    overstock_threshold: Math.max(0, parseInt(String(overstockThreshold), 10) || 0),
    overstock_blacklist_enabled: overstockBlacklistEnabled,
    overstock_blacklist_product_ids: parseIdList(overstockBlacklistText),
    accept_personalized_solutions: acceptPersonalizedSolutions,
    admin_index_columns: buildAdminIndexColumnsPayload(columnPrefs),
  });

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
      else setSaveError(err.response?.data?.message || t('common.error'));
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

      <form onSubmit={handleSave} className="space-y-6 min-w-0">
        <section className="card bg-base-100 shadow border border-base-200">
          <div className="card-body space-y-4 min-w-0">
            <h2 className="card-title text-lg">{t('admin.settings.section_home')}</h2>
            <p className="text-sm text-base-content/70">{t('admin.settings.section_home_help')}</p>

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
          </div>
        </section>

        <section className="card bg-base-100 shadow border border-base-200">
          <div className="card-body space-y-4 min-w-0">
            <h2 className="card-title text-lg">{t('admin.settings.section_personalized')}</h2>
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
        </section>

        <section className="card bg-base-100 shadow border border-base-200">
          <div className="card-body space-y-6 min-w-0">
            <div className="min-w-0">
              <h2 className="card-title text-lg">{t('admin.settings.index_columns_title')}</h2>
              <p className="text-sm text-base-content/70 mt-1">{t('admin.settings.index_columns_help')}</p>
            </div>
            {ADMIN_INDEX_TABLE_ORDER.map((tableId) => {
              const cols = ADMIN_INDEX_COLUMN_REGISTRY[tableId] ?? [];
              const titleKey = ADMIN_INDEX_TABLE_META[tableId]?.titleKey;
              if (!titleKey || cols.length === 0) return null;
              return (
                <fieldset key={tableId} className="fieldset border border-base-200 rounded-box p-4 space-y-3 min-w-0">
                  <legend className="fieldset-legend text-base font-semibold">{t(titleKey)}</legend>
                  <div className="flex flex-wrap gap-x-4 gap-y-2">
                    {cols.map((col) => (
                      <label
                        key={col.id}
                        className="label w-full min-w-0 cursor-pointer items-start justify-start gap-2 py-1 sm:w-[calc(50%-0.5rem)] sm:max-w-[calc(50%-0.5rem)]"
                      >
                        <input
                          type="checkbox"
                          className="checkbox checkbox-sm checkbox-primary shrink-0 mt-0.5"
                          checked={columnPrefs[tableId]?.[col.id] !== false}
                          onChange={(e) => {
                            const checked = e.target.checked;
                            setColumnPrefs((prev) => ({
                              ...prev,
                              [tableId]: { ...prev[tableId], [col.id]: checked },
                            }));
                          }}
                        />
                        <span className="label-text text-sm min-w-0 flex-1">{t(col.labelKey)}</span>
                      </label>
                    ))}
                  </div>
                </fieldset>
              );
            })}
          </div>
        </section>

        <div className="flex justify-end">
          <button type="submit" className="btn btn-primary btn-sm sm:btn-md" disabled={saving || recalculating}>
            {saving ? t('common.loading') : t('common.save')}
          </button>
        </div>
      </form>
    </div>
  );
}
