import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Quiz Attempts Management @feature', () => {
  test('lists quiz attempts with pass/fail badges and navigation', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.quizAttempts);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText(
      'Quiz Attempts',
    );

    // Verify seeded quiz attempt row
    await expect(adminPage.locator('text=JavaScript Engine Evaluation Quiz').first()).toBeVisible();

    // Verify student display name and result badge
    const attemptRow = adminPage.locator('.tutor-table tr, tbody tr').filter({ hasText: 'student' }).first();
    await expect(attemptRow).toBeVisible();
    await expect(attemptRow).toContainText(/pass/i);
  });
});
