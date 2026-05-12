import { useRef, useEffect, useCallback } from 'react';

const FRICTION = 0.92;          // velocity decay per frame (~60fps)
const MIN_VELOCITY = 0.4;       // px/frame — stop momentum below this
const DRAG_THRESHOLD = 5;       // px before committing to drag
const VELOCITY_WINDOW_MS = 80;  // use samples from last N ms for velocity

/**
 * Horizontal drag-to-scroll with momentum inertia.
 * No snap — scroll stops wherever the inertia lands.
 * Uses window-level listeners (no setPointerCapture) so child links stay clickable.
 *
 * Returns { scrollRef, wrapperRef }.
 */
export default function useDragScroll() {
  const scrollRef   = useRef(null);
  const wrapperRef  = useRef(null);
  const drag        = useRef(null);
  const justDragged = useRef(false);
  const rafId       = useRef(0);
  const edgeRafId   = useRef(0);

  // ─── Edge fade classes ────────────────────────────────────────────────────

  const updateEdgeClasses = useCallback(() => {
    const el      = scrollRef.current;
    const wrapper = wrapperRef.current;
    if (!el || !wrapper) return;
    wrapper.classList.toggle('at-start', el.scrollLeft <= 1);
    wrapper.classList.toggle('at-end', el.scrollLeft + el.clientWidth >= el.scrollWidth - 1);
  }, []);

  const onScroll = useCallback(() => {
    cancelAnimationFrame(edgeRafId.current);
    edgeRafId.current = requestAnimationFrame(updateEdgeClasses);
  }, [updateEdgeClasses]);

  // ─── Momentum animation ───────────────────────────────────────────────────

  const applyMomentum = useCallback((velocityPxFrame) => {
    let vel = velocityPxFrame;
    const step = () => {
      const el = scrollRef.current;
      if (!el || Math.abs(vel) < MIN_VELOCITY) return;
      el.scrollLeft -= vel;
      vel *= FRICTION;
      rafId.current = requestAnimationFrame(step);
    };
    rafId.current = requestAnimationFrame(step);
  }, []);

  // ─── Window-level move / up ───────────────────────────────────────────────

  const onDocPointerMove = useCallback((e) => {
    const d = drag.current;
    if (!d || e.pointerId !== d.pointerId) return;
    const el = scrollRef.current;
    if (!el) return;

    d.totalMoved += Math.abs(e.clientX - d.lastX);
    d.lastX = e.clientX;

    if (!d.dragging && d.totalMoved > DRAG_THRESHOLD) {
      d.dragging = true;
      el.style.cursor = 'grabbing';
    }

    if (d.dragging) {
      el.scrollLeft = d.startScrollLeft - (e.clientX - d.startX);
      d.samples.push({ x: e.clientX, t: performance.now() });
      if (d.samples.length > 30) d.samples.shift();
    }
  }, []);

  const onDocPointerUp = useCallback((e) => {
    const d = drag.current;
    if (!d || e.pointerId !== d.pointerId) return;
    drag.current = null;

    window.removeEventListener('pointermove',   onDocPointerMove);
    window.removeEventListener('pointerup',     onDocPointerUp);
    window.removeEventListener('pointercancel', onDocPointerUp);

    cancelAnimationFrame(rafId.current);

    const el = scrollRef.current;
    if (!el) return;
    el.style.cursor = '';

    if (!d.dragging) return; // pure click — let it propagate

    justDragged.current = true;
    setTimeout(() => { justDragged.current = false; }, 300);

    // Velocity from recent samples only
    const now    = performance.now();
    const recent = d.samples.filter(s => now - s.t < VELOCITY_WINDOW_MS);
    let vel = 0;
    if (recent.length >= 2) {
      const first = recent[0];
      const last  = recent[recent.length - 1];
      const dt    = last.t - first.t;
      if (dt > 0) vel = ((last.x - first.x) / dt) * 16;
    }

    if (Math.abs(vel) >= MIN_VELOCITY) applyMomentum(vel);
  }, [onDocPointerMove, applyMomentum]);

  // ─── Container pointerdown ────────────────────────────────────────────────

  const onPointerDown = useCallback((e) => {
    if (e.button !== 0) return;
    const el = scrollRef.current;
    if (!el) return;

    cancelAnimationFrame(rafId.current);

    drag.current = {
      pointerId:       e.pointerId,
      startX:          e.clientX,
      startScrollLeft: el.scrollLeft,
      lastX:           e.clientX,
      totalMoved:      0,
      dragging:        false,
      samples:         [{ x: e.clientX, t: performance.now() }],
    };

    window.addEventListener('pointermove',   onDocPointerMove);
    window.addEventListener('pointerup',     onDocPointerUp);
    window.addEventListener('pointercancel', onDocPointerUp);
  }, [onDocPointerMove, onDocPointerUp]);

  const onClickCapture = useCallback((e) => {
    if (justDragged.current) {
      justDragged.current = false;
      e.preventDefault();
      e.stopPropagation();
    }
  }, []);

  const preventNativeDrag = useCallback((e) => {
    if (drag.current?.dragging) e.preventDefault();
  }, []);

  // ─── Setup / teardown ─────────────────────────────────────────────────────

  useEffect(() => {
    const el = scrollRef.current;
    if (!el) return;

    el.addEventListener('pointerdown', onPointerDown);
    el.addEventListener('dragstart',   preventNativeDrag);
    el.addEventListener('click',       onClickCapture, true);
    el.addEventListener('scroll',      onScroll, { passive: true });

    updateEdgeClasses();

    return () => {
      cancelAnimationFrame(rafId.current);
      cancelAnimationFrame(edgeRafId.current);
      el.removeEventListener('pointerdown', onPointerDown);
      el.removeEventListener('dragstart',   preventNativeDrag);
      el.removeEventListener('click',       onClickCapture, true);
      el.removeEventListener('scroll',      onScroll);
      window.removeEventListener('pointermove',   onDocPointerMove);
      window.removeEventListener('pointerup',     onDocPointerUp);
      window.removeEventListener('pointercancel', onDocPointerUp);
    };
  }, [onPointerDown, preventNativeDrag, onClickCapture, onScroll, updateEdgeClasses, onDocPointerMove, onDocPointerUp]);

  return { scrollRef, wrapperRef };
}
