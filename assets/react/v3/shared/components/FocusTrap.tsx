import { Children, cloneElement, useEffect, useRef, type ReactNode } from 'react';

interface FocusTrapProps {
  children: ReactNode;
}

const FocusTrap = ({ children }: FocusTrapProps) => {
  const containerRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const allFocusable = () =>
      containerRef.current?.querySelectorAll<HTMLElement>(
        'a[href], button, textarea, input, select, [tabindex]:not([tabindex="-1"])',
      ) || [];

    const focusableElements = () => Array.from(allFocusable()).filter((el) => !el.hasAttribute('disabled'));

    const handleTab = (event: KeyboardEvent) => {
      const elements = Array.from(focusableElements());
      const firstElement = elements[0];
      const lastElement = elements[elements.length - 1];

      if (event.key === 'Tab' && elements.length) {
        if (event.shiftKey && document.activeElement === firstElement) {
          event.preventDefault();
          lastElement?.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
          event.preventDefault();
          firstElement?.focus();
        }
      }
    };

    const handleFocusOut = (event: FocusEvent) => {
      const elements = focusableElements();
      if (!containerRef.current?.contains(event.target as Node)) {
        elements[0]?.focus();
      }
    };

    document.addEventListener('keydown', handleTab);
    document.addEventListener('focusin', handleFocusOut);

    return () => {
      document.removeEventListener('keydown', handleTab);
      document.removeEventListener('focusin', handleFocusOut);
    };
  }, []);

  return cloneElement(Children.only(children) as React.ReactElement, { ref: containerRef });
};

export default FocusTrap;
