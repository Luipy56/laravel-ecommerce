/**
 * True when the image is an animated GIF (by URL extension or MIME).
 */
export function isGifImageUrl(url, contentType = null) {
  if (contentType && typeof contentType === 'string' && contentType.toLowerCase() === 'image/gif') {
    return true;
  }
  if (!url || typeof url !== 'string') {
    return false;
  }
  const path = url.split('?')[0].split('#')[0].toLowerCase();
  return path.endsWith('.gif');
}
