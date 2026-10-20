import { assertNoPhpErrors, clearAdminSearch, searchAdminTable } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Quiz Attempts Management @feature', () => {
  test('lists quiz attempts with pass/fail badges, student names, and handles empty search', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.quizAttempts);

    await expect(adminPage.locator('.tutor-wp-dashboard-header-title')).toContainText('Quiz Attempts');
    const table = adminPage.locator('table.tutor-table');
    await expect(table).toContainText('JavaScript Engine Evaluation Quiz');
    await expect(table).toContainText(/pass/i);
    await expect(table).toContainText(/student|E2E Student/i);

    // Negative search edge case
    await searchAdminTable(adminPage, '__non_existent_quiz_attempt__');
    await expect(adminPage.locator('body')).toContainText(/No Data Found|No Record Found/i);

    // Clear search
    await clearAdminSearch(adminPage);
    await expect(table).toContainText('JavaScript Engine Evaluation Quiz');
    await assertNoPhpErrors(adminPage);
  });
});
