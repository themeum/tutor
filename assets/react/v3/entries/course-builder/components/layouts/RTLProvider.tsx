import React from 'react';
import { isRTL } from "@Config/constants";
import createCache from "@emotion/cache";
import { CacheProvider } from "@emotion/react";
import { ReactNode } from "react";
import rtlPlugin from "stylis-plugin-rtl";

const cache = createCache({
  stylisPlugins: [rtlPlugin],
  key: "rtl",
});

const RTLProvider = ({ children }: { children: ReactNode }) => {
  if (isRTL) {
    return <CacheProvider value={cache}>{children}</CacheProvider>;
  }

  return <>{children}</>;
};

export default RTLProvider;
