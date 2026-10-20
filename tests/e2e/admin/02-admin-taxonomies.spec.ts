import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test.describe('Admin Taxonomy Management @feature', () => {
  test('manages course categories and course tags', async ({ adminPage }) => {
    // 1. Categories
    await adminPage.goto(backendUrls.categories);
    await expect(adminPage.locator('h1.wp-heading-inline, .wrap h1').first()).toContainText(/Categories/i);

    // Verify seeded categories in the taxonomy table
    const categoryTable = adminPage.locator('#the-list');
    await expect(categoryTable.locator('text=Web Development').first()).toBeVisible();

    // Add new Category
    const catNameInput = adminPage.locator('#tag-name');
    await catNameInput.fill('Cloud & DevOps');
    await adminPage.locator('#submit').click();

    // Wait for AJAX insertion or table refresh
    await expect(categoryTable.locator('text=Cloud & DevOps').first()).toBeVisible();

    // 2. Tags
    await adminPage.goto(backendUrls.tags);
    await expect(adminPage.locator('h1.wp-heading-inline, .wrap h1').first()).toContainText(/Tags/i);

    // Verify seeded tag in the tags table
    const tagsTable = adminPage.locator('#the-list');
    await expect(tagsTable.locator('text=JavaScript').first()).toBeVisible();

    // Add new Tag
    const tagNameInput = adminPage.locator('#tag-name');
    await tagNameInput.fill('TypeScript');
    await adminPage.locator('#submit').click();

    await expect(tagsTable.locator('text=TypeScript').first()).toBeVisible();
  });
});
