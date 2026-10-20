import { expect, type Page } from './test';

/**
 * Filter an admin table using the global backend search input.
 */
export async function searchAdminTable(page: Page, query: string): Promise<void> {
  const searchInput = page.locator('#tutor-backend-filter-search');
  await searchInput.fill(query);
  await searchInput.press('Enter');
  await page.waitForLoadState('domcontentloaded');
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
}

/**
 * Assert that no PHP fatal errors, warnings, or parse errors are rendered.
 */
export async function assertNoPhpErrors(page: Page): Promise<void> {
  const bodyText = await page.locator('body').innerText();
  expect(bodyText).not.toMatch(/Fatal error|Parse error|Warning:/i);
}
