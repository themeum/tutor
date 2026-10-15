import type { ReactNode } from 'react';
import createCache from '@emotion/cache';
import { CacheProvider } from '@emotion/react';
import rtlPlugin from 'stylis-plugin-rtl';

import { isRTL } from '@TutorShared/config/constants';

const cache = createCache({
  stylisPlugins: [rtlPlugin],
  key: 'rtl',
});

const RTLProvider = ({ children }: { children: ReactNode }) => {
  if (isRTL) {
    return <CacheProvider value={cache}>{children}</CacheProvider>;
  }

  return <>{children}</>;
};

export default RTLProvider;
