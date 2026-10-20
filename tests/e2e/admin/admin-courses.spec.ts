import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Course Management @feature', () => {
  test('lists courses with status filtering, search, and navigation to builder', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.courses);

    // Verify course header & Create Course button
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText('Courses');
    const createBtn = adminPage
      .locator('.tutor-wp-dashboard-header-inner a, .tutor-wp-dashboard-header-inner button')
      .filter({ hasText: /Course/i })
      .first();
    await expect(createBtn).toBeVisible();

    // Verify table displays seeded course
    await expect(adminPage.locator('text=Mastering Modern JavaScript').first()).toBeVisible();

    // Search courses
    const searchInput = adminPage.locator('input[name="search"], input[type="search"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('Modern JavaScript');
      await searchInput.press('Enter');
      await expect(adminPage.locator('text=Mastering Modern JavaScript').first()).toBeVisible();
    }

    // Filter by status tabs if available
    const draftTab = adminPage.locator('a:has-text("Draft"), a[data-status="draft"], a[href*="data=draft"]').first();
    if (await draftTab.isVisible()) {
      await draftTab.click();
      await expect(adminPage.locator('text=Draft UI/UX Prototyping Essentials').first()).toBeVisible();
    }

    // Verify Course Builder loads when navigating
    await adminPage.goto(backendUrls.courseBuilder);
    await expect(adminPage).toHaveURL(/page=create-course/);
    await expect(adminPage.locator('body')).not.toContainText('Fatal error');
  });
});
