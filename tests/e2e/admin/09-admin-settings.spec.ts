import { assertNoPhpErrors } from '@Test/e2e/support/admin';
import { ensureNativePaymentSettings } from '@Test/e2e/support/checkout';
import { setTutorOption, snapshot } from '@Test/e2e/support/factories';
import { expect, test } from '@Test/e2e/support/test';
import { backendUrls } from '@Test/e2e/support/urls';

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
    // Saving regenerates payment_settings from installed gateways and drops the
    // seeded manual bank-transfer method, so restore it for later checkout tests.
    const tutorOptions = (snapshot({ options: ['tutor_option'] }).options.tutor_option ?? {}) as Record<
      string,
      unknown
    >;
    const previousPaymentSettings = tutorOptions.payment_settings;

    try {
      await adminPage.goto(backendUrls.settings);

      const saveButton = adminPage.locator('#save_tutor_option, button[name="tutor_option_save_btn"]');
      await expect(saveButton).toBeAttached();

      // Toggle a setting to dirty the form, then toggle it back so the saved
      // configuration stays the same for later tests.
      const firstToggle = adminPage.locator('.tutor-form-toggle').first();
      await firstToggle.click();
      await expect(saveButton).toBeEnabled();
      await firstToggle.click();
      await expect(saveButton).toBeEnabled();

      // Save changes
      await saveButton.click();
      const toast = adminPage
        .locator('.tutor-toast-parent .tutor-notification')
        .filter({ hasText: /Settings Saved|Success/i });
      await expect(toast).toBeVisible({ timeout: 10_000 });
      await assertNoPhpErrors(adminPage);
    } finally {
      if (previousPaymentSettings) {
        setTutorOption('payment_settings', previousPaymentSettings);
      } else {
        ensureNativePaymentSettings();
      }
    }
  });
});
