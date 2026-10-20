import { assertNoPhpErrors, clearAdminSearch, searchAdminTable, toggleAdminSortOrder } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Orders and Students Management @feature', () => {
  test('views orders table and asserts order details', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.orders);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Orders');
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText(/student|E2E Student/i);
    await expect(table).toContainText(/completed|incomplete|unpaid|\$49/i);
    await assertNoPhpErrors(adminPage);
  });

  test('lists enrolled students with search, handles empty search edge case, and restores', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.students);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Students');
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText(/student|E2E Student/i);

    // Positive search
    await searchAdminTable(adminPage, 'Student');
    await expect(table).toContainText(/student|E2E Student/i);

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_student_xyz__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found|No Record Found|No Data Available/i);

    // Clear search restores list
    await clearAdminSearch(adminPage);
    await expect(table).toContainText(/student|E2E Student/i);
    await assertNoPhpErrors(adminPage);
  });

  test('applies list filters and toggles sort order on students list', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.students);

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
});
