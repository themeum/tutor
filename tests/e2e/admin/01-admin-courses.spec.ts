import {
  applyAdminFilter,
  assertNoPhpErrors,
  clearAdminSearch,
  searchAdminTable,
  toggleAdminSortOrder,
} from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Course Management @feature', () => {
  test('lists seeded courses with dashboard header and action button', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.courses);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText(/Courses/i);
    await expect(adminPage.locator('table.tutor-table')).toContainText(/Mastering Modern JavaScript/i);
    await expect(
      adminPage.locator('a.tutor-create-new-course, .tutor-wp-dashboard-header-inner a.tutor-btn'),
    ).toBeVisible();
    await assertNoPhpErrors(adminPage);
  });

  test('filters courses by search query, handles empty search edge case, and restores on clear', async ({
    adminPage,
  }) => {
    await adminPage.goto(backendUrls.courses);

    // Positive search query
    await searchAdminTable(adminPage, 'JavaScript');
    await expect(adminPage.locator('table.tutor-table')).toContainText(/Mastering Modern JavaScript/i);

    // Negative search edge case (non-existent item)
    await searchAdminTable(adminPage, '__non_existent_course_xyz__');
    await expect(adminPage.locator('body')).toContainText(/No Courses Found\./i);

    // Clear search and verify restoration of original table rows
    await clearAdminSearch(adminPage);
    await expect(adminPage.locator('table.tutor-table')).toContainText(/Mastering Modern JavaScript/i);
    await assertNoPhpErrors(adminPage);
  });

  test('applies list filter popup and toggles sort order', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.courses);

    // Filter via popup filter form for draft
    await applyAdminFilter(adminPage, { data: 'draft' });
    await expect(adminPage).toHaveURL(/data=draft/);
    await expect(adminPage.locator('table.tutor-table')).toContainText(/Draft UI\/UX Prototyping Essentials/i);

    // Filter via popup filter form for published
    await applyAdminFilter(adminPage, { data: 'published' });
    await expect(adminPage).toHaveURL(/data=published/);
    await expect(adminPage.locator('table.tutor-table')).toContainText(/Mastering Modern JavaScript/i);

    // Toggle sort order
    await toggleAdminSortOrder(adminPage);
    await expect(adminPage).toHaveURL(/order=/);
    await assertNoPhpErrors(adminPage);
  });

  test('navigates to course builder without PHP errors', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.courses);

    await adminPage.locator('a.tutor-create-new-course, .tutor-wp-dashboard-header-inner a.tutor-btn').first().click();
    await adminPage.waitForLoadState('domcontentloaded');

    await expect(adminPage).toHaveURL(/page=create-course|post_type=courses/);
    await assertNoPhpErrors(adminPage);
  });
});
