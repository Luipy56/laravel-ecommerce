import React, { useState, useCallback } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import PageTitle from '../components/PageTitle';

/** 64 hex chars, lowercase (matches API route constraint). */
const TOKEN_64 = /^[a-f0-9]{64}$/;

/**
 * Normalize pasted code: full URL, or raw hex. Returns lowercase hex or null.
 */
export function normalizePersonalizedSolutionToken(input) {
  const raw = String(input ?? '').trim();
  if (!raw) {
    return null;
  }
  const fromPath = raw.match(/client\/personalized-solutions\/([a-f0-9]{64})/i);
  if (fromPath) {
    return fromPath[1].toLowerCase();
  }
  const fromQuery = raw.match(/[?&#]token=([a-f0-9]{64})/i);
  if (fromQuery) {
    return fromQuery[1].toLowerCase();
  }
  const onlyHex = raw.replace(/[^a-f0-9]/gi, '');
  if (onlyHex.length === 64) {
    return onlyHex.toLowerCase();
  }
  return null;
}

export default function ClientPersonalizedSolutionAccessPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [value, setValue] = useState('');
  const [error, setError] = useState('');

  const go = useCallback(() => {
    setError('');
    const token = normalizePersonalizedSolutionToken(value);
    if (!token || !TOKEN_64.test(token)) {
      setError(t('shop.personalized_solution_access.invalid_code'));
      return;
    }
    navigate(`/client/personalized-solutions/${token}`, { replace: true });
  }, [value, navigate, t]);

  return (
    <div className="mx-auto w-full min-w-0 max-w-lg">
      <div className="mb-2">
        <Link to="/custom-solution" className="link link-primary text-sm">
          {t('shop.personalized_solution_access.new_request_link')}
        </Link>
      </div>
      <PageTitle className="mb-2">{t('shop.personalized_solution_access.title')}</PageTitle>
      <p className="text-sm text-base-content/80 mb-6">
        {t('shop.personalized_solution_access.intro')}
      </p>
      {error && (
        <div className="alert alert-error mb-4" role="alert">
          {error}
        </div>
      )}
      <form
        className="card bg-base-100 shadow"
        onSubmit={(e) => {
          e.preventDefault();
          go();
        }}
      >
        <div className="card-body gap-4">
          <label className="form-field w-full">
            <span className="form-label">{t('shop.personalized_solution_access.label')}</span>
            <textarea
              className="textarea textarea-bordered w-full font-mono text-sm min-h-[4.5rem]"
              placeholder={t('shop.personalized_solution_access.placeholder')}
              value={value}
              onChange={(e) => { setValue(e.target.value); setError(''); }}
              autoComplete="off"
              spellCheck="false"
              aria-label={t('shop.personalized_solution_access.label')}
            />
            <p className="text-xs text-base-content/60">{t('shop.personalized_solution_access.hint_paste')}</p>
          </label>
          <div className="flex justify-end">
            <button type="submit" className="btn btn-primary">
              {t('shop.personalized_solution_access.submit')}
            </button>
          </div>
        </div>
      </form>
      <p className="text-xs text-base-content/50 mt-6">{t('shop.personalized_solution_access.url_legend')}</p>
    </div>
  );
}
