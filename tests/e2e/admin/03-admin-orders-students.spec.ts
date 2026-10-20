import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Orders and Students Management @feature', () => {
  test('views orders table and asserts order details', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.orders);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText('Orders');

    // Verify seeded order row exists with amount or student name
    const orderRow = adminPage.locator('.tutor-table tr, .wp-list-table tr').filter({ hasText: 'student' }).first();
    await expect(orderRow).toBeVisible();
    await expect(orderRow).toContainText(/completed|incomplete|unpaid|\$49/i);
  });

  test('lists enrolled students with search', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.students);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText('Students');

    // Verify student is listed
    await expect(adminPage.locator('.tutor-table tr, tbody tr').filter({ hasText: 'student' }).first()).toBeVisible();

    // Search student
    const searchInput = adminPage.locator('input[name="search"], input[type="search"]').first();
    if (await searchInput.isVisible()) {
      await searchInput.fill('student');
      await searchInput.press('Enter');
      await expect(adminPage.locator('.tutor-table tr, tbody tr').filter({ hasText: 'student' }).first()).toBeVisible();
    }
  });
});
