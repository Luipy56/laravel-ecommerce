/**
 * @param {unknown} err
 * @returns {boolean}
 */
export function isEmailNotVerifiedError(err) {
  const status = err?.response?.status;
  const code = err?.response?.data?.code;
  return status === 403 && code === 'email_not_verified';
}

/**
 * User-facing message for failed profile API calls (Axios).
 * @param {unknown} err
 * @param {import('i18next').TFunction} t
 * @returns {string}
 */
export function profileMutationErrorMessage(err, t) {
  if (isEmailNotVerifiedError(err)) {
    return t('auth.verify_email_required_to_save');
  }
  const msg = err?.response?.data?.message;
  if (typeof msg === 'string' && msg.trim()) {
    return msg.trim();
  }
  return t('common.error');
}
