import { openSettingsTab } from '../support/builder';
import {
  chooseOption,
  escapeRegExp,
  field,
  fillLessonForm,
  type LessonFormInput,
  type Scope,
  toggleCheckbox,
  uploadMedia,
} from '../support/content-authoring';
import { expect, type Locator, type Page } from '../support/test';
import { backendUrls } from '../support/urls';

export interface QuizInput {
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

/**
 * Page Object Model for Tutor LMS Course Builder.
 * Encapsulates multi-step course authoring, curriculum generation, and persistence.
 */
export class CourseBuilderPage {
  readonly page: Page;
  readonly submitButton: Locator;
  readonly tracker: Locator;
  readonly saveToast: Locator;

  private currentCourseId: number | null = null;

  constructor(page: Page) {
    this.page = page;
    this.submitButton = page.getByTestId('course-builder-submit-button');
    this.tracker = page.locator('[data-testid="tutor-tracker"]');
    this.saveToast = page.getByTestId('tutor-toast').filter({ hasText: /course updated successfully/i });
  }

  get courseId(): number | null {
    return this.currentCourseId;
  }

  /** Start a new course with the builder and return its ID. */
  async createNew(): Promise<number> {
    await this.page.goto(backendUrls.tutorDashboard);
    const detailsPromise = this.page.waitForResponse(
      (res) =>
        res.url().includes('admin-ajax.php') &&
        (res.request().postData() ?? '').includes('tutor_course_details') &&
        res.ok(),
      { timeout: 30_000 },
    );
    await this.page.locator('.tutor-create-new-course:visible').first().click();
    await expect(this.page).toHaveURL(/course_id=\d+/, { timeout: 30_000 });
    await expect(this.submitButton).toBeVisible();
    await detailsPromise;
    await expect(field(this.page, 'Title').locator('input:visible, textarea:visible').first()).toBeVisible();

    const courseId = Number(new URL(this.page.url()).searchParams.get('course_id'));
    expect(courseId).toBeGreaterThan(0);
    this.currentCourseId = courseId;
    return courseId;
  }

  /** Open an existing course in the builder. */
  async open(courseId: number): Promise<void> {
    this.currentCourseId = courseId;
    const detailsPromise = this.page.waitForResponse(
      (res) =>
        res.url().includes('admin-ajax.php') &&
        (res.request().postData() ?? '').includes('tutor_course_details') &&
        res.ok(),
      { timeout: 30_000 },
    );
    await this.page.goto(`${backendUrls.courseBuilder}&course_id=${courseId}`);
    await expect(this.submitButton).toBeVisible();
    await detailsPromise;
    // The details query resets the form after mount. Editing during that reset can
    // detach the submit button and silently drop the save click.
    await expect(field(this.page, 'Title').locator('input:visible, textarea:visible').first()).not.toHaveValue('');
  }

  /** Navigate between Basics / Curriculum / Additional steps. */
  async openStep(step: 'Basics' | 'Curriculum' | 'Additional'): Promise<void> {
    const stepButton = this.tracker.locator('button').filter({ hasText: step });
    await stepButton.click();
    await expect(stepButton).toBeVisible();
  }

  /** Open a tab inside the settings section. */
  async openSettingsTab(tabName: 'General' | 'Content Drip' | 'Enrollment'): Promise<void> {
    await openSettingsTab(this.page, tabName);
  }

  /** Set the course title. */
  async setTitle(title: string): Promise<void> {
    const input = field(this.page, 'Title').locator('input:visible, textarea:visible').first();
    await input.fill(title);
  }

  /** Save the course from the header and wait for the success toast. */
  async save(): Promise<void> {
    const savePromise = this.page.waitForResponse(
      (res) =>
        res.url().includes('admin-ajax.php') &&
        (res.request().postData() ?? '').includes('tutor_update_course') &&
        res.ok(),
      { timeout: 20_000 },
    );
    await this.submitButton.click();
    await savePromise;
    await expect(this.saveToast.last()).toBeVisible({ timeout: 20_000 });
  }

  /** Select an existing category by its visible label. */
  async selectCategory(name: string, categoryField = field(this.page, 'Categories')): Promise<void> {
    await categoryField.getByText(name, { exact: true }).click();
  }

  /** Create a new tag and select it. Tag names must be unique per run. */
  async addTag(name: string, tagField = field(this.page, 'Tags')): Promise<void> {
    const input = tagField.locator('input').first();
    await input.click();
    await input.fill(name);

    const addButton = this.page.getByRole('button', { name: new RegExp(`Add\\s+${escapeRegExp(name)}`) }).last();
    await expect(addButton).toBeVisible();
    await addButton.click();
    await expect(tagField.getByText(name, { exact: true })).toBeVisible();
  }

  /** Change the course author through the author picker and confirmation modal. */
  async changeAuthor(authorName: string): Promise<void> {
    const authorField = field(this.page, 'Author');
    await authorField.locator('button').first().click();

    const popover = this.page.locator('.tutor-portal-popover').last();
    const search = popover.locator('input').first();
    await expect(search).toBeVisible();
    await search.fill(authorName);
    await popover.getByRole('button', { name: new RegExp(escapeRegExp(authorName)) }).click();

    const modal = this.page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
    await expect(modal.getByText('Are you sure?')).toBeVisible();
    await modal.getByRole('button', { name: 'Yes, change author' }).click();
    await expect(modal).toBeHidden();
    await expect(authorField).toContainText(authorName);
  }

  /** Fill the hour/minute pair under Total Course Duration. */
  async fillDuration(hours: number, minutes: number, scope: Scope = this.page): Promise<void> {
    const wrapper = field(scope, 'Total Course Duration');
    const parent = wrapper.locator('..');
    await parent.locator('input').nth(0).fill(String(hours));
    await parent.locator('input').nth(1).fill(String(minutes));
  }

  /** Choose a pricing model radio option. */
  async choosePricingModel(model: 'Free' | 'Paid', scope: Scope = this.page): Promise<void> {
    await chooseOption(scope, 'Pricing Model', model);
  }

  /** Upload a file to the course-level Attachments box on the Additional page. */
  async uploadAttachment(file: string): Promise<void> {
    const uploader = this.page
      .locator('[data-testid="form-field-wrapper"]')
      .filter({ has: this.page.getByRole('button', { name: 'Upload Attachment', exact: true }) })
      .filter({ visible: true })
      .last();
    await uploadMedia(this.page, uploader, file);
  }

  /** Add a topic block under the curriculum step. */
  async addTopic(title: string, summary = ''): Promise<void> {
    await this.page.locator('[data-testid="add-topic"]:visible').first().click();
    const titleInput = this.page.locator('input[name="title"]:visible').last();
    await titleInput.fill(title);
    if (summary) {
      await this.page.locator('textarea[name="summary"]:visible').last().fill(summary);
    }
    await this.page.locator('[data-testid="save-topic"]:visible').last().click();
    await expect(
      this.page
        .getByTestId('tutor-toast')
        .filter({ hasText: /topic saved successfully/i })
        .last(),
    ).toBeVisible();
    // The topic editor stays inline; wait for edit mode to close before adding content.
    await expect(this.page.getByTestId('save-topic')).toBeHidden();
    await expect(this.page.locator(`text="${title}"`).first()).toBeVisible();
  }

  /** Add a lesson inside the current topic. */
  async addLesson(input: LessonFormInput): Promise<void> {
    await this.page.locator('[data-testid="add-lesson"]:visible').first().click();
    const modal = this.page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
    await expect(modal).toBeVisible();

    await fillLessonForm(this.page, modal, input);

    // Clear any hover overlay from the media previews before using the header action.
    await this.page.mouse.move(0, 0);

    await this.page.getByTestId('save-lesson').click();
    await expect(modal).toBeHidden();
    await expect(this.page.locator(`text="${input.title}"`).first()).toBeVisible();
  }

  /** Add a quiz with configuration and settings. */
  async addQuiz(input: QuizInput): Promise<void> {
    await this.page.locator('[data-testid="add-quiz"]:visible').first().click();
    const modal = this.page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
    await expect(modal).toBeVisible();

    const titleInput = modal.locator('textarea[name="quiz_title"], input[name="quiz_title"]').first();
    await titleInput.fill(input.title);
    if (input.description) {
      await modal.locator('textarea[name="quiz_description"]').fill(input.description);
    }
    const saveTitle = this.page.getByTestId('save-quiz-title');
    if (await saveTitle.isVisible()) {
      await saveTitle.click();
    }

    await modal.getByTestId('add-question').click();
    const picker = this.page.locator('.tutor-portal-popover').last();
    const trueFalse = picker.getByRole('button', { name: 'True/False', exact: true }).filter({ visible: true });
    await expect(trueFalse).toBeVisible();
    await trueFalse.click();
    await this.page.getByTestId('quiz-next').click();

    const settingsTab = modal.getByRole('tab', { name: 'Settings' });
    if (await settingsTab.isVisible()) {
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
      const unit = this.page
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

    await this.page.getByTestId('save-quiz').click();
    await expect(modal).toBeHidden();
    await expect(this.page.locator(`text="${input.title}"`).first()).toBeVisible();
  }

  /** Open the edit modal for a curriculum item. */
  async openContentEditor(type: 'lesson' | 'tutor_quiz' | string): Promise<Locator> {
    await this.page.getByTestId(`edit-${type}`).first().click();
    const modal = this.page.locator('[data-testid="tutor-modal"]').filter({ visible: true }).last();
    await expect(modal).toBeVisible();
    return modal;
  }
}
