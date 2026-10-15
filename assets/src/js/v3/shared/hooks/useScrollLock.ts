import { useEffect, useRef } from 'react';

let scrollbarWidth: number | null = null;
const lockStack: symbol[] = [];
let originalStyles: {
  overflow: string;
  paddingRight: string;
} | null = null;

const getScrollbarWidth = (): number => {
  if (scrollbarWidth !== null) return scrollbarWidth;

  const outer = document.createElement('div');
  outer.style.visibility = 'hidden';
  outer.style.overflow = 'scroll';
  outer.style.width = '100px';
  document.body.appendChild(outer);

  const inner = document.createElement('div');
  inner.style.width = '100%';
  outer.appendChild(inner);

  scrollbarWidth = outer.offsetWidth - inner.offsetWidth;
  document.body.removeChild(outer);

  return scrollbarWidth;
};

const applyScrollLock = () => {
  if (originalStyles) {
    return;
  }

  const scrollBarWidth = getScrollbarWidth();
  const hasScrollbar = window.innerWidth > document.documentElement.clientWidth;

  originalStyles = {
    overflow: document.body.style.overflow,
    paddingRight: document.body.style.paddingRight,
  };

  document.body.style.overflow = 'hidden';

  if (hasScrollbar && scrollBarWidth > 0) {
    const currentPadding = parseInt(window.getComputedStyle(document.body).paddingRight || '0', 10);
    document.body.style.paddingRight = `${currentPadding + scrollBarWidth}px`;
  }
};

const removeScrollLock = () => {
  if (!originalStyles) {
    return;
  }

  document.body.style.overflow = originalStyles.overflow;
  document.body.style.paddingRight = originalStyles.paddingRight;

  originalStyles = null;
};

export const lockScroll = (): symbol => {
  const lockId = Symbol('scroll-lock');
  lockStack.push(lockId);

  if (lockStack.length === 1) {
    applyScrollLock();
  }

  return lockId;
};

const pendingUnlocks = new Set<symbol>();

export const unlockScroll = (lockId: symbol) => {
  const index = lockStack.indexOf(lockId);
  if (index === -1) {
    return;
  }

  lockStack.splice(index, 1);
  pendingUnlocks.delete(lockId);

  if (lockStack.length === 0 && pendingUnlocks.size === 0) {
    removeScrollLock();
  }
};

export const useScrollLock = (enabled: boolean = true) => {
  const lockIdRef = useRef<symbol | null>(null);

  useEffect(() => {
    if (!enabled) {
      if (lockIdRef.current) {
        unlockScroll(lockIdRef.current);
        lockIdRef.current = null;
      }
      return;
    }

    lockIdRef.current = lockScroll();

    return () => {
      if (lockIdRef.current) {
        const lockToRelease = lockIdRef.current;
        lockIdRef.current = null;

        pendingUnlocks.add(lockToRelease);

        requestAnimationFrame(() => {
          unlockScroll(lockToRelease);
        });
      }
    };
  }, [enabled]);
};
