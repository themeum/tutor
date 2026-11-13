import { existsSync } from 'node:fs';
import { resolve } from 'node:path';

import { defineConfig, type Project } from '@playwright/test';
import { config as loadEnv } from 'dotenv';

loadEnv({ path: process.env.E2E_ENV_FILE ?? resolve(__dirname, '../../.env') });

const baseURL = process.env.E2E_BASE_URL ?? process.env.SITE_URL ?? 'http://localhost:8888';

const proTestDir = resolve(__dirname, '../../../tutor-pro/tests/e2e');
const externalTestDir = resolve(__dirname, './external');

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
          testDir: './external',
        },
      ]
    : []),
  ...(hasPro
    ? [
        {
          name: 'pro',
          testDir: proTestDir,
        },
      ]
    : []),
];

export default defineConfig({
  testDir: '.',
  outputDir: resolve(__dirname, '../../test-results'),
  globalSetup: './global-setup.ts',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: [['list'], ['html', { outputFolder: resolve(__dirname, '../../playwright-report'), open: 'never' }]],
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
    extraHTTPHeaders: {
      'X-WP-Test-Env': 'playwright',
    },
  },
  // Start the local test server when one is not already running. CI starts it manually.
  webServer: {
    command: 'php bin/e2e/serve.php',
    cwd: resolve(__dirname, '../..'),
    url: baseURL,
    reuseExistingServer: true,
    timeout: 120_000,
    stdout: 'ignore',
  },
  projects,
});
