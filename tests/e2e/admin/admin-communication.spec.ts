import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Announcements and Q&A Management @feature', () => {
  test('lists course announcements and opens creation modal', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.announcements);
    await expect(adminPage.locator('.tutor-wp-dashboard-header-title, .wrap h1').first()).toContainText(
      'Announcements',
    );

    // Verify seeded announcement is displayed
    await expect(adminPage.locator('text=Welcome to Modern JS Bootcamp').first()).toBeVisible();

    // Trigger Add New Announcement modal
    const addModalBtn = adminPage
      .locator('button[data-tutor-modal-target="tutor_announcement_new"], button:has-text("Add New Announcement")')
      .first();
    await expect(addModalBtn).toBeVisible();
    await addModalBtn.click();

    // Verify modal overlay opens
    const modal = adminPage.locator('#tutor_announcement_new, .tutor-modal.is-active').first();
    await expect(modal).toBeVisible();
  });

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
