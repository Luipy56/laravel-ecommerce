import React, { useEffect, useState } from 'react';
import { useTranslation } from 'react-i18next';

const empty = {
  sort_order: 0,
  is_active: true,
  question_ca: '',
  question_es: '',
  question_en: '',
  answer_ca: '',
  answer_es: '',
  answer_en: '',
};

export default function AdminFaqForm({ initial = null, onSubmit, loading, error }) {
  const { t } = useTranslation();
  const [form, setForm] = useState(empty);

  useEffect(() => {
    if (initial) {
      setForm({
        sort_order: initial.sort_order ?? 0,
        is_active: !!initial.is_active,
        question_ca: initial.question_ca ?? '',
        question_es: initial.question_es ?? '',
        question_en: initial.question_en ?? '',
        answer_ca: initial.answer_ca ?? '',
        answer_es: initial.answer_es ?? '',
        answer_en: initial.answer_en ?? '',
      });
    } else {
      setForm(empty);
    }
  }, [initial]);

  const setField = (key, value) => setForm((f) => ({ ...f, [key]: value }));

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit({
      ...form,
      sort_order: Number(form.sort_order) || 0,
    });
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      {error ? <div className="alert alert-error text-sm">{error}</div> : null}
      <label className="flex items-center gap-2 cursor-pointer">
        <input
          type="checkbox"
          className="toggle toggle-sm"
          checked={form.is_active}
          onChange={(e) => setField('is_active', e.target.checked)}
        />
        <span className="text-sm">{t('admin.faqs.is_active')}</span>
      </label>
      <label className="form-field w-full max-w-xs">
        <span className="form-label">{t('admin.faqs.sort_order')}</span>
        <input
          type="number"
          min={0}
          className="input input-bordered input-sm w-full"
          value={form.sort_order}
          onChange={(e) => setField('sort_order', e.target.value)}
        />
      </label>

      {(['ca', 'es', 'en']).map((lng) => (
        <fieldset key={lng} className="fieldset border border-base-300 rounded-box p-4 gap-2">
          <legend className="fieldset-legend text-sm">{lng.toUpperCase()}</legend>
          <label className="form-field w-full">
            <span className="form-label">{t('admin.faqs.question')}</span>
            <input
              type="text"
              className="input input-bordered w-full"
              value={form[`question_${lng}`]}
              onChange={(e) => setField(`question_${lng}`, e.target.value)}
              required
            />
          </label>
          <label className="form-field w-full">
            <span className="form-label">{t('admin.faqs.answer')}</span>
            <textarea
              className="textarea textarea-bordered w-full min-h-[100px]"
              value={form[`answer_${lng}`]}
              onChange={(e) => setField(`answer_${lng}`, e.target.value)}
              required
            />
          </label>
        </fieldset>
      ))}

      <div className="flex justify-end pt-2">
        <button type="submit" className="btn btn-primary btn-sm sm:btn-md" disabled={loading}>
          {loading ? t('common.loading') : t('common.save')}
        </button>
      </div>
    </form>
  );
}
