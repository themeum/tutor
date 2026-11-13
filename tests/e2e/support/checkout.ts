import { expect, type Page } from './test';

/** Complete the native Tutor checkout with the seeded manual bank-transfer method. */
export const payWithNativeCheckout = async (page: Page, email: string): Promise<number> => {
  const buyNow = page
    .locator('[data-testid="tutor-buy-now"], a:has-text("Buy Now"):visible, button:has-text("Buy Now"):visible')
    .first();
  await expect(buyNow).toBeVisible();
  await buyNow.click();
  await expect(page).toHaveURL(/course_id=\d+/);

  await page.locator('input[name="billing_first_name"]').fill('E2E');
  await page.locator('input[name="billing_last_name"]').fill('Journey');
  await page.locator('input[name="billing_email"]').fill(email);

  const checkoutUpdate = page.waitForResponse(
    (response) =>
      response.url().includes('admin-ajax.php') &&
      (response.request().postData() ?? '').includes('tutor_get_checkout_html'),
  );
  await page.locator('select[name="billing_country"]').selectOption('United States');
  await checkoutUpdate;

  await page.locator('input[name="billing_city"]').fill('Boston');
  await page.locator('input[name="billing_zip_code"]').fill('02108');
  await page.locator('input[name="billing_phone"]').fill('555-0199');
  await page.locator('input[name="billing_address"]').fill('42 Beacon St');

  const terms = page.locator('input[name="agree_to_terms"]');
  if (await terms.isVisible().catch(() => false)) {
    await terms.check();
  }

  await page.locator('input[name="payment_method"][value="bank-transfer"]').check({ force: true });
  await page.locator('#tutor-checkout-pay-now-button').click();

  await expect(page).toHaveURL(/tutor_order_placement=success/, { timeout: 20_000 });
  await expect(page.getByText('Order Confirmed')).toBeVisible();

  const orderId = Number(new URL(page.url()).searchParams.get('order_id'));
  expect(orderId).toBeGreaterThan(0);
  return orderId;
};

/** Pay through the WooCommerce block checkout with cash on delivery enabled. */
export const payWithWooCommerceCheckout = async (page: Page, email: string): Promise<number> => {
  await page.goto('/checkout/');
  const checkout = page.locator('.wc-block-checkout').first();
  await expect(checkout).toBeVisible({ timeout: 20_000 });
  await expect(checkout.locator('.wc-block-components-spinner'))
    .toHaveCount(0, { timeout: 20_000 })
    .catch(() => undefined);

  await page.locator('#email').fill(email);

  // A returning customer's saved billing address collapses the form; open it
  // so the checkout is exercised with a known, valid address every time.
  const editBilling = page.getByRole('button', { name: 'Edit billing address' });
  if (await editBilling.isVisible().catch(() => false)) {
    await editBilling.click();
  }
  await expect(page.locator('#billing-country')).toBeVisible();

  await page.locator('#billing-country').selectOption('US');
  await expect(page.locator('#billing-state option[value="MA"]')).toBeAttached();
  await page.locator('#billing-state').selectOption('MA');
  await page.locator('#billing-first_name').fill('E2E');
  await page.locator('#billing-last_name').fill('WooCommerce');
  await page.locator('#billing-address_1').fill('42 Beacon St');
  await page.locator('#billing-city').fill('Boston');
  await page.locator('#billing-postcode').fill('02108');
  await page.locator('#billing-phone').fill('555-0199');

  await page.locator('#radio-control-wc-payment-method-options-cod').check();
  await page.locator('.wc-block-components-checkout-place-order-button').click();

  await expect(page).toHaveURL(/order-received\/\d+/, { timeout: 30_000 });
  const match = page.url().match(/order-received\/(\d+)/);
  expect(match).not.toBeNull();
  return Number(match?.[1]);
};
