import path from 'node:path';

import {
  type AssignmentFormInput,
  chooseOption,
  escapeRegExp,
  field,
  fillAssignmentForm,
  fillLessonForm,
  type LessonFormInput,
  pickDateFromNow,
  type Scope,
  selectField,
  toggleCheckbox,
  toggleSwitch,
  uploadMedia,
} from './content-authoring';
import { expect, type Locator, type Page } from './test';
import { backendUrls } from './urls';

export const fixtures = {
  image: path.resolve(process.cwd(), 'tests/e2e/fixtures/course-featured-image.png'),
  attachment: path.resolve(process.cwd(), 'tests/e2e/fixtures/course-attachment.pdf'),
};

/** Upload a file to the course-level Attachments box on the Additional page. */
export const uploadCourseAttachment = async (page: Page, file: string): Promise<void> => {
  const uploader = page
    .locator('[data-testid="form-field-wrapper"]')
    .filter({ has: page.getByRole('button', { name: 'Upload Attachment', exact: true }) })
    .filter({ visible: true })
    .last();
  await uploadMedia(page, uploader, file);
};

/** Select an existing category by its visible label. */
export const selectCategory = async (
  page: Page,
  name: string,
  categoryField = field(page, 'Categories'),
): Promise<void> => {
  await categoryField.getByText(name, { exact: true }).click();
};

/** Create a new tag and select it. Tag names must be unique per run. */
export const addTag = async (page: Page, name: string, tagField = field(page, 'Tags')): Promise<void> => {
  const input = tagField.locator('input').first();
  await input.click();
  await input.fill(name);

  const addButton = page.getByRole('button', { name: new RegExp(`Add\\s+${escapeRegExp(name)}`) }).last();
  await expect(addButton).toBeVisible();
  await addButton.click();
  await expect(tagField.getByText(name, { exact: true })).toBeVisible();
};

/** Change the course author through the author picker and confirmation modal. */
export const changeAuthor = async (page: Page, authorName: string): Promise<void> => {
  const authorField = field(page, 'Author');
  await authorField.locator('button').first().click();

  const popover = page.locator('.tutor-portal-popover').last();
  const search = popover.locator('input').first();
  await expect(search).toBeVisible();
  if (await search.isVisible().catch(() => false)) {
    await search.fill(authorName);
  }
  await popover.getByRole('button', { name: new RegExp(escapeRegExp(authorName)) }).click();

  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal.getByText('Are you sure?')).toBeVisible();
  await modal.getByRole('button', { name: 'Yes, change author' }).click();
  await expect(modal).toBeHidden();
  await expect(authorField).toContainText(authorName);
};

/** Change the visible course permalink (slug). */
export const setCourseAlias = async (page: Page, alias: string): Promise<void> => {
  const wrapper = field(page, 'Course URL');
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

/** Toggle the schedule switch and save a future date inside the schedule card. */
export const setSchedule = async (page: Page, daysFromNow = 30): Promise<Date> => {
  await toggleSwitch(page, 'Schedule', true);
  const dateInput = page.locator('input[data-input][placeholder="Select date"]:visible').last();
  const date = await pickDateFromNow(page, dateInput, daysFromNow);
  await page.locator('input[data-input][placeholder="hh:mm A"]:visible').last().fill('10:00 AM');
  await page.getByRole('button', { name: 'Ok', exact: true }).click();
  await expect(page.getByText(/Scheduled/).first()).toBeVisible();
  return date;
};

/** Fill both enrollment period date pickers and times. */
export const setEnrollmentPeriod = async (page: Page, startDays: number, endDays: number): Promise<void> => {
  await toggleSwitch(page, 'Course Enrollment Period', true);

  await pickDateFromNow(page, page.locator('#enrollment_starts_at input[data-input]').first(), startDays);
  await page.locator('#enrollment_starts_at input[data-input]').last().fill('09:00 AM');

  const addEnd = page.getByRole('button', { name: 'Add End Date', exact: true });
  await expect(addEnd).toBeEnabled();
  await addEnd.click();

  await pickDateFromNow(page, page.locator('#enrollment_ends_at input[data-input]').first(), endDays);
  await page.locator('#enrollment_ends_at input[data-input]').last().fill('05:00 PM');
};

/** Fill the hour/minute pair under Total Course Duration. */
export const fillCourseDuration = async (scope: Scope, hours: number, minutes: number): Promise<void> => {
  const wrapper = field(scope, 'Total Course Duration');
  const parent = wrapper.locator('..');
  await parent.locator('input').nth(0).fill(String(hours));
  await parent.locator('input').nth(1).fill(String(minutes));
};

/** Choose a pricing model radio option. */
export const choosePricingModel = async (scope: Scope, model: 'Free' | 'Paid'): Promise<void> => {
  await chooseOption(scope, 'Pricing Model', model);
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

/** Start a new course with the builder and return its ID. */
export const createCourseInBuilder = async (page: Page): Promise<number> => {
  await page.goto(backendUrls.tutorDashboard);
  await page.locator('.tutor-create-new-course:visible').first().click();
  await expect(page).toHaveURL(/course_id=\d+/);
  await expect(page.getByTestId('course-builder-submit-button')).toBeVisible();

  const courseId = Number(new URL(page.url()).searchParams.get('course_id'));
  expect(courseId).toBeGreaterThan(0);
  return courseId;
};

/** Open an existing course in the builder. */
export const openCourseBuilder = async (page: Page, courseId: number): Promise<void> => {
  await page.goto(`${backendUrls.courseBuilder}&course_id=${courseId}`);
  await expect(page.getByTestId('course-builder-submit-button')).toBeVisible();
  // The details query resets the form after mount. Editing during that reset can
  // detach the submit button and silently drop the save click.
  await expect(field(page, 'Title').locator('input:visible, textarea:visible').first()).not.toHaveValue('');
};

/** Navigate between Basics / Curriculum / Additional. */
export const openBuilderStep = async (page: Page, step: 'Basics' | 'Curriculum' | 'Additional'): Promise<void> => {
  await page.locator('[data-testid="tutor-tracker"] button').filter({ hasText: step }).click();
  await expect(page.locator('[data-testid="tutor-tracker"] button').filter({ hasText: step })).toBeVisible();
};

/** Open a tab inside the course settings card (General, Content Drip, Enrollment). */
export const openSettingsTab = async (page: Page, tab: 'General' | 'Content Drip' | 'Enrollment'): Promise<void> => {
  await page.locator('[data-testid="course-settings"]').getByRole('tab', { name: tab }).click();
};

/** Save the course from the header and wait for the success toast. */
export const saveCourse = async (page: Page): Promise<void> => {
  const toast = page.getByTestId('tutor-toast').filter({ hasText: /course updated successfully/i });
  // The success toast lives for 3s; clear the previous one so its replacement is unambiguous.
  await toast
    .last()
    .waitFor({ state: 'hidden' })
    .catch(() => undefined);

  await page.getByTestId('course-builder-submit-button').click();
  await expect(toast.last()).toBeVisible({ timeout: 20_000 });
};

export const addTopic = async (page: Page, title: string, summary = ''): Promise<void> => {
  await page.locator('[data-testid="add-topic"]:visible').first().click();
  const titleInput = page.locator('input[name="title"]:visible').last();
  await titleInput.fill(title);
  if (summary) {
    await page.locator('textarea[name="summary"]:visible').last().fill(summary);
  }
  await page.locator('[data-testid="save-topic"]:visible').last().click();
  await expect(
    page
      .getByTestId('tutor-toast')
      .filter({ hasText: /topic saved successfully/i })
      .last(),
  ).toBeVisible();
  // The topic editor stays inline; wait for edit mode to close before adding content.
  await expect(page.getByTestId('save-topic')).toBeHidden();
  await expect(page.locator(`text="${title}"`).first()).toBeVisible();
};

export const addLesson = async (page: Page, input: LessonFormInput): Promise<void> => {
  await page.locator('[data-testid="add-lesson"]:visible').first().click();
  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal).toBeVisible();

  await fillLessonForm(page, modal, input);

  await page.getByTestId('save-lesson').click();
  await expect(modal).toBeHidden();
  await expect(page.locator(`text="${input.title}"`).first()).toBeVisible();
};

/**
 * A course quiz is a container with its own settings. Content Bank questions are
 * standalone, so they use a separate question editor instead of this helper.
 */
interface QuizInput {
  title: string;
  description?: string;
  passingGrade?: number;
  allowAttempts?: boolean;
  attempts?: number;
  timeLimit?: { value: number; unit: string };
  autoStart?: boolean;
  revealAnswers?: boolean;
  layout?: 'Single Question' | 'Full Page';
  dripDays?: number;
}

export const addQuiz = async (page: Page, input: QuizInput): Promise<void> => {
  await page.locator('[data-testid="add-quiz"]:visible').first().click();
  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal).toBeVisible();

  const titleInput = modal.locator('textarea[name="quiz_title"], input[name="quiz_title"]').first();
  await titleInput.fill(input.title);
  // The description textarea only renders while the title block is in edit mode.
  if (input.description) {
    await modal.locator('textarea[name="quiz_description"]').fill(input.description);
  }
  const saveTitle = page.getByTestId('save-quiz-title');
  if (await saveTitle.isVisible().catch(() => false)) {
    await saveTitle.click();
  }

  await modal.getByTestId('add-question').click();
  // The question-type picker is a portal, not a child of the quiz modal.
  const picker = page.locator('.tutor-portal-popover').last();
  const trueFalse = picker.getByRole('button', { name: 'True/False', exact: true }).filter({ visible: true });
  await expect(trueFalse).toBeVisible();
  await trueFalse.click();
  await page.getByTestId('quiz-next').click();

  const settingsTab = modal.getByRole('tab', { name: 'Settings' });
  if (await settingsTab.isVisible().catch(() => false)) {
    await settingsTab.click();
  }

  if (input.passingGrade !== undefined) {
    await modal.locator('input[name="quiz_option.passing_grade"]').fill(String(input.passingGrade));
  }
  if (input.allowAttempts !== undefined) {
    await toggleCheckbox(modal, 'Allow multiple attempts', input.allowAttempts);
  }
  if (input.attempts !== undefined) {
    await modal.locator('input[name="quiz_option.attempts_allowed"]').fill(String(input.attempts));
  }
  if (input.timeLimit) {
    await toggleCheckbox(modal, 'Set time limit', true);
    await modal.locator('[data-time-limit]').fill(String(input.timeLimit.value));
    await modal.locator('[data-time-limit-unit]').click();
    const unit = page
      .locator('.tutor-portal-popover')
      .last()
      .getByRole('button', { name: input.timeLimit.unit, exact: true })
      .filter({ visible: true });
    await expect(unit).toBeVisible();
    await unit.click();
  }
  if (input.autoStart) {
    await toggleCheckbox(modal, 'Auto start quiz', true);
  }
  if (input.revealAnswers) {
    await toggleCheckbox(modal, 'Reveal answers after submission', true);
  }
  if (input.layout) {
    await modal.getByText(input.layout, { exact: true }).click();
  }
  if (input.dripDays !== undefined) {
    await modal
      .locator('input[name="quiz_option.content_drip_settings.after_xdays_of_enroll"]')
      .fill(String(input.dripDays));
  }

  await page.getByTestId('save-quiz').click();
  await expect(modal).toBeHidden();
  await expect(page.locator(`text="${input.title}"`).first()).toBeVisible();
};

export const addAssignment = async (page: Page, input: AssignmentFormInput): Promise<void> => {
  await page.locator('[data-testid="add-assignment"]:visible').first().click();
  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal).toBeVisible();

  await fillAssignmentForm(page, modal, input);

  await page.getByTestId('save-assignment').click();
  await expect(modal).toBeHidden();
  await expect(page.locator(`text="${input.title}"`).first()).toBeVisible();
};

/** Pick a content drip strategy from the Basics > Content Drip tab. */
export const chooseContentDrip = async (page: Page, strategy: string): Promise<void> => {
  await openSettingsTab(page, 'Content Drip');
  await page.getByText(strategy, { exact: true }).click();
};

/** Select the first real certificate template (the "None" card comes first). */
export const selectCertificate = async (page: Page): Promise<void> => {
  const selectButtons = page.getByRole('button', { name: 'Select', exact: true }).filter({ visible: true });
  await expect(selectButtons.first()).toBeVisible();
  const count = await selectButtons.count();
  await selectButtons.nth(count > 1 ? 1 : 0).click();
};

export const selectPrerequisite = async (page: Page, courseTitle: string): Promise<void> => {
  const input = page.locator('input[placeholder*="Search courses for prerequisites"]').first();
  // The course list loads after the step renders; clicking through the fetch is dropped.
  await expect(page.getByText('Select a course to add as a prerequisite.', { exact: true })).toBeVisible();
  await input.click();
  await input.fill(courseTitle);
  const popover = page.locator('.tutor-portal-popover').last();
  const option = popover.getByRole('button', { name: new RegExp(escapeRegExp(courseTitle)) }).first();
  await expect(option).toBeVisible();
  await option.click();
  await expect(page.getByText(courseTitle, { exact: true }).first()).toBeVisible();
};

/** Open the edit modal for a curriculum item. Journeys create one item of each type. */
export const openContentEditor = async (
  page: Page,
  type: 'lesson' | 'tutor_quiz' | 'tutor_assignments',
): Promise<Locator> => {
  await page.getByTestId(`edit-${type}`).first().click();
  const modal = page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
  await expect(modal).toBeVisible();
  return modal;
};

/** Select a lesson/quiz/assignment as a content-drip prerequisite. */
export const selectContentPrerequisite = async (page: Page, modal: Locator, title: string): Promise<void> => {
  const input = modal.locator('input[placeholder="Select Prerequisite"]').first();
  // Options are fetched with the course; clicking before the fetch settles does nothing.
  await expect(modal.getByText('Select a topic content to add as a prerequisite', { exact: true })).toBeVisible();
  await input.click();
  await input.fill(title);
  const popover = page.locator('.tutor-portal-popover').last();
  const option = popover.getByRole('button', { name: new RegExp(escapeRegExp(title)) }).first();
  await expect(option).toBeVisible();
  await option.click();
  await expect(modal.getByText(title, { exact: true }).first()).toBeVisible();
};
