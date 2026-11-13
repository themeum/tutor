import { expect } from '@playwright/test';

import { createCourse } from '../support/factories';
import { test } from '../support/test';
import { backendUrls } from '../support/urls';

test('admin searches the course list by title @feature', async ({ adminPage: page }) => {
  const stamp = Date.now();
  const course = createCourse({ title: `E2E Searchable Course ${stamp}` });

  await page.goto(backendUrls.tutorDashboard);
  const search = page.locator('#tutor-backend-filter-search');

  await search.fill(course.title);
  await search.press('Enter');
  await expect(page).toHaveURL(/search=/);
  await expect(page.locator('a.tutor-table-link').filter({ hasText: course.title }).first()).toBeVisible();

  await search.fill(`no-such-course-${stamp}`);
  await search.press('Enter');
  await expect(page).toHaveURL(new RegExp(`search=no-such-course-${stamp}`));
  await expect(page.locator('a.tutor-table-link').filter({ hasText: course.title })).toHaveCount(0);
});
