import { expect, type Page } from './test';

/**
 * Filter an admin table using the global backend search input.
 */
export async function searchAdminTable(page: Page, query: string): Promise<void> {
  const searchInput = page.locator('#tutor-backend-filter-search');
  await searchInput.fill(query);
  await Promise.all([
    page.waitForURL((u) => u.searchParams.get('search') === query, { timeout: 10_000 }),
    searchInput.press('Enter'),
  ]);
  await assertNoPhpErrors(page);
}

/**
 * Reset/clear the admin search filter.
 */
export async function clearAdminSearch(page: Page): Promise<void> {
  const searchInput = page.locator('#tutor-backend-filter-search');
  await searchInput.fill('');
  await Promise.all([
    page.waitForURL((u) => !u.searchParams.get('search'), { timeout: 10_000 }),
    searchInput.press('Enter'),
  ]);
  await expect(searchInput).toHaveValue('');
  await assertNoPhpErrors(page);
}

/**
 * Apply filters from the admin filter popup dropdown form.
 */
export async function applyAdminFilter(page: Page, filters: Record<string, string>): Promise<void> {
  const filterBtn = page.locator('.tutor-wp-dashboard-filters-button').first();
  const form = page.locator('.tutor-admin-dashboard-filter-form');

  await expect(filterBtn).toBeVisible();
  await filterBtn.click();
  await expect(form).toBeVisible();

  for (const [name, value] of Object.entries(filters)) {
    const item = form
      .locator('.tutor-wp-dashboard-filters-item')
      .filter({ has: page.locator(`select[name="${name}"]`) });

    const dropdown = item.locator('.tutor-js-form-select');
    if (await dropdown.isVisible()) {
      await dropdown.click();
      const option = item.locator(`span[tutor-dropdown-item][data-key="${value}"], span[data-key="${value}"]`).first();
      await option.click();
    } else {
      const select = form.locator(`select[name="${name}"]`);
      await select.selectOption({ value }, { force: true });
    }
  }

  const submitBtn = form.locator('button[type="submit"]');
  await Promise.all([
    page.waitForURL((u) => Object.entries(filters).every(([k, v]) => u.searchParams.get(k) === v), {
      timeout: 10_000,
    }),
    submitBtn.click(),
  ]);
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
  await page.waitForURL((url) => url.href !== initialUrl, { timeout: 10_000 });
  await assertNoPhpErrors(page);
}

/**
 * Assert that no PHP fatal errors, warnings, or parse errors are rendered in the DOM.
 */
export async function assertNoPhpErrors(page: Page): Promise<void> {
  await expect(page.locator('#error-page, .wp-die-message, body.php-error')).toHaveCount(0);
  await expect(page.locator('body')).not.toContainText(
    /Fatal error:|Parse error:|Uncaught \w*(Error|Exception)|There has been a critical error/,
  );
}
