import { expect, test } from '../support/test';
import { backendUrls } from '../support/urls';

test('instructor creates and publishes a course with a lesson @smoke', async ({ courseBuilder }) => {
  const title = `E2E Course ${Date.now()}`;
  const lessonTitle = `E2E Lesson ${Date.now()}`;

  await courseBuilder.createNew();
  await courseBuilder.setTitle(title);
  await courseBuilder.save();

  await courseBuilder.openStep('Curriculum');
  await courseBuilder.addTopic('Introduction');
  await courseBuilder.addLesson({ title: lessonTitle });
  await courseBuilder.save();

  await courseBuilder.page.goto(backendUrls.tutorDashboard);
  await expect(courseBuilder.page.locator('a.tutor-table-link').filter({ hasText: title }).first()).toBeVisible();
});
