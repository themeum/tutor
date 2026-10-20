import { expect } from '@playwright/test';

import { createCourse } from '../support/factories';
import { test } from '../support/test';

test('student enrolls in a free course and opens the lesson @smoke', async ({ studentPage: page }) => {
  const stamp = Date.now();
  const content = `E2E free lesson content ${stamp}`;
  const course = createCourse({
    title: `E2E Free Course ${stamp}`,
    lessonTitle: `E2E Free Lesson ${stamp}`,
    lessonContent: content,
  });

  await page.goto(course.url);
  await page.locator('.tutor-enroll-course-button').click();

  const startLearning = page.locator('a:has-text("Start Learning"), button:has-text("Start Learning")');
  await expect(startLearning).toBeVisible({ timeout: 15_000 });
  await startLearning.click();

  const skipTour = page.getByRole('button', { name: 'Skip' });
  if (await skipTour.isVisible().catch(() => false)) {
    await skipTour.click();
  }

  await expect(page.getByText(content)).toBeVisible();
});
