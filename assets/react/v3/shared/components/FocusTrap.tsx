import { Children, cloneElement, useEffect, useRef, type ReactElement, type ReactNode } from 'react';

const FocusTrap = ({ children }: { children: ReactNode }) => {
  const containerRef = useRef<HTMLDivElement>(null);
  const previousActiveElementRef = useRef<HTMLElement | null>(null);
  const focusedHistoryRef = useRef<HTMLElement[]>([]);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) return;

    previousActiveElementRef.current = document.activeElement as HTMLElement;

    const isElementVisible = (element: HTMLElement): boolean => {
      if (!element || !element.isConnected) return false;
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

    const tryFocusElement = (focusable: HTMLElement[]) => {
      const history = [...focusedHistoryRef.current];
      let validElement: HTMLElement | null = null;

      // Find last valid element in focus history
      for (let i = history.length - 1; i >= 0; i--) {
        const el = history[i];
        if (container.contains(el) && isElementVisible(el)) {
          validElement = el;
          focusedHistoryRef.current = history.slice(0, i + 1);
          break;
        }
      }

      if (validElement) {
        validElement.focus();
      } else if (focusable.length > 0) {
        focusable[0].focus();
      } else {
        container.focus();
      }
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      if (!isTopmostTrap() || event.key !== 'Tab') return;

      const focusable = getFocusableElements();
      if (focusable.length === 0) return;

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement;

      if (!container.contains(active)) {
        event.preventDefault();
        first.focus();
        return;
      }

      if (event.shiftKey && active === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    };

    const handleFocusIn = (event: FocusEvent) => {
      if (!isTopmostTrap()) return;

      const target = event.target as HTMLElement;
      const isValidFocus = container.contains(target) && isElementVisible(target);

      if (isValidFocus) {
        const history = focusedHistoryRef.current;
        if (history.length === 0 || history[history.length - 1] !== target) {
          focusedHistoryRef.current = [...history, target];
        }
        return;
      }

      event.preventDefault();
      tryFocusElement(getFocusableElements());
    };

    const handleFocusOut = (event: FocusEvent) => {
      if (!isTopmostTrap()) return;

      const target = event.relatedTarget as HTMLElement;
      if (container.contains(target)) return;

      const focusable = getFocusableElements();
      if (focusable.length === 0) return;

      for (const eachHistory of focusedHistoryRef.current) {
        if (eachHistory === target) return;
        tryFocusElement(focusable);
      }
    };

    document.addEventListener('keydown', handleKeyDown, true);
    document.addEventListener('focusin', handleFocusIn, true);
    document.addEventListener('focusout', handleFocusOut, true);

    return () => {
      document.removeEventListener('keydown', handleKeyDown, true);
      document.removeEventListener('focusin', handleFocusIn, true);
      document.removeEventListener('focusout', handleFocusOut, true);

      if (previousActiveElementRef.current && isElementVisible(previousActiveElementRef.current)) {
        previousActiveElementRef.current.focus();
      }
    };
  }, []);

  return cloneElement(Children.only(children) as ReactElement, {
    ref: containerRef,
    'data-focus-trap': 'true',
    tabIndex: -1,
  });
};

export default FocusTrap;
