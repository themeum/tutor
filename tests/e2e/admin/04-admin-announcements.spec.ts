import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Announcements Management @feature', () => {
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
});
