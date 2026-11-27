import { assertNoPhpErrors, clearAdminSearch, searchAdminTable, toggleAdminSortOrder } from '@Test/e2e/support/admin';
import { expect, test } from '@Test/e2e/support/test';
import { backendUrls } from '@Test/e2e/support/urls';

test.describe('Admin Orders and Students Management @feature', () => {
  test('views orders table and asserts order details', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.orders);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Orders');
    const table = adminPage.locator('table.tutor-table');
    const orderRow = table
      .locator('tbody tr')
      .filter({ has: adminPage.getByRole('cell', { name: /E2E Student$/ }) })
      .first();
    await expect(orderRow).toBeVisible();
    await expect(orderRow).toContainText(/completed/i);
    await expect(orderRow).toContainText(/\$49\.99/);
    await assertNoPhpErrors(adminPage);
  });

  test('lists enrolled students with search, handles empty search edge case, and restores', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.students);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Students');
    const table = adminPage.locator('table.tutor-table');
    const studentRow = table
      .locator('tbody tr')
      .filter({ has: adminPage.getByRole('cell', { name: /E2E Student$/ }) })
      .first();
    await expect(studentRow).toBeVisible();

    // Positive search
    await searchAdminTable(adminPage, 'Student');
    await expect(studentRow).toBeVisible();

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_student_xyz__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found|No Record Found|No Data Available/i);

    // Clear search restores list
    await clearAdminSearch(adminPage);
    await expect(studentRow).toBeVisible();
    await assertNoPhpErrors(adminPage);
  });

  test('applies list filters and toggles sort order on students list', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.students);

    // Toggle sort order
    await toggleAdminSortOrder(adminPage);
    await expect(adminPage).toHaveURL(/order=/);
    await expect(adminPage.locator('table.tutor-table')).toBeVisible();

    // Verify filter popup button opens filter controls
    const filterBtn = adminPage.locator('.tutor-wp-dashboard-filters-button').first();
    await expect(filterBtn).toBeVisible();
    await filterBtn.click();
    await expect(adminPage.locator('.tutor-admin-dashboard-filter-form')).toBeVisible();
    await assertNoPhpErrors(adminPage);
  });
});
