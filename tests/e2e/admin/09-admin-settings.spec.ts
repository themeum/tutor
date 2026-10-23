import { assertNoPhpErrors } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Settings Management @feature', () => {
  test('navigates settings tabs and verifies tab content', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.settings);

    const form = adminPage.locator('#tutor-option-form, form[action*="tutor_option"]');
    await expect(form).toBeVisible();
    const navTabs = adminPage.locator('.tutor-backend-settings-nav a, .tutor-nav a');
    await expect(navTabs.filter({ hasText: 'General' }).first()).toBeVisible();
    await expect(navTabs.filter({ hasText: 'Course' }).first()).toBeVisible();
    await expect(navTabs.filter({ hasText: 'Monetization' }).first()).toBeVisible();

    // Switch to Course Settings tab
    const courseTab = navTabs.filter({ hasText: 'Course' }).first();
    await courseTab.click();
    await adminPage.waitForLoadState('domcontentloaded');
    await assertNoPhpErrors(adminPage);
  });

  test('toggles configuration and saves settings with feedback confirmation', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.settings);

    const saveButton = adminPage.locator('#save_tutor_option, button[name="tutor_option_save_btn"]');
    await expect(saveButton).toBeAttached();

    // Toggle a setting checkbox to trigger form modification
    const firstCheckbox = adminPage
      .locator('#tutor-option-form, form[action*="tutor_option"]')
      .locator('input[type="checkbox"]')
      .first();
    if (await firstCheckbox.isVisible()) {
      await firstCheckbox.click();
      await expect(saveButton).toBeEnabled();

      // Save changes
      await saveButton.click();
      await expect(adminPage.locator('.tutor-toast, .tutor-alert, .notice-success')).toBeVisible({
        timeout: 10000,
      });
      await expect(adminPage.locator('.tutor-toast, .tutor-alert, .notice-success')).toContainText(/Saved|Success/i);
    }
    await assertNoPhpErrors(adminPage);
  });
});
