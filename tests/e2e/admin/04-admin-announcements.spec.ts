import { assertNoPhpErrors, clearAdminSearch, searchAdminTable, toggleAdminSortOrder } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Announcements Management @feature', () => {
  test('lists course announcements, filters with search, and handles empty state', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.announcements);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Announcements');
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText('Welcome to Modern JS Bootcamp');

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_announcement_query__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found|No Record Found/i);

    // Clear search
    await clearAdminSearch(adminPage);
    await expect(table).toContainText('Welcome to Modern JS Bootcamp');
    await assertNoPhpErrors(adminPage);
  });

  test('toggles sort order and asserts announcements list filters', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.announcements);

    // Toggle sort order
    await toggleAdminSortOrder(adminPage);
    await expect(adminPage).toHaveURL(/order=/);
    await expect(adminPage.locator('table.tutor-table')).toBeVisible();

    // Verify filter popup button opens filter controls
    const filterBtn = adminPage.locator('.tutor-wp-dashboard-filters-button');
    if (await filterBtn.isVisible()) {
      await filterBtn.click();
      await expect(adminPage.locator('.tutor-admin-dashboard-filter-form')).toBeVisible();
    }
    await assertNoPhpErrors(adminPage);
  });

  test('opens creation modal, asserts input controls, and handles modal dismissal', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.announcements);

    // Open modal
    await adminPage
      .locator(
        'a[data-tutor-modal-target="tutor_announcement_new"], button[data-tutor-modal-target="tutor_announcement_new"]',
      )
      .click();
    const modal = adminPage.locator('#tutor_announcement_new');
    await expect(modal).toBeVisible();
    await expect(modal.locator('input[name="tutor_announcement_title"]')).toBeVisible();
    await expect(modal.locator('textarea[name="tutor_announcement_summary"]')).toBeVisible();
    await expect(modal.locator('button[type="submit"]')).toBeVisible();

    // Close modal edge case
    await modal.locator('.tutor-modal-close').click();
    await expect(modal).toBeHidden();
    await assertNoPhpErrors(adminPage);
  });
});
