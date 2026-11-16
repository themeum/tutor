import { assertNoPhpErrors } from '@Test/e2e/support/admin';
import { expect, test } from '@Test/e2e/support/test';
import { backendUrls } from '@Test/e2e/support/urls';

test.describe('Admin Taxonomy Management @feature', () => {
  test('manages course categories with creation and search filtering', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.categories);

    await expect(adminPage.locator('.wp-heading-inline, h1')).toContainText(/Categories/i);
    await expect(adminPage.locator('#the-list')).toContainText('Web Development');

    const stamp = Date.now();
    const catName = `Cloud & DevOps ${stamp}`;
    const catSlug = `cloud-devops-${stamp}`;

    // Add new Category
    await adminPage.locator('#tag-name').fill(catName);
    await adminPage.locator('#tag-slug').fill(catSlug);
    await adminPage.locator('#submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText(catName);

    // Search Category
    await adminPage.locator('#tag-search-input').fill(String(stamp));
    await adminPage.locator('#search-submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText(catName);
    await assertNoPhpErrors(adminPage);
  });

  test('manages course tags with creation and verification', async ({ adminPage }) => {
    await adminPage.goto(backendUrls.tags);

    await expect(adminPage.locator('.wp-heading-inline, h1')).toContainText(/Tags/i);
    await expect(adminPage.locator('#the-list')).toContainText('JavaScript');

    const stamp = Date.now();
    const tagName = `TypeScript ${stamp}`;
    const tagSlug = `typescript-${stamp}`;

    // Add new Tag
    await adminPage.locator('#tag-name').fill(tagName);
    await adminPage.locator('#tag-slug').fill(tagSlug);
    await adminPage.locator('#submit').click();
    await adminPage.waitForLoadState('domcontentloaded');
    await expect(adminPage.locator('#the-list')).toContainText(tagName);
    await assertNoPhpErrors(adminPage);
  });
});
