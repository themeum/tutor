import { assertNoPhpErrors, clearAdminSearch, searchAdminTable } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Q&A Management @feature', () => {
  test('views Q&A threads, searches questions, and navigates to question details', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.qna);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText(/Question & Answer|Q&A/i);
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText(/How can I debug microtasks/i);

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_question_query__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found\./i);

    // Clear search and restore
    await clearAdminSearch(adminPage);
    await expect(table).toContainText(/How can I debug microtasks/i);

    // Click into single question thread
    await adminPage.locator('table.tutor-table tbody tr a:has-text("How can I debug microtasks")').first().click();
    await adminPage.waitForLoadState('domcontentloaded');

    await expect(adminPage).toHaveURL(/question_id=/);
    await expect(adminPage.locator('body')).toContainText('How can I debug microtasks in the event loop?');
    await expect(adminPage.locator('.tutor-qna-reply-editor, form[action*="tutor_reply"]')).toBeVisible();
    await expect(adminPage.locator('a.tutor-btn:has-text("Back")')).toBeVisible();
    await assertNoPhpErrors(adminPage);
  });
});
