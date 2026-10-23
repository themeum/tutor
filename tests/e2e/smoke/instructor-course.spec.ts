import { expect } from '@playwright/test';

import { test } from '../support/test';
import { backendUrls } from '../support/urls';

test('instructor creates and publishes a course with a lesson @smoke', async ({ instructorPage: page }) => {
  const title = `E2E Course ${Date.now()}`;
  const lessonTitle = `E2E Lesson ${Date.now()}`;

  await page.goto(backendUrls.tutorDashboard);
  await page.locator('.tutor-create-new-course:visible').first().click();
  await expect(page).toHaveURL(/course_id=\d+/);

  await page.locator('input[name="post_title"]:visible').first().fill(title);
  await page.getByTestId('course-builder-submit-button').click();
  await expect(
    page
      .getByTestId('tutor-toast')
      .filter({ hasText: /course updated successfully/i })
      .first(),
  ).toBeVisible();

  await page.getByRole('button', { name: 'Curriculum' }).first().click();
  await page.locator('[data-testid="add-topic"]:visible').first().click();
  await page.locator('input[name="title"]:visible').first().fill('Introduction');
  await page.locator('[data-testid="save-topic"]:visible').last().click();
  await expect(
    page
      .getByTestId('tutor-toast')
      .filter({ hasText: /topic saved successfully/i })
      .first(),
  ).toBeVisible();

  await page.locator('[data-testid="add-lesson"]:visible').first().click();
  const modal = page.locator('[data-testid="tutor-modal"]:visible').last();
  await modal.locator('input[name="title"]').fill(lessonTitle);
  await modal.getByTestId('save-lesson').click();
  await expect(
    page
      .getByTestId('tutor-toast')
      .filter({ hasText: /lesson saved successfully/i })
      .first(),
  ).toBeVisible();

  await page.getByTestId('course-builder-submit-button').click();
  await expect(
    page
      .getByTestId('tutor-toast')
      .filter({ hasText: /course updated successfully/i })
      .first(),
  ).toBeVisible();

  await page.goto(backendUrls.tutorDashboard);
  await expect(page.locator('a.tutor-table-link').filter({ hasText: title }).first()).toBeVisible();
});
