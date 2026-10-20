import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Course Marketplace & Instructors @feature', () => {
  test('manages instructor lists and pending applicant approvals', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.instructors);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText(/Instructor/i);

    // Verify approved instructor is listed
    await expect(adminPage.locator('text=instructor').first()).toBeVisible();

    // Check pending instructor tab or list
    const pendingTab = adminPage.locator('a:has-text("Pending"), a[href*="data=pending"]').first();
    if (await pendingTab.isVisible()) {
      await pendingTab.click();
      await expect(adminPage.locator('text=Pending Applicant, text=instructor_pending').first()).toBeVisible();
    }
  });

  test('views withdrawal requests table', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.withdrawRequests);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText(/Withdraw/i);

    // Verify pending withdrawal row exists
    const withdrawRow = adminPage.locator('.tutor-table tr, tbody tr').filter({ hasText: '75.00' }).first();
    await expect(withdrawRow).toBeVisible();
  });
});
