import { assertNoPhpErrors } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Addons and Tools @feature', () => {
  test('displays available addons catalog and key integrations', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.addons);

    const addonWrapper = adminPage.locator('#tutor-addon-list-wrapper');
    await expect(addonWrapper).toBeVisible({ timeout: 15000 });
    await expect(addonWrapper).toContainText('Course Bundle');
    await expect(addonWrapper).toContainText('Content Bank');
    await assertNoPhpErrors(adminPage);
  });

  test('loads tools page with system status and environment info', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.tools);

    await expect(adminPage.locator('body')).toContainText('WordPress environment');
    await expect(adminPage.locator('body')).toContainText('Home URL');
    await assertNoPhpErrors(adminPage);
  });
});
