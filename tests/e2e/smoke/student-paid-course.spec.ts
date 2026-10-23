import { expect } from '@playwright/test';

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
  const buyNowBtn = page
    .locator(
      '[data-testid="tutor-buy-now"], [data-cy="tutor-buy-now"], a:has-text("Buy Now"):visible, button:has-text("Buy Now"):visible',
    )
    .first();
  await expect(buyNowBtn).toBeVisible();
  await buyNowBtn.click();
  await expect(page).toHaveURL(/course_id=\d+/);

  await page.locator('input[name="billing_first_name"]').fill('E2E');
  await page.locator('input[name="billing_last_name"]').fill('Student');
  await page.locator('input[name="billing_email"]').fill(`student-${stamp}@example.test`);
  const checkoutUpdate = page.waitForResponse(
    (response) =>
      response.url().includes('admin-ajax.php') &&
      (response.request().postData() ?? '').includes('tutor_get_checkout_html'),
  );
  await page.locator('select[name="billing_country"]').selectOption('United States');
  await checkoutUpdate;
  await page.locator('input[name="billing_city"]').fill('New York');
  await page.locator('input[name="billing_zip_code"]').fill('10001');
  await page.locator('input[name="billing_phone"]').fill('555-0100');
  await page.locator('input[name="billing_address"]').fill('1 E2E Street');

  const terms = page.locator('input[name="agree_to_terms"]');
  if (await terms.isVisible().catch(() => false)) {
    await terms.check();
  }

  await page.locator('input[name="payment_method"][value="bank-transfer"]').check({ force: true });
  await page.locator('#tutor-checkout-pay-now-button').click();

  await expect(page).toHaveURL(/tutor_order_placement=success/, { timeout: 20_000 });
  await expect(page.getByText('Order Confirmed')).toBeVisible();
});
