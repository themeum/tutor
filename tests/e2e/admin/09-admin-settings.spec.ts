import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Settings Management @feature', () => {
  test('navigates settings tabs and saves configuration', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.settings);
    await expect(
      adminPage.locator('.tutor-admin-wrap, .tutor-option-wrapper, #tutor-option-form').first(),
    ).toBeVisible();

    // Verify main settings navigation tabs
    await expect(adminPage.locator('text=General').first()).toBeVisible();
    await expect(adminPage.locator('text=Course').first()).toBeVisible();
    await expect(adminPage.locator('text=Monetization').first()).toBeVisible();

    // Switch to Course Settings tab
    const courseTab = adminPage
      .locator('.tutor-option-tabs a[data-tab="course"], .tutor-nav-link:has-text("Course")')
      .first();
    if (await courseTab.isVisible()) {
      await courseTab.click();
      await expect(adminPage.locator('body')).not.toContainText('Fatal error');
    }

    // Verify Save Changes button exists in header
    const saveBtn = adminPage.locator('#save_tutor_option');
    await expect(saveBtn).toBeAttached();

    // Toggle a checkbox to enable save button and save
    const firstCheckbox = adminPage.locator('#tutor-option-form input[type="checkbox"]').first();
    if (await firstCheckbox.isVisible()) {
      await firstCheckbox.click();
      await expect(saveBtn).toBeEnabled();
      await saveBtn.click();
      // Verify toast / notification or saved indicator appears
      await expect(
        adminPage.locator('.tutor-toast, .tutor-alert, .tutor-notification, text=saved, text=Success').first(),
      ).toBeVisible();
    }
  });
});
