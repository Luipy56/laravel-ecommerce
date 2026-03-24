import { z } from 'zod';

/**
 * Map a Zod issue to a translated string (i18n keys in validation.*).
 */
export function issueToMessage(issue, t) {
  const msg = issue.message;
  if (typeof msg === 'string' && msg.startsWith('validation.')) {
    return t(msg);
  }

  switch (issue.code) {
    case z.ZodIssueCode.invalid_type:
      if (issue.received === 'undefined' || issue.received === 'null') {
        return t('validation.required');
      }
      return t('validation.invalid');
    case z.ZodIssueCode.too_small: {
      if (issue.type === 'string') {
        if (typeof msg === 'string' && msg.startsWith('validation.')) {
          return t(msg);
        }
        return t('validation.min_length', { min: issue.minimum });
      }
      if (issue.type === 'number') {
        return t('validation.number_min', { min: issue.minimum });
      }
      if (issue.type === 'array') {
        return t('validation.required');
      }
      return t('validation.invalid');
    }
    case z.ZodIssueCode.too_big: {
      if (issue.type === 'string') {
        if (typeof msg === 'string' && msg.startsWith('validation.')) {
          return t(msg);
        }
        return t('validation.max_length', { max: issue.maximum });
      }
      if (issue.type === 'number') {
        return t('validation.number_max', { max: issue.maximum });
      }
      return t('validation.invalid');
    }
    case z.ZodIssueCode.invalid_string:
      if (issue.validation === 'email') {
        return t('validation.email');
      }
      if (issue.validation === 'url') {
        return t('validation.url');
      }
      if (issue.validation === 'regex') {
        return t('validation.format');
      }
      return t('validation.format');
    case z.ZodIssueCode.invalid_enum_value:
      return t('validation.invalid');
    case z.ZodIssueCode.custom:
      return t('validation.invalid');
    default:
      return t('validation.invalid');
  }
}
