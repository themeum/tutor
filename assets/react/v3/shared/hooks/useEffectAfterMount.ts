import { type DependencyList, type EffectCallback, useEffect, useRef } from 'react';

export const useEffectAfterMount = (cb: EffectCallback, dependencies: DependencyList | undefined) => {
  const mounted = useRef(true);

  useEffect(() => {
    if (!mounted.current) {
      return cb();
    }
    mounted.current = false;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, dependencies);
};
