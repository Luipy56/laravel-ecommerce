/** Max length aligned with Laravel `max:20` on postal fields. */
export const POSTAL_CODE_MAX_LENGTH = 20;

/** Known form field names that must accept digits-only postal codes. */
export const POSTAL_CODE_FIELD_NAMES = new Set([
  'address_postal_code',
  'shipping_postal_code',
  'installation_postal_code',
  'postal_code',
]);

/**
 * Strip non-digits (paste-safe). Caps length for Spanish-style numeric CP and backend limit.
 */
export function sanitizePostalCodeDigits(raw, maxLen = POSTAL_CODE_MAX_LENGTH) {
  return String(raw ?? '')
    .replace(/\D/g, '')
    .slice(0, maxLen);
}

/** Use in generic onChange handlers to coerce postal fields while typing. */
export function coercePostalCodeFieldValue(fieldName, value) {
  return POSTAL_CODE_FIELD_NAMES.has(fieldName) ? sanitizePostalCodeDigits(value) : value;
}
