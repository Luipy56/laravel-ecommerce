/**
 * After a failed form submit, scroll so the user sees validation feedback.
 * Deferred so React can paint error state before scrolling.
 */
function deferScroll(fn) {
  queueMicrotask(() => {
    requestAnimationFrame(() => {
      requestAnimationFrame(fn);
    });
  });
}

/** Full-page / drawer forms: scroll the window to the top (summary alert near title). */
export function scrollWindowToTopOnFormError() {
  deferScroll(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/** Scroll a page section into view so inline validation feedback stays visible. */
export function scrollFormSectionIntoView(sectionId) {
  deferScroll(() => {
    const el = typeof sectionId === 'string' ? document.getElementById(sectionId) : sectionId;
    el?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
}

/** Modal forms (daisyUI): scroll the open dialog content to the top so inline errors are visible. */
export function scrollOpenModalBoxToTop() {
  deferScroll(() => {
    const box = document.querySelector('dialog.modal-open .modal-box');
    box?.scrollTo({ top: 0, behavior: 'smooth' });
  });
}
