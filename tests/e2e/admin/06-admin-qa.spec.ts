import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Q&A Management @feature', () => {
  test('views Q&A threads and displays question details', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.qna);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText(
      'Question & Answer',
    );

    // Verify seeded question from student exists
    const questionLink = adminPage.locator('text=How can I debug microtasks in the event loop?').first();
    await expect(questionLink).toBeVisible();

    // Click into question details
    await questionLink.click();
    await expect(adminPage).toHaveURL(/question_id=/);
    await expect(adminPage.locator('text=How can I debug microtasks in the event loop?').first()).toBeVisible();
  });
});
