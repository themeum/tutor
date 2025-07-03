import { useCallback, useEffect, useRef, useState } from 'react';

interface UseInViewReturn {
  ref: React.RefObject<HTMLDivElement>;
  isInView: boolean;
}

const useInView = (): UseInViewReturn => {
  const elementRef = useRef<HTMLDivElement | null>(null);
  const [isElementInView, setIsElementInView] = useState<boolean>(false);

  const handleIntersectionChange = useCallback((entries: IntersectionObserverEntry[]) => {
    const [entry] = entries;
    setIsElementInView(entry.isIntersecting);
  }, []);

  useEffect(() => {
    const currentElement = elementRef.current;

    if (!currentElement) {
      return;
    }

    const observerOptions: IntersectionObserverInit = {
      threshold: 0,
      rootMargin: '0px',
    };

    const intersectionObserver = new IntersectionObserver(handleIntersectionChange, observerOptions);

    intersectionObserver.observe(currentElement);

    return () => {
      intersectionObserver.disconnect();
    };
  }, [handleIntersectionChange]);

  return {
    ref: elementRef,
    isInView: isElementInView,
  };
};

export default useInView;
