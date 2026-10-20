import { assertNoPhpErrors, clearAdminSearch, searchAdminTable } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Course Marketplace & Instructors @feature', () => {
  test('manages instructor lists, views pending tab, and opens creation modal', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.instructors);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText(/Instructor/i);
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText('instructor');

    // Check pending instructor tab
    const pendingTab = adminPage.locator('a[data-tab="pending"], a[href*="data=pending"]').first();
    if (await pendingTab.isVisible()) {
      await pendingTab.click();
      await adminPage.waitForLoadState('domcontentloaded');
      await expect(adminPage.locator('table.tutor-table')).toContainText(/Pending|instructor_pending/i);
    }

    // Open add instructor modal
    const addModal = adminPage.locator('#tutor-instructor-add-new');
    await adminPage
      .locator(
        'button[data-tutor-modal-target="tutor-instructor-add-new"], a[data-tutor-modal-target="tutor-instructor-add-new"]',
      )
      .first()
      .click();
    await expect(addModal).toBeVisible();
    await assertNoPhpErrors(adminPage);
  });

  test('views withdrawal requests table, verifies amounts, and handles search edge case', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.withdrawRequests);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText(/Withdraw/i);
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText('75.00');

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_withdrawal_id__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found|No Record Found/i);

    // Clear search
    await clearAdminSearch(adminPage);
    await expect(table).toContainText('75.00');
    await assertNoPhpErrors(adminPage);
  });
});
