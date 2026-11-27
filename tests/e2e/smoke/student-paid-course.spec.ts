import { expect } from '@playwright/test';

import { payWithNativeCheckout } from '../support/checkout';
import { createCourse } from '../support/factories';
import { test } from '../support/test';

test('student buys a paid course with the native manual payment method @smoke', async ({ student2Page: page }) => {
  const stamp = Date.now();
  const course = createCourse({
    title: `E2E Paid Course ${stamp}`,
    price_type: 'paid',
    price: 49,
    lessonTitle: `E2E Paid Lesson ${stamp}`,
    lessonContent: `E2E paid lesson content ${stamp}`,
  });

  await page.goto(course.url);
  const orderId = await payWithNativeCheckout(page, `student-${stamp}@example.test`);

  expect(orderId).toBeGreaterThan(0);
});
