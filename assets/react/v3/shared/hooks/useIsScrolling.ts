import { isDefined } from '@Utils/types';
import { useEffect, useRef, useState } from 'react';

export const useIsScrolling = <TRef extends HTMLElement>() => {
  const ref = useRef<TRef>(null);
  const [isScrolling, setIsScrolling] = useState(false);

  useEffect(() => {
    if (!isDefined(ref.current)) {
      return;
    }

    const handleScroll = (event: any) => {
      const top = event.target?.scrollTop ?? 0;
      setIsScrolling(top > 0);
    };

    ref.current.addEventListener('scroll', handleScroll);

    return () => {
      ref.current?.removeEventListener('scroll', handleScroll);
    };
  }, [ref.current]);

  return { ref, isScrolling };
};
