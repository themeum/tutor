import { defineConfig, type Project } from '@playwright/test';
import { config as loadEnv } from 'dotenv';

// The new suite only reads `.env.e2e`. Never fall back to `.env`, which points at a
// developer's manual site.
loadEnv({ path: process.env.E2E_ENV_FILE ?? '.env.e2e' });

const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8888';

const projects: Project[] = [
  {
    name: 'core',
    testIgnore: ['**/external/**'],
  },
  ...(process.env.E2E_EXTERNAL === '1'
    ? [
        {
          name: 'external',
          testDir: './tests/e2e/external',
        },
      ]
    : []),
  ...(process.env.E2E_PRO === '1'
    ? [
        {
          name: 'pro',
          testDir: '../tutor-pro/tests/e2e',
        },
      ]
    : []),
];

export default defineConfig({
  testDir: './tests/e2e',
  outputDir: 'test-results',
  globalSetup: './tests/e2e/global-setup.ts',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: [['list'], ['html', { outputFolder: 'playwright-report', open: 'never' }]],
  timeout: 60_000,
  expect: {
    timeout: 10_000,
  },
  use: {
    baseURL,
    browserName: 'chromium',
    testIdAttribute: 'data-testid',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    ignoreHTTPSErrors: true,
  },
  projects,
});
