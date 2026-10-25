import { expect, type Locator, type Page } from './test';

/**
 * Find a course card in the paginated archive. Existing data can outrank a new
 * course by release date, so the card is not guaranteed to be on page one.
 */
export const findCourseCard = async (page: Page, title: string, maxPages = 10): Promise<Locator> => {
  const card = page.locator('.tutor-course-card', { hasText: title });

  for (let currentPage = 1; currentPage < maxPages; currentPage++) {
    if (await card.count()) {
      await expect(card.first()).toBeVisible();
      return card.first();
    }

    const next = page.getByRole('link', { name: 'Next page', exact: true }).filter({ visible: true });
    if (!(await next.count())) {
      break;
    }
    await next.first().click();
    await page.waitForLoadState('domcontentloaded');
  }

  await expect(card.first()).toBeVisible();
  return card.first();
};
