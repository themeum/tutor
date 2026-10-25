import path from 'node:path';

import { expect, type Locator, type Page } from './test';

export type Scope = Page | Locator;

export const escapeRegExp = (value: string) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

const pageOf = (scope: Scope): Page => {
  return typeof (scope as Locator).page === 'function' ? (scope as Locator).page() : (scope as Page);
};

/**
 * Locate a form field by its visible label. `data-testid="form-field-wrapper"`
 * wraps fields in both the Course Builder and Content Bank.
 */
export const field = (scope: Scope, label: string | RegExp, exact = true): Locator => {
  const matcher = typeof label === 'string' && exact ? new RegExp(`^\\s*${escapeRegExp(label)}:?\\s*$`) : label;
  // The `has` locator must be page-rooted; scoping it to `scope` breaks for modal scopes.
  return scope
    .locator('[data-testid="form-field-wrapper"]')
    .filter({ has: pageOf(scope).locator('label', { hasText: matcher }) })
    .filter({ visible: true })
    .first();
};

export const fillField = async (scope: Scope, label: string | RegExp, value: string): Promise<void> => {
  const input = field(scope, label).locator('input:visible, textarea:visible').first();
  await input.fill(value);
};

export const selectField = async (scope: Scope, label: string | RegExp, option: string): Promise<void> => {
  await field(scope, label).locator('input[data-select]').click();
  const optionButton = pageOf(scope)
    .locator('.tutor-portal-popover')
    .last()
    .getByRole('button', { name: option, exact: true })
    .filter({ visible: true })
    .last();
  await expect(optionButton).toBeVisible();
  await optionButton.click();
};

export const chooseOption = async (scope: Scope, label: string | RegExp, option: string): Promise<void> => {
  await field(scope, label).getByText(option, { exact: true }).click();
};

export const toggleSwitch = async (scope: Scope, label: string | RegExp, checked = true): Promise<void> => {
  const wrapper = field(scope, label);
  const input = wrapper.locator('input[data-input][type="checkbox"]').first();
  if ((await input.isChecked()) !== checked) {
    // Clicking the visible label toggles the styled switch; clicking the input itself can hit an overlay.
    await wrapper.locator('label').first().click();
  }
  await expect(input).toBeChecked({ checked });
};

export const toggleCheckbox = async (scope: Scope, label: string | RegExp, checked = true): Promise<void> => {
  const wrapper = scope
    .locator('label')
    .filter({ has: pageOf(scope).locator('input[type="checkbox"]') })
    .filter({ hasText: label })
    .filter({ visible: true })
    .first();
  const input = wrapper.locator('input[type="checkbox"]').first();
  const isChecked = await input.isChecked();
  if (isChecked !== checked) {
    await wrapper.click();
  }
  await expect(input).toBeChecked({ checked });
};

export const fillRichText = async (page: Page, scope: Scope, html: string): Promise<void> => {
  const textarea = scope.locator('textarea[data-testid="tutor-tinymce"]').first();
  await expect(textarea).toBeAttached();
  const id = await textarea.getAttribute('id');
  expect(id).toBeTruthy();

  await expect.poll(() => page.evaluate((editorId) => !!window.tinymce?.get(editorId), id as string)).toBe(true);

  await page.evaluate(
    ({ editorId, content }) => {
      const editor = window.tinymce.get(editorId);
      editor.setContent(content);
      editor.fire('change');
    },
    { editorId: id as string, content: html },
  );
};

const MONTHS = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];

const isoDay = (date: Date): string => {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
};

/** Pick a date in the shared DayPicker popover opened by a date input. */
export const pickDate = async (page: Page, trigger: Locator, date: Date): Promise<void> => {
  await trigger.click();
  const picker = page.locator('.rdp-root:visible').last();
  await expect(picker).toBeVisible();

  const yearSelect = picker.locator('.rdp-years_dropdown');
  if (await yearSelect.count()) {
    await yearSelect.selectOption({ value: String(date.getFullYear()) });
  }
  const monthSelect = picker.locator('.rdp-months_dropdown');
  if (await monthSelect.count()) {
    await monthSelect.selectOption({ label: MONTHS[date.getMonth()] });
  }

  const dayButton = picker.locator(`[data-day="${isoDay(date)}"] button`);
  await expect(dayButton).toBeVisible();
  await dayButton.click();
  await expect(picker).toBeHidden();
};

/** Pick a date a number of days from today. */
export const pickDateFromNow = async (page: Page, trigger: Locator, days: number): Promise<Date> => {
  const date = new Date();
  date.setDate(date.getDate() + days);
  await pickDate(page, trigger, date);
  return date;
};

/**
 * Open the WordPress media modal and select a file. Files already in the media
 * library are reused; pass `reuse: false` when the upload itself is under test.
 */
export const uploadMedia = async (
  page: Page,
  scope: Scope,
  file: string,
  { reuse = true }: { reuse?: boolean } = {},
): Promise<void> => {
  const replaceButton = scope.getByTestId('replace-media').filter({ visible: true }).first();
  if (await replaceButton.isVisible().catch(() => false)) {
    await replaceButton.click();
  } else {
    const uploadButton = scope.getByTestId('upload-media').filter({ visible: true }).first();
    if (await uploadButton.isVisible().catch(() => false)) {
      await uploadButton.click();
    } else {
      await scope
        .getByRole('button', { name: /Upload (?:Image|Thumbnail|Attachment|Media|Video|File)/i })
        .filter({ visible: true })
        .first()
        .click();
    }
  }

  const modal = page.locator('.media-modal').filter({ visible: true }).last();
  await expect(modal).toBeVisible();

  const selectButton = modal.locator('button.media-button-select');
  const uploadTab = modal.locator('.media-frame-router .media-menu-item', { hasText: /Upload files/i }).first();

  if (reuse) {
    const libraryTab = modal.locator('.media-frame-router .media-menu-item', { hasText: /Media Library/i }).first();
    if (await libraryTab.isVisible().catch(() => false)) {
      await libraryTab.click();
    }

    const search = modal.locator('#media-search-input');
    if (await search.isVisible().catch(() => false)) {
      const query = path.basename(file, path.extname(file));
      const searchResponse = page.waitForResponse((response) => {
        const body = decodeURIComponent(response.request().postData() ?? '');
        return body.includes('action=query-attachments') && body.includes(`query[s]=${query}`);
      });
      await search.fill(query);
      await searchResponse;

      const existing = modal.locator('.attachments .attachment').first();
      const emptyState = modal.getByText('No items found.');
      const resultsReady = async () =>
        (await existing.count()) > 0 || (await emptyState.isVisible().catch(() => false));
      await expect.poll(resultsReady).toBe(true);

      if (await existing.count()) {
        await existing.click();
        await expect(selectButton).toBeEnabled();
        await selectButton.click();
        await expect(modal).toBeHidden();
        return;
      }

      await search.fill('');
    }
  }

  if (await uploadTab.isVisible().catch(() => false)) {
    await uploadTab.click();
  }

  await modal.locator('input[type="file"]').first().setInputFiles(file);
  await expect(selectButton).toBeEnabled({ timeout: 20_000 });
  await selectButton.click();
  await expect(modal).toBeHidden();
};

/** Add a video through the shared "Add from URL" popover. */
export const setVideoUrl = async (
  page: Page,
  scope: Scope,
  label: string,
  url: string,
  source = 'Embedded',
): Promise<void> => {
  const wrapper = field(scope, label);
  await wrapper.getByTestId('add-from-url').click();

  const popover = page
    .locator('.tutor-portal-popover')
    .filter({ has: page.getByTestId('submit-url') })
    .last();
  const sourceInput = popover.locator('input[data-select]');
  await expect(sourceInput)
    .toBeVisible({ timeout: 5000 })
    .catch(async () => {
      // The first click can be consumed while the course form hydrates.
      await wrapper.getByTestId('add-from-url').click();
      await expect(sourceInput).toBeVisible();
    });
  if (await sourceInput.isVisible().catch(() => false)) {
    const current = await sourceInput.inputValue();
    if (current !== source) {
      const sourceOption = page.getByRole('button', { name: source, exact: true }).filter({ visible: true }).last();
      // Toggling the select can need more than one attempt inside a modal's focus trap.
      for (let attempt = 0; attempt < 4 && (await sourceOption.count()) === 0; attempt++) {
        await sourceInput.evaluate((element) => (element as HTMLElement).click());
        await page.waitForTimeout(200);
      }
      await expect(sourceOption).toBeVisible();
      // Picking a source re-renders the popover, detaching the option mid-click.
      await sourceOption.evaluate((element) => (element as HTMLElement).click());
    }
  }

  await popover.locator('textarea').first().fill(url);
  await popover.getByTestId('submit-url').click();
  await expect(wrapper.getByTestId('media-preview')).toBeVisible();
};

export interface LessonFormInput {
  title: string;
  content?: string;
  image?: string;
  video?: string;
  attachments?: string[];
  duration?: { hours?: number; minutes?: number; seconds?: number };
  preview?: boolean;
  dripDays?: number;
}

/**
 * Fill a lesson editor modal. The caller owns the add trigger, save button, and
 * list assertion, so Course Builder and Content Bank can share the form itself.
 */
export const fillLessonForm = async (page: Page, modal: Locator, input: LessonFormInput): Promise<void> => {
  await modal.locator('input[name="title"]').fill(input.title);
  if (input.content) {
    await fillRichText(page, modal, input.content);
  }

  if (input.image) {
    await uploadMedia(page, field(modal, 'Featured Image'), input.image);
  }

  if (input.video) {
    await setVideoUrl(page, modal, 'Video', input.video);
  }

  if (input.duration) {
    const values = input.duration;
    if (values.hours !== undefined) {
      await modal.locator('input[name="duration.hour"]').fill(String(values.hours));
    }
    if (values.minutes !== undefined) {
      await modal.locator('input[name="duration.minute"]').fill(String(values.minutes));
    }
    if (values.seconds !== undefined) {
      await modal.locator('input[name="duration.second"]').fill(String(values.seconds));
    }
  }

  if (input.dripDays !== undefined) {
    await fillField(modal, /^Available after days$/, String(input.dripDays));
  }

  if (input.attachments?.length) {
    for (const attachment of input.attachments) {
      await uploadMedia(page, field(modal, 'Exercise Files'), attachment);
    }
  }

  if (input.preview !== undefined) {
    await toggleSwitch(modal, 'Lesson Preview', input.preview);
  }
};

export interface AssignmentFormInput {
  title: string;
  content?: string;
  attachments?: string[];
  totalPoints?: number;
  passPoints?: number;
  timeLimit?: { value: number; unit: 'Weeks' | 'Days' | 'Hours' };
  deadlineFromStart?: boolean;
  fileUploadLimit?: number;
  maxFileSize?: number;
  allowResubmission?: boolean;
  attemptsAllowed?: number;
  dripDays?: number;
}

/**
 * Fill an assignment editor modal. Course Builder-only controls are optional so
 * the Content Bank assignment form can reuse the shared fields.
 */
export const fillAssignmentForm = async (page: Page, modal: Locator, input: AssignmentFormInput): Promise<void> => {
  await modal.locator('input[name="title"]').fill(input.title);
  if (input.content) {
    await fillRichText(page, modal, input.content);
  }
  if (input.attachments?.length) {
    const uploader = modal
      .locator('[data-testid="form-field-wrapper"]')
      .filter({
        has: page.getByTestId('upload-media'),
      })
      .last();
    for (const attachment of input.attachments) {
      await uploadMedia(page, uploader, attachment);
    }
  }
  if (input.timeLimit) {
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
  if (input.deadlineFromStart !== undefined) {
    await toggleSwitch(modal, 'Set Deadline From Assignment Start Time', input.deadlineFromStart);
  }
  if (input.totalPoints !== undefined) {
    await modal.locator('input[name="total_mark"]').fill(String(input.totalPoints));
  }
  if (input.passPoints !== undefined) {
    await modal.locator('input[name="pass_mark"]').fill(String(input.passPoints));
  }
  if (input.fileUploadLimit !== undefined) {
    await modal.locator('input[name="upload_files_limit"]').fill(String(input.fileUploadLimit));
  }
  if (input.maxFileSize !== undefined) {
    await modal.locator('input[name="upload_file_size_limit"]').fill(String(input.maxFileSize));
  }
  if (input.allowResubmission !== undefined) {
    await toggleSwitch(modal, 'Allow Assignment Resubmission', input.allowResubmission);
  }
  if (input.attemptsAllowed !== undefined) {
    await modal.locator('input[name="attempts_allowed"]').fill(String(input.attemptsAllowed));
  }
  if (input.dripDays !== undefined) {
    await fillField(modal, /^Available after days$/, String(input.dripDays));
  }
};
