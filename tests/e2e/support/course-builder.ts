import { CourseBuilderPage, type QuizInput } from '../pages/CourseBuilderPage';
import { field, type LessonFormInput, type Scope } from './content-authoring';
import { type Locator, type Page } from './test';

export { CourseBuilderPage, type QuizInput };

/** Upload a file to the course-level Attachments box on the Additional page. */
export const uploadCourseAttachment = async (page: Page, file: string): Promise<void> => {
  return new CourseBuilderPage(page).uploadAttachment(file);
};

/** Select an existing category by its visible label. */
export const selectCategory = async (
  page: Page,
  name: string,
  categoryField = field(page, 'Categories'),
): Promise<void> => {
  return new CourseBuilderPage(page).selectCategory(name, categoryField);
};

/** Create a new tag and select it. Tag names must be unique per run. */
export const addTag = async (page: Page, name: string, tagField = field(page, 'Tags')): Promise<void> => {
  return new CourseBuilderPage(page).addTag(name, tagField);
};

/** Change the course author through the author picker and confirmation modal. */
export const changeAuthor = async (page: Page, authorName: string): Promise<void> => {
  return new CourseBuilderPage(page).changeAuthor(authorName);
};

/** Fill the hour/minute pair under Total Course Duration. */
export const fillCourseDuration = async (scope: Scope, hours: number, minutes: number): Promise<void> => {
  // If scope is a Page, delegate; otherwise use helper logic directly
  if ('getByTestId' in scope && 'locator' in scope && typeof (scope as Page).goto === 'function') {
    return new CourseBuilderPage(scope as Page).fillDuration(hours, minutes, scope);
  }
  const wrapper = field(scope, 'Total Course Duration');
  const parent = wrapper.locator('..');
  await parent.locator('input').nth(0).fill(String(hours));
  await parent.locator('input').nth(1).fill(String(minutes));
};

/** Choose a pricing model radio option. */
export const choosePricingModel = async (scope: Scope, model: 'Free' | 'Paid'): Promise<void> => {
  if ('getByTestId' in scope && 'locator' in scope && typeof (scope as Page).goto === 'function') {
    return new CourseBuilderPage(scope as Page).choosePricingModel(model, scope);
  }
  const { chooseOption } = await import('./content-authoring');
  await chooseOption(scope, 'Pricing Model', model);
};

/** Start a new course with the builder and return its ID. */
export const createCourseInBuilder = async (page: Page): Promise<number> => {
  return new CourseBuilderPage(page).createNew();
};

/** Open an existing course in the builder. */
export const openCourseBuilder = async (page: Page, courseId: number): Promise<void> => {
  return new CourseBuilderPage(page).open(courseId);
};

/** Navigate between Basics / Curriculum / Additional. */
export const openBuilderStep = async (page: Page, step: 'Basics' | 'Curriculum' | 'Additional'): Promise<void> => {
  return new CourseBuilderPage(page).openStep(step);
};

/** Save the course from the header and wait for the success toast. */
export const saveCourse = async (page: Page): Promise<void> => {
  return new CourseBuilderPage(page).save();
};

export const addTopic = async (page: Page, title: string, summary = ''): Promise<void> => {
  return new CourseBuilderPage(page).addTopic(title, summary);
};

export const addLesson = async (page: Page, input: LessonFormInput): Promise<void> => {
  return new CourseBuilderPage(page).addLesson(input);
};

export const addQuiz = async (page: Page, input: QuizInput): Promise<void> => {
  return new CourseBuilderPage(page).addQuiz(input);
};

/** Open the edit modal for a curriculum item. */
export const openContentEditor = async (page: Page, type: 'lesson' | 'tutor_quiz' | string): Promise<Locator> => {
  return new CourseBuilderPage(page).openContentEditor(type);
};
