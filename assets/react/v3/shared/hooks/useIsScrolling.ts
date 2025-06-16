import { isDefined } from '@TutorShared/utils/types';
import { useEffect, useRef, useState } from 'react';

interface Options {
  defaultValue?: boolean;
}

const defaultOptions = {
  defaultValue: false,
};

export const useIsScrolling = <TRef extends HTMLElement = HTMLDivElement>(options?: Options) => {
  const ref = useRef<TRef>(null);
  const mergedOptions = { ...defaultOptions, ...options };
  const [isScrolling, setIsScrolling] = useState(mergedOptions.defaultValue);

  useEffect(() => {
    if (!isDefined(ref.current)) {
      return;
    }

    if (ref.current.scrollHeight <= ref.current.clientHeight) {
      setIsScrolling(false);
      return;
    }

    const handleScroll = (event: Event) => {
      const element = event.target as HTMLElement;

      if (element.scrollTop + element.clientHeight >= element.scrollHeight) {
        setIsScrolling(false);
        return;
      }

      setIsScrolling(element.scrollTop >= 0);
    };

    ref.current.addEventListener('scroll', handleScroll);

    return () => {
      // eslint-disable-next-line react-hooks/exhaustive-deps
      ref.current?.removeEventListener('scroll', handleScroll);
    };
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [ref.current]);

  return { ref, isScrolling };
};
