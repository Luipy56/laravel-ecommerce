import React, { useCallback, useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { api } from '../../api';
import PageTitle from '../../components/PageTitle';
import { useAdminToast } from '../../contexts/AdminToastContext';

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
  });

  const handleSave = async (e) => {
    e.preventDefault();
    setSaveError('');
    setSaving(true);
    try {
      const { data } = await api.put('admin/settings', buildPutBody());
      if (data.success && data.data) {
        applyPayload(data.data);
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
    return <div className="alert alert-error">{loadError}</div>;
  }

  return (
    <div className="space-y-6">
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

      <form onSubmit={handleSave} className="space-y-6">
        <section className="card bg-base-100 shadow border border-base-200">
          <div className="card-body space-y-4">
            <h2 className="card-title text-lg">{t('admin.settings.section_home')}</h2>
            <p className="text-sm text-base-content/70">{t('admin.settings.section_home_help')}</p>

            <div className="divider my-1">{t('admin.settings.low_stock')}</div>
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={lowStockEnabled}
                onChange={(e) => setLowStockEnabled(e.target.checked)}
              />
              <span className="label-text">{t('admin.settings.low_stock_enabled')}</span>
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
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={lowStockBlacklistEnabled}
                onChange={(e) => setLowStockBlacklistEnabled(e.target.checked)}
              />
              <span className="label-text">{t('admin.settings.blacklist_enabled')}</span>
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
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={overstockEnabled}
                onChange={(e) => setOverstockEnabled(e.target.checked)}
              />
              <span className="label-text">{t('admin.settings.overstock_enabled')}</span>
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
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={overstockBlacklistEnabled}
                onChange={(e) => setOverstockBlacklistEnabled(e.target.checked)}
              />
              <span className="label-text">{t('admin.settings.overstock_blacklist_enabled')}</span>
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
          <div className="card-body space-y-4">
            <h2 className="card-title text-lg">{t('admin.settings.section_personalized')}</h2>
            <label className="label cursor-pointer justify-start gap-3">
              <input
                type="checkbox"
                className="toggle toggle-primary"
                checked={acceptPersonalizedSolutions}
                onChange={(e) => setAcceptPersonalizedSolutions(e.target.checked)}
              />
              <span className="label-text">{t('admin.settings.accept_personalized_solutions')}</span>
            </label>
          </div>
        </section>

        <section className="card bg-base-100 shadow border border-base-200 border-dashed">
          <div className="card-body py-6">
            <p className="text-sm text-base-content/60">{t('admin.settings.future_placeholder')}</p>
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
