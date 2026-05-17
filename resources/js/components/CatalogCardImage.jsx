import React, { useEffect, useMemo, useState } from 'react';
import { captureGifStillFrame } from '../lib/captureGifStillFrame';
import { isGifImageUrl } from '../lib/isGifImageUrl';

/**
 * Product/pack card image: animated GIFs play only while `animate` is true (card hover).
 * Non-GIF images and detail/show pages use a plain <img> instead.
 */
export default function CatalogCardImage({
  src,
  alt,
  className,
  contentType = null,
  animate = false,
  onError,
}) {
  const isGif = isGifImageUrl(src, contentType);
  const [stillSrc, setStillSrc] = useState(null);
  const [reducedMotion, setReducedMotion] = useState(false);

  useEffect(() => {
    const mq = window.matchMedia('(prefers-reduced-motion: reduce)');
    const update = () => setReducedMotion(mq.matches);
    update();
    mq.addEventListener('change', update);
    return () => mq.removeEventListener('change', update);
  }, []);

  useEffect(() => {
    if (!isGif) {
      setStillSrc(null);
      return undefined;
    }
    let cancelled = false;
    captureGifStillFrame(src).then((dataUrl) => {
      if (!cancelled && dataUrl) {
        setStillSrc(dataUrl);
      }
    });
    return () => {
      cancelled = true;
    };
  }, [src, isGif]);

  const displaySrc = useMemo(() => {
    if (!isGif) {
      return src;
    }
    const playGif = animate && !reducedMotion;
    if (playGif) {
      return src;
    }
    return stillSrc ?? src;
  }, [isGif, src, stillSrc, animate, reducedMotion]);

  return (
    <img
      src={displaySrc}
      alt={alt}
      className={className}
      onError={onError}
    />
  );
}
