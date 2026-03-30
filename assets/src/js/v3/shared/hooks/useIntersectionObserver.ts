import { isDefined } from '@TutorShared/utils/types';
import { useEffect, useRef, useState, type DependencyList } from 'react';

type Args = IntersectionObserverInit & {
  freezeOnceVisible?: boolean;
  dependencies?: DependencyList;
};

const useIntersectionObserver = <T extends HTMLElement>({
  threshold = 0,
  root = null,
  rootMargin = '0%',
  freezeOnceVisible = false,
  dependencies = [],
}: Args = {}) => {
  const intersectionElementRef = useRef<T>(null);
  const [intersectionEntry, setIntersectionEntry] = useState<IntersectionObserverEntry>();

  const frozen = intersectionEntry?.isIntersecting && freezeOnceVisible;

  const updateEntry = ([entry]: IntersectionObserverEntry[]): void => {
    setIntersectionEntry(entry);
  };

  useEffect(() => {
    const node = intersectionElementRef.current;
    const hasIOSupport = isDefined(window.IntersectionObserver);

    if (!hasIOSupport || frozen || !node) {
      if (!node) {
        setIntersectionEntry(undefined);
      }
      return;
    }

    const observerParams = { threshold, root, rootMargin };
    const observer = new IntersectionObserver(updateEntry, observerParams);

    observer.observe(node);

    return () => observer.disconnect();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [intersectionElementRef.current, threshold, root, rootMargin, frozen, ...dependencies]);

  return { intersectionEntry, intersectionElementRef };
};

export default useIntersectionObserver;
