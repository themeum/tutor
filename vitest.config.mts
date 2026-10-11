import { fileURLToPath } from 'node:url';

import { defineConfig } from 'vitest/config';

const rootDir = fileURLToPath(new URL('.', import.meta.url));

export default defineConfig({
  resolve: {
    alias: {
      '@Core': `${rootDir}assets/core`,
    },
  },
  test: {
    environment: 'happy-dom',
    include: ['assets/core/ts/**/*.test.ts'],
  },
});
