const POSTAL_FIELD_KEYS = new Set(['address_postal_code', 'shipping_postal_code', 'installation_postal_code', 'postal_code']);

/**
 * First message per form field from Laravel `errors` (422). Keys match request attributes;
 * `attachments.*` is collapsed to `attachments`. Postal regex failures use `validation.postal_digits`.
 *
 * @param {unknown} err
 * @param {import('i18next').TFunction} t
 * @returns {Record<string, string>}
 */
export function fieldErrorsFromApiValidation(err, t) {
  const data = err && typeof err === 'object' && 'response' in err ? err.response?.data : undefined;
  if (!data || typeof data !== 'object' || !data.errors || typeof data.errors !== 'object' || Array.isArray(data.errors)) {
    return {};
  }

  /** @type {Record<string, string>} */
  const out = {};

  for (const [key, messages] of Object.entries(data.errors)) {
    if (!Array.isArray(messages) || typeof messages[0] !== 'string') {
      continue;
    }
    const formKey = /^attachments(\.|$)/.test(key) ? 'attachments' : key;
    let text = messages[0];
    if (POSTAL_FIELD_KEYS.has(key)) {
      text = t('validation.postal_digits');
    }
    if (out[formKey] == null) {
      out[formKey] = text;
    }
  }

  return out;
}

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

  const errs = data.errors;
  if (errs && typeof errs === 'object' && !Array.isArray(errs)) {
    for (const key of POSTAL_FIELD_KEYS) {
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
