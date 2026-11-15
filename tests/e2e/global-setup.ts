import { mkdirSync } from 'node:fs';

import { chromium } from '@playwright/test';

import { apiLogin, authDir, storageState } from './support/auth';
import { baseURL, roles } from './support/env';

export default async function globalSetup() {
  mkdirSync(authDir, { recursive: true });
  const browser = await chromium.launch();

  try {
    for (const role of roles) {
      const context = await browser.newContext({
        baseURL,
        ignoreHTTPSErrors: true,
        extraHTTPHeaders: { 'X-WP-Test-Env': 'playwright' },
      });
      await apiLogin(context, role);
      await context.storageState({ path: storageState(role) });
      await context.close();
    }
  } finally {
    await browser.close();
  }
}
