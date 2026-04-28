/**
 * Human-readable message from a Laravel 422 validation response (axios error).
 * Prefer summary message; fall back to first field error. Postal regex uses i18n when needed.
 *
 * @param {unknown} err
 * @param {import('i18next').TFunction} t
 */
export function messageFromApiValidationError(err, t) {
  const data = err && typeof err === 'object' && 'response' in err ? err.response?.data : undefined;
  if (!data || typeof data !== 'object') {
    return t('common.error');
  }

  const postalKeys = new Set(['address_postal_code', 'shipping_postal_code', 'installation_postal_code', 'postal_code']);
  const errs = data.errors;
  if (errs && typeof errs === 'object' && !Array.isArray(errs)) {
    for (const key of postalKeys) {
      const arr = errs[key];
      if (Array.isArray(arr) && typeof arr[0] === 'string') {
        return t('validation.postal_digits');
      }
    }
    const firstKey = Object.keys(errs)[0];
    const first = firstKey && errs[firstKey]?.[0];
    if (typeof first === 'string') {
      return first;
    }
  }

  if (typeof data.message === 'string' && data.message.trim() !== '') {
    return data.message;
  }

  return t('common.error');
}
