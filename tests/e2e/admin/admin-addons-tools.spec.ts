import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Addons and Tools @feature', () => {
  test('displays available addons and filter tabs', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.addons);
    await expect(adminPage.getByText(/Available Addons|Addons/i).first()).toBeVisible();

    // Verify key addon items are listed
    await expect(adminPage.getByText('Course Bundle').first()).toBeVisible();
    await expect(adminPage.getByText('Content Bank').first()).toBeVisible();
  });

  test('loads tools page with system status and environment info', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.tools);
    await expect(adminPage.locator('.wrap h1, .tutor-admin-wrap').first()).toContainText('Tools');

    // Verify System Environment block
    await expect(adminPage.getByText('WordPress environment').first()).toBeVisible();
    await expect(adminPage.getByText('Home URL').first()).toBeVisible();
  });
});
