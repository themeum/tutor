import { expect, test } from '@playwright/test';

import { login } from '../support/auth';
import { backendUrls } from '../support/urls';

test('admin can log in and open the Tutor course list @smoke', async ({ page }) => {
  await login(page, 'admin');

  await expect(page.locator('#wpadminbar')).toBeVisible();

  await page.goto(backendUrls.tutorDashboard);
  await expect(page.locator('.tutor-wp-dashboard-header-title')).toContainText('Courses');
  await expect(page.locator('a.tutor-create-new-course, button:has-text("Create Course")').first()).toBeVisible();
});
