import { type Browser, expect, type Locator, type Page, test as base } from '@playwright/test';

import { CourseBuilderPage } from '../pages/CourseBuilderPage';
import { storageState } from './auth';
import { baseURL, type Role } from './env';

type RoleFixtures = {
  adminPage: Page;
  instructorPage: Page;
  studentPage: Page;
  student2Page: Page;
  managerPage: Page;
  guestPage: Page;
};

type AppFixtures = RoleFixtures & {
  courseBuilder: CourseBuilderPage;
};

const rolePage =
  (role?: Role) =>
  async ({ browser }: { browser: Browser }, use: (page: Page) => Promise<void>) => {
    const context = await browser.newContext({
      baseURL,
      ignoreHTTPSErrors: true,
      extraHTTPHeaders: { 'X-WP-Test-Env': 'playwright' },
      ...(role ? { storageState: storageState(role) } : {}),
    });
    const page = await context.newPage();

    await use(page);
    try {
      await page.close();
      await context.close();
    } catch {
      // Ignore context close artifacts cleanup race condition
    }
  };

export const test = base.extend<AppFixtures>({
  adminPage: rolePage('admin'),
  instructorPage: rolePage('instructor'),
  studentPage: rolePage('student'),
  student2Page: rolePage('student2'),
  managerPage: rolePage('manager'),
  guestPage: rolePage(),
  courseBuilder: async ({ instructorPage }, use) => {
    const builder = new CourseBuilderPage(instructorPage);
    await use(builder);
  },
});

export { CourseBuilderPage, expect };
export type { Browser, Locator, Page };
