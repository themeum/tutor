import { existsSync } from 'node:fs';
import { resolve } from 'node:path';

import { defineConfig, type Project } from '@playwright/test';
import { config as loadEnv } from 'dotenv';

// The new suite only reads `.env.e2e`. Never fall back to `.env`, which points at a
// developer's manual site.
loadEnv({ path: process.env.E2E_ENV_FILE ?? '.env.e2e' });

const baseURL = process.env.E2E_BASE_URL ?? 'http://localhost:8888';

const proTestDir = resolve(__dirname, '../tutor-pro/tests/e2e');
const externalTestDir = resolve(__dirname, './tests/e2e/external');

const hasExternal = existsSync(externalTestDir) && process.env.E2E_EXTERNAL !== '0';
const hasPro = existsSync(proTestDir) && process.env.E2E_PRO !== '0';

const projects: Project[] = [
  {
    name: 'core',
    testIgnore: ['**/external/**'],
  },
  ...(hasExternal
    ? [
        {
          name: 'external',
          testDir: './tests/e2e/external',
        },
      ]
    : []),
  ...(hasPro
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
