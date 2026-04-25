/**
 * Parse a 64-hex public_token from a pasted string (code only, full portal path, or optional ?token=).
 * Returns lowercase [a-f0-9]{64} or null.
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

const TOKEN_64 = /^[a-f0-9]{64}$/;

export function isValidPersonalizedSolutionToken(token) {
  return token != null && TOKEN_64.test(String(token));
}
