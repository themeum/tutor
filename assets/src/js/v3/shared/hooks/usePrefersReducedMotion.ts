import { useEffect, useState } from 'react';

type MotionAttribute = 'auto' | 'reduce' | null;

const MOTION_ATTRIBUTE = 'data-tutor-motion';

const readMotionAttribute = (): MotionAttribute => {
  if (typeof document === 'undefined') {
    return null;
  }
  return document.documentElement.getAttribute(MOTION_ATTRIBUTE) as MotionAttribute;
};

const systemPrefersReducedMotion = () => {
  if (typeof window === 'undefined' || !window.matchMedia) {
    return false;
  }
  return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
};

const resolvePreference = () => {
  const attribute = readMotionAttribute();

  // Explicit override always wins.
  if (attribute === 'reduce') return true;

  // 'auto' (or unset) defers to the OS/browser setting.
  return systemPrefersReducedMotion();
};

/**
 * Resolves whether motion should be reduced, honoring:
 * - data-tutor-motion="reduce" on <html>  -> always reduce
 * - data-tutor-motion="auto" (or absent)  -> defer to prefers-reduced-motion
 */
export const usePrefersReducedMotion = () => {
  const [prefersReduced, setPrefersReduced] = useState(resolvePreference);

  useEffect(() => {
    if (typeof window === 'undefined' || !window.matchMedia) {
      return;
    }

    const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    const update = () => setPrefersReduced(resolvePreference());

    update();
    mediaQuery.addEventListener('change', update);

    // In case data-tutor-motion is toggled at runtime (e.g. a settings panel)
    const observer = new MutationObserver(update);
    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: [MOTION_ATTRIBUTE],
    });

    return () => {
      mediaQuery.removeEventListener('change', update);
      observer.disconnect();
    };
  }, []);

  return prefersReduced;
};
