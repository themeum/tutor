import { expect, type Page } from './test';

/**
 * Filter an admin table using the global backend search input.
 */
export async function searchAdminTable(page: Page, query: string): Promise<void> {
  const searchInput = page.locator('#tutor-backend-filter-search');
  await searchInput.fill(query);
  await searchInput.press('Enter');
  await page.waitForLoadState('domcontentloaded');
  await assertNoPhpErrors(page);
}

/**
 * Reset/clear the admin search filter.
 */
export async function clearAdminSearch(page: Page): Promise<void> {
  const clearButton = page.locator('#tutor-backend-filter-search ~ button, a:has-text("Reset"), a:has-text("Clear")');
  if (
    await clearButton
      .first()
      .isVisible()
      .catch(() => false)
  ) {
    await clearButton.first().click();
  } else {
    const searchInput = page.locator('#tutor-backend-filter-search');
    await searchInput.fill('');
    await searchInput.press('Enter');
  }
  await page.waitForLoadState('domcontentloaded');
  await assertNoPhpErrors(page);
}

/**
 * Apply filters from the admin filter popup dropdown form.
 */
export async function applyAdminFilter(page: Page, filters: Record<string, string>): Promise<void> {
  const filterBtn = page.locator('.tutor-wp-dashboard-filters-button');
  const form = page.locator('.tutor-admin-dashboard-filter-form');

  if (await filterBtn.isVisible()) {
    await filterBtn.click();
  }

  for (const [name, value] of Object.entries(filters)) {
    const select = form.locator(`select[name="${name}"]`);
    await select.selectOption({ value }, { force: true });
  }

  const submitBtn = form.locator('button[type="submit"]');
  const initialUrl = page.url();
  await submitBtn.click();
  await page.waitForFunction((prev) => window.location.href !== prev, initialUrl, { timeout: 10_000 }).catch(() => {});
  await page.waitForLoadState('domcontentloaded');
  await assertNoPhpErrors(page);
}

/**
 * Toggle the admin table sort order between ASC and DESC.
 */
export async function toggleAdminSortOrder(page: Page): Promise<void> {
  const sortBtn = page.locator('.tutor-wp-dashboard-filter-order');
  await expect(sortBtn).toBeVisible();
  const initialUrl = page.url();
  await sortBtn.click();
  await page.waitForFunction((prev) => window.location.href !== prev, initialUrl, { timeout: 10_000 }).catch(() => {});
  await page.waitForLoadState('domcontentloaded');
  await assertNoPhpErrors(page);
}

/**
 * Assert that no PHP fatal errors, warnings, or parse errors are rendered.
 */
export async function assertNoPhpErrors(page: Page): Promise<void> {
  const bodyText = await page.locator('body').innerText();
  expect(bodyText).not.toMatch(/Fatal error|Parse error|Warning:/i);
}
