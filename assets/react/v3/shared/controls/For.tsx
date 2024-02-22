import { ReactNode } from 'react';

type ForProps<T, U extends ReactNode> = {
  each: readonly T[];
  children: (item: T, index: number) => U;
  fallback?: ReactNode;
};

const For = <T, U extends ReactNode>({ each, children, fallback = null }: ForProps<T, U>) => {
  if (each.length === 0) {
    return fallback;
  }

  return each.map((item, index) => {
    return children(item, index);
  });
};

export default For;
