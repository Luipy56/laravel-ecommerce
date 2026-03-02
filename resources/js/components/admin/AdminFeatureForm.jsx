import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import { useTranslation } from 'react-i18next';

export default function AdminFeatureForm({ feature = null, featureNames = [], onSubmit, loading = false, error = '' }) {
  const { t } = useTranslation();
  const [featureNameId, setFeatureNameId] = useState(feature?.feature_name_id ?? '');
  const [value, setValue] = useState(feature?.value ?? '');
  const [isActive, setIsActive] = useState(feature?.is_active ?? true);

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({
      feature_name_id: Number(featureNameId),
      value: value.trim(),
      is_active: !!isActive,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      {error && (
        <div role="alert" className="alert alert-error text-sm">
          {error}
        </div>
      )}

      <label className="form-field">
        <span className="form-label">{t('admin.features.type')} *</span>
        <select
          className="select select-bordered w-full"
          value={featureNameId}
          onChange={(e) => setFeatureNameId(e.target.value)}
          required
          aria-label={t('admin.features.type')}
        >
          <option value=""></option>
          {featureNames.map((n) => (
            <option key={n.id} value={n.id}>
              {n.name}
            </option>
          ))}
        </select>
      </label>

      <label className="form-field">
        <span className="form-label">{t('admin.features.value')} *</span>
        <input
          type="text"
          className="input input-bordered w-full"
          value={value}
          onChange={(e) => setValue(e.target.value)}
          required
          aria-label={t('admin.features.value')}
        />
      </label>

      <label className="label cursor-pointer gap-2">
        <input
          type="checkbox"
          className="checkbox checkbox-sm"
          checked={isActive}
          onChange={(e) => setIsActive(e.target.checked)}
        />
        <span className="label-text">{t('admin.products.is_active')}</span>
      </label>

      <div className="flex justify-between gap-2 pt-4">
        <Link to="/admin/features" className="btn btn-ghost">
          {t('common.back')}
        </Link>
        <button type="submit" className="btn btn-primary" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
