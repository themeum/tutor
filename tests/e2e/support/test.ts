import { closeSync, existsSync, openSync, readSync, statSync } from 'node:fs';
import path from 'node:path';

import { type Browser, expect, type Locator, type Page, test as base } from '@playwright/test';

import { CourseBuilderPage } from '../pages/CourseBuilderPage';
import { storageState } from './auth';
import { baseURL, type Role } from './env';

const wpRoot = path.resolve(process.env.WP_ROOT ?? path.resolve(__dirname, '../../../../..'));
const debugLog = path.join(wpRoot, 'wp-content/debug.log');

const logSize = () => (existsSync(debugLog) ? statSync(debugLog).size : 0);

function readLogSince(offset: number): string {
  if (!existsSync(debugLog)) return '';
  const size = statSync(debugLog).size;
  if (size <= offset) return '';
  const fd = openSync(debugLog, 'r');
  try {
    const buf = Buffer.alloc(size - offset);
    readSync(fd, buf, 0, buf.length, offset);
    return buf.toString('utf8');
  } finally {
    closeSync(fd);
  }
}

const IGNORED_5XX = [/\/wp-cron\.php/, /heartbeat/];

function watch(page: Page): string[] {
  const problems: string[] = [];
  page.on('pageerror', (e) => problems.push(`pageerror: ${e.message}`));
  page.on('response', (r) => {
    const url = r.url();
    if (!url.startsWith(baseURL) || IGNORED_5XX.some((re) => re.test(url))) return;
    if (r.status() >= 500) problems.push(`${r.status()} ${r.request().method()} ${url}`);
  });
  return problems;
}

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
    const problems = watch(page);
    const logOffset = logSize();

    await use(page);

    await context.close();
    const fatals = readLogSince(logOffset)
      .split('\n')
      .filter((l) => /PHP (Fatal|Parse) error|Uncaught (Error|Exception)/.test(l));

    expect.soft(problems, 'browser/server errors').toEqual([]);
    expect.soft(fatals, 'new PHP fatals in debug.log').toEqual([]);
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
