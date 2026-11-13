import { assertNoPhpErrors } from '@e2e/support/admin';
import { expect, test } from '@e2e/support/test';
import { backendUrls } from '@e2e/support/urls';

test.describe('Admin Taxonomy Management @feature', () => {
  test('manages course categories with creation and search filtering', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.categories);

    await expect(adminPage.locator('.wp-heading-inline, h1')).toContainText(/Categories/i);
    await expect(adminPage.locator('#the-list')).toContainText('Web Development');

    // Add new Category
    await adminPage.locator('#tag-name').fill('Cloud & DevOps');
    await adminPage.locator('#tag-slug').fill('cloud-devops');
    await adminPage.locator('#submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText('Cloud & DevOps');

    // Search Category
    await adminPage.locator('#tag-search-input').fill('Cloud');
    await adminPage.locator('#search-submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText('Cloud & DevOps');
    await assertNoPhpErrors(adminPage);
  });

  test('manages course tags with creation and verification', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.tags);

    await expect(adminPage.locator('.wp-heading-inline, h1')).toContainText(/Tags/i);
    await expect(adminPage.locator('#the-list')).toContainText('JavaScript');

    // Add new Tag
    await adminPage.locator('#tag-name').fill('TypeScript');
    await adminPage.locator('#tag-slug').fill('typescript');
    await adminPage.locator('#submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText('TypeScript');
    await assertNoPhpErrors(adminPage);
  });
});
