import { useEffect } from 'react';

/**
 * Updates document title and meta description for browser tabs and JS-capable crawlers.
 * Does not replace server-rendered OG tags required by WhatsApp / Facebook.
 */
export function useDocumentMeta(title, description) {
  useEffect(() => {
    if (title) {
      document.title = title;
    }
  }, [title]);

  useEffect(() => {
    if (!description) {
      return;
    }
    let el = document.querySelector('meta[name="description"]');
    if (!el) {
      el = document.createElement('meta');
      el.setAttribute('name', 'description');
      document.head.appendChild(el);
    }
    el.setAttribute('content', description);
  }, [description]);
}
