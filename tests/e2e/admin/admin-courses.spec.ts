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
    await expect(
      adminPage.locator('.tutor-table, tbody').getByText('Mastering Modern JavaScript').first(),
    ).toBeVisible();

    // Test search filter
    await adminPage.goto(`${backendUrls.courses}&search=Modern+JavaScript`);
    await expect(
      adminPage.locator('.tutor-table, tbody').getByText('Mastering Modern JavaScript').first(),
    ).toBeVisible();

    // Test status tab filter (Draft)
    await adminPage.goto(`${backendUrls.courses}&data=draft`);
    await expect(
      adminPage.locator('.tutor-table, tbody').getByText('Draft UI/UX Prototyping Essentials').first(),
    ).toBeVisible();

    // Verify Course Builder loads when navigating
    await adminPage.goto(backendUrls.courseBuilder);
    await expect(adminPage).toHaveURL(/page=create-course/);
    await expect(adminPage.locator('body')).not.toContainText('Fatal error');
  });
});
