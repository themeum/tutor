import { field, pickDateFromNow, type Scope, selectField, toggleSwitch } from './content-authoring';
import { expect, type Page } from './test';

/** Change the visible permalink (slug) for a course or bundle. */
export const setCourseAlias = async (page: Page, alias: string, label = 'Course URL'): Promise<void> => {
  const wrapper = field(page, label);
  await wrapper.locator('button').first().click();
  const input = wrapper.locator('input').first();
  await expect(input).toBeVisible();
  await input.fill(alias);
  await expect(input).toHaveValue(alias);
  // Let the controlled input commit before the Save handler reads its state.
  await input.blur();
  await wrapper.getByRole('button', { name: 'Save', exact: true }).click();
  await expect(page.getByTestId('course-slug')).toContainText(alias);
};

/** Open a tab in the shared course/bundle builder settings panel. */
export const openSettingsTab = async (page: Page, tab: 'General' | 'Content Drip' | 'Enrollment'): Promise<void> => {
  await page.locator('[data-testid="course-settings"]').getByRole('tab', { name: tab }).click();
};

/** Choose the purchase-options select (Pro subscription addon). */
export const choosePurchaseOption = async (scope: Scope, option: string): Promise<void> => {
  await selectField(scope, 'Purchase Options', option);
};

/** Create a subscription plan from the builder's Add Subscription modal. */
export const addSubscriptionPlan = async (
  page: Page,
  plan: { name: string; price: number; intervalValue?: number; intervalUnit?: string; cycles?: number },
): Promise<void> => {
  await page.getByTestId('add-subscription').click();
  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal).toBeVisible();

  await modal.locator('input[name="plan_name"]').fill(plan.name);
  await modal.locator('input[name="regular_price"]').fill(String(plan.price));
  await modal.locator('input[name="recurring_value"]').fill(String(plan.intervalValue ?? 1));

  if (plan.intervalUnit) {
    await modal.locator('input[data-select]').first().click();
    const option = page
      .locator('.tutor-portal-popover')
      .last()
      .getByRole('button', { name: plan.intervalUnit, exact: true })
      .filter({ visible: true });
    await expect(option).toBeVisible();
    await option.click();
  }

  await modal.locator('input[name="recurring_limit"]').fill(String(plan.cycles ?? 6));
  await page.getByTestId('save-subscription').click();
  await expect(modal).toBeHidden();
  await expect(page.getByText(plan.name, { exact: true }).first()).toBeVisible();
};

/** Fill both enrollment period date pickers and times. */
export const setEnrollmentPeriod = async (
  page: Page,
  startDays: number,
  endDays: number,
  switchLabel = 'Course Enrollment Period',
): Promise<void> => {
  await toggleSwitch(page, switchLabel, true);

  await pickDateFromNow(page, page.locator('#enrollment_starts_at input[data-input]').first(), startDays);
  await page.locator('#enrollment_starts_at input[data-input]').last().fill('09:00 AM');

  const addEnd = page.getByRole('button', { name: 'Add End Date', exact: true });
  await expect(addEnd).toBeEnabled();
  await addEnd.click();

  await pickDateFromNow(page, page.locator('#enrollment_ends_at input[data-input]').first(), endDays);
  await page.locator('#enrollment_ends_at input[data-input]').last().fill('05:00 PM');
};

/** Select the first real certificate template (the "None" card comes first). */
export const selectCertificate = async (page: Page): Promise<void> => {
  const selectButtons = page.getByRole('button', { name: 'Select', exact: true }).filter({ visible: true });
  await expect(selectButtons.first()).toBeVisible();
  const count = await selectButtons.count();
  await selectButtons.nth(count > 1 ? 1 : 0).click();
};
