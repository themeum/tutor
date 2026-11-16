import { expect } from '@playwright/test';

import { createCourse } from '../support/factories';
import { test } from '../support/test';

test('unenrolled student cannot view lesson content @smoke', async ({ student2Page: page }) => {
  const stamp = Date.now();
  const content = `E2E locked lesson content ${stamp}`;
  const course = createCourse({
    title: `E2E Locked Course ${stamp}`,
    lessonTitle: `E2E Locked Lesson ${stamp}`,
    lessonContent: content,
  });

  await page.goto(course.lessonUrl);

  await expect(page.getByRole('img', { name: 'Permission Denied' })).toBeVisible();
  await expect(page.getByText('Please enroll in this course to view course content.')).toBeVisible();
  await expect(page.getByText(content)).toHaveCount(0);
});
