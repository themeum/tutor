import { Children, cloneElement, useEffect, useRef, type ReactElement, type ReactNode } from 'react';

const FocusTrap = ({ children }: { children: ReactNode }) => {
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const container = containerRef.current;
    if (!container) {
      return;
    }

    const getFocusableElements = () => {
      const focusableSelectors = 'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])';
      return Array.from(container.querySelectorAll(focusableSelectors)).filter(
        (focusableElement) => !focusableElement.hasAttribute('disabled') && !(focusableElement as HTMLElement).hidden,
      ) as HTMLElement[];
    };

    const handleKeyDown = (event: KeyboardEvent) => {
      // Check if this is the topmost trap
      const allFocusTraps = document.querySelectorAll('[data-focus-trap="true"]');
      const isTopmostFocusTrap =
        allFocusTraps.length > 0 && Array.from(allFocusTraps)[allFocusTraps.length - 1] === container;

      if (!isTopmostFocusTrap || event.key !== 'Tab') {
        return;
      }

      const focusableElements = getFocusableElements();
      if (focusableElements.length === 0) {
        return;
      }

      const firstElement = focusableElements[0];
      const lastElement = focusableElements[focusableElements.length - 1];

      const activeElement = document.activeElement;

      if (!container.contains(activeElement)) {
        event.preventDefault();
        firstElement.focus();
        return;
      }

      if (event.shiftKey && activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
      } else if (!event.shiftKey && activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
      }
    };

    document.addEventListener('keydown', handleKeyDown, true);

    return () => {
      document.removeEventListener('keydown', handleKeyDown, true);
    };
  }, []);

  return cloneElement(Children.only(children) as ReactElement, {
    ref: containerRef,
    'data-focus-trap': 'true',
    tabIndex: -1,
  });
};

export default FocusTrap;
