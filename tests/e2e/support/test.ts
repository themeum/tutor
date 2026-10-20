import { type Browser, expect, type Page, test as base } from '@playwright/test';

import { storageState } from './auth';
import { baseURL, type Role } from './env';

type RoleFixtures = {
  adminPage: Page;
  instructorPage: Page;
  studentPage: Page;
  student2Page: Page;
};

const rolePage =
  (role: Role) =>
  async ({ browser }: { browser: Browser }, use: (page: Page) => Promise<void>) => {
    const context = await browser.newContext({
      baseURL,
      storageState: storageState(role),
    });
    const page = await context.newPage();

    await use(page);
    await context.close();
  };

export const test = base.extend<RoleFixtures>({
  adminPage: rolePage('admin'),
  instructorPage: rolePage('instructor'),
  studentPage: rolePage('student'),
  student2Page: rolePage('student2'),
});

export { expect };
