import { Children, cloneElement, useEffect, useRef, type ReactElement, type ReactNode } from 'react';

interface FocusTrapProps {
  children: ReactNode;
  blurPrevious?: boolean;
}

const FocusTrap = ({ children, blurPrevious = false }: FocusTrapProps) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const previousActiveElementRef = useRef<HTMLElement | null>(null);

  useEffect(() => {
    const container = containerRef.current;

    if (!container) {
      return;
    }

    previousActiveElementRef.current = document.activeElement as HTMLElement;

    if (blurPrevious && previousActiveElementRef.current && previousActiveElementRef.current !== document.body) {
      previousActiveElementRef.current.blur();
    }

    const isElementVisible = (element: HTMLElement): boolean => {
      if (!element || !element.isConnected) {
        return false;
      }
      const style = getComputedStyle(element);

      return (
        style.display !== 'none' && style.visibility !== 'hidden' && !element.hidden && element.offsetParent !== null
      );
    };

    const getFocusableElements = (): HTMLElement[] => {
      const selector = 'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])';

      return Array.from(container.querySelectorAll(selector)).filter((el): el is HTMLElement => {
        return !el.hasAttribute('disabled') && isElementVisible(el as HTMLElement);
      });
    };

    const isTopmostTrap = () => {
      const traps = document.querySelectorAll('[data-focus-trap="true"]');

      return traps.length > 0 && traps[traps.length - 1] === container;
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!isTopmostTrap() || event.key !== 'Tab') {
        return;
      }

      const focusable = getFocusableElements();
      if (focusable.length === 0) {
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement;

      if (!container.contains(active) && document.body !== active) {
        event.preventDefault();
        first.focus();
        return;
      }

      if (event.shiftKey && active === first) {
        event.preventDefault();
        last.focus();
        return;
      }

      if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
        return;
      }
    };

    document.addEventListener('keydown', handleKeyDown, true);

    return () => {
      document.removeEventListener('keydown', handleKeyDown, true);

      if (previousActiveElementRef.current && isElementVisible(previousActiveElementRef.current)) {
        previousActiveElementRef.current.focus();
      }
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  return cloneElement(Children.only(children) as ReactElement, {
    ref: containerRef,
    'data-focus-trap': 'true',
    tabIndex: -1,
  });
};

export default FocusTrap;
