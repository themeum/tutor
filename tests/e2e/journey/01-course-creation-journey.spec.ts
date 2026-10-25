import {
  clearWooCommerceCart,
  completeTutorOrder,
  completeWcOrder,
  createCourse,
  createWcProduct,
  enrollStudent,
  getCourse,
  setOption,
  setTutorOption,
} from '@e2e/support/factories';

import { findCourseCard } from '../support/archive';
import { payWithNativeCheckout, payWithWooCommerceCheckout } from '../support/checkout';
import {
  field,
  fillField,
  fillRichText,
  selectField,
  setVideoUrl,
  toggleSwitch,
  uploadMedia,
} from '../support/content-authoring';
import {
  addLesson,
  addQuiz,
  addTag,
  addTopic,
  changeAuthor,
  choosePricingModel,
  createCourseInBuilder,
  fillCourseDuration,
  fixtures,
  openBuilderStep,
  openCourseBuilder,
  openSettingsTab,
  saveCourse,
  selectCategory,
  setCourseAlias,
} from '../support/course-builder';
import { expect, type Page, test } from '../support/test';

const fullBookedMessage =
  'This course is full right now. We limit the number of students to create an optimized and productive group dynamic.';

const enrollmentButton = (page: Page) => page.locator('.tutor-enroll-course-button');

test.describe('Course Creation Journey: Core Authoring & Enrollment @journey', () => {
  test('instructor authors a course with every core builder field and it persists, reloads, and renders', async ({
    instructorPage: page,
    studentPage,
    guestPage,
  }) => {
    test.setTimeout(240_000);
    const stamp = Date.now();
    const title = `E2E Core Course ${stamp}`;
    const slug = `e2e-core-course-${stamp}`;
    const description = `Core course description ${stamp}`;
    const tag = `core-tag-${stamp}`;
    const topicTitle = `Core Topic ${stamp}`;
    const topicSummary = `Core topic summary ${stamp}`;
    const lessonTitle = `Core Lesson ${stamp}`;
    const lessonContent = `Core lesson content ${stamp}`;
    const publicLessonTitle = `Core Public Lesson ${stamp}`;
    const publicLessonContent = `Core public lesson content ${stamp}`;
    const quizTitle = `Core Quiz ${stamp}`;
    const quizDescription = `Core quiz description ${stamp}`;
    const benefitOne = `Core benefit one ${stamp}`;
    const benefitTwo = `Core benefit two ${stamp}`;
    const audience = `Core audience ${stamp}`;
    const material = `Core material ${stamp}`;
    const requirement = `Core requirement ${stamp}`;

    const previousQna = setTutorOption('enable_q_and_a_on_course', 'on');
    const previousExpiry = setTutorOption('enrollment_expiry_enabled', 'on');
    const previousDuration = setTutorOption('enable_course_duration', 'on');

    try {
      const courseId = await createCourseInBuilder(page);

      await fillField(page, 'Title', title);
      await setCourseAlias(page, slug);
      await fillRichText(page, page, `<p>${description}</p>`);
      await selectCategory(page, 'Web Development');
      await addTag(page, tag);
      await uploadMedia(page, field(page, 'Featured Image'), fixtures.image, { reuse: false });
      await setVideoUrl(page, page, 'Intro Video', '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>');
      await selectField(page, 'Visibility', 'Public');
      await selectField(page, 'Difficulty Level', 'Intermediate');
      await choosePricingModel(page, 'Free');
      await toggleSwitch(page, 'Public Course', true);
      await toggleSwitch(page, 'Q&A', false);

      await openSettingsTab(page, 'Enrollment');
      await fillField(page, 'Maximum Student', '0');
      await fillField(page, 'Enrollment Expiration', '15');

      await openBuilderStep(page, 'Curriculum');
      await addTopic(page, topicTitle, topicSummary);
      await addLesson(page, {
        title: lessonTitle,
        content: `<p>${lessonContent}</p>`,
        image: fixtures.image,
        video: '<iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ"></iframe>',
        attachments: [fixtures.attachment],
        duration: { hours: 1, minutes: 2, seconds: 3 },
        preview: true,
      });
      // Public Course should let anonymous visitors open this non-preview lesson.
      await addLesson(page, {
        title: publicLessonTitle,
        content: `<p>${publicLessonContent}</p>`,
      });
      await addQuiz(page, {
        title: quizTitle,
        description: quizDescription,
        passingGrade: 70,
        allowAttempts: true,
        attempts: 3,
        timeLimit: { value: 10, unit: 'Min' },
        autoStart: true,
        revealAnswers: true,
        layout: 'Full Page',
      });

      await openBuilderStep(page, 'Additional');
      await fillField(page, 'What Will I Learn?', `${benefitOne}\n${benefitTwo}`);
      await fillField(page, 'Target Audience', audience);
      await fillCourseDuration(page, 4, 15);
      await fillField(page, 'Materials Included', material);
      await fillField(page, 'Requirements/Instructions', requirement);

      await saveCourse(page);

      const course = getCourse(courseId);
      expect(course.title).toBe(title);
      expect(course.slug).toBe(slug);
      expect(course.content).toContain(description);
      expect(course.status).toBe('publish');
      expect(course.priceType).toBe('free');
      expect(course.isPublic).toBe(true);
      expect(course.qna).toBe('no');
      expect(course.level).toBe('intermediate');
      expect(course.password).toBe('');
      expect(course.thumbnailId).toBeGreaterThan(0);
      expect(JSON.stringify(course.video)).toContain('youtube');
      expect(course.categories.map((item) => item.name)).toContain('Web Development');
      expect(course.tags.map((item) => item.name)).toContain(tag);
      expect(course.benefits).toContain(benefitOne);
      expect(course.benefits).toContain(benefitTwo);
      expect(course.audience).toContain(audience);
      expect(course.materials).toContain(material);
      expect(course.requirements).toContain(requirement);
      expect(course.duration).toMatchObject({ hours: '4', minutes: '15' });
      expect(course.settings).toMatchObject({
        maximum_students: 0,
        enrollment_expiry: 15,
        course_enrollment_period: 'no',
        pause_enrollment: 'no',
      });

      const topic = course.contents.find((item) => item.title === topicTitle);
      expect(topic?.summary).toContain(topicSummary);

      const lesson = topic?.children.find((item) => item.title === lessonTitle);
      expect(lesson?.content).toContain(lessonContent);
      expect(lesson?.meta.preview).toBeTruthy();
      expect(lesson?.meta.attachments).toHaveLength(1);
      expect(lesson?.meta.video).toMatchObject({ runtime: { hours: '1', minutes: '2', seconds: '3' } });

      const publicLesson = topic?.children.find((item) => item.title === publicLessonTitle);
      expect(publicLesson?.content).toContain(publicLessonContent);
      expect(publicLesson?.meta.preview).toBeFalsy();

      const quiz = topic?.children.find((item) => item.title === quizTitle);
      expect(quiz?.content).toContain(quizDescription);
      expect(quiz?.meta.quiz_option).toMatchObject({
        passing_grade: '70',
        attempts_allowed: '3',
        time_limit: { time_type: 'minutes', time_value: '10' },
        quiz_auto_start: '1',
        enable_answer_reveal: '1',
        question_layout_view: 'question_below_each_other',
      });

      // Reloading the builder must restore every saved field.
      await page.reload();
      await openBuilderStep(page, 'Basics');
      await expect(field(page, 'Title').locator('input')).toHaveValue(title);
      await expect(page.getByTestId('course-slug')).toContainText(slug);
      await openSettingsTab(page, 'General');
      await expect(field(page, 'Public Course').locator('input[data-input][type="checkbox"]')).toBeChecked();
      await expect(field(page, 'Q&A').locator('input[data-input][type="checkbox"]')).not.toBeChecked();
      await openSettingsTab(page, 'Enrollment');
      await expect(field(page, 'Maximum Student').locator('input')).toHaveValue('0');
      await expect(field(page, 'Enrollment Expiration').locator('input')).toHaveValue('15');
      await openBuilderStep(page, 'Curriculum');
      await expect(page.getByText(topicTitle, { exact: true }).first()).toBeVisible();
      await expect(page.getByText(lessonTitle, { exact: true }).first()).toBeVisible();
      await expect(page.getByText(quizTitle, { exact: true }).first()).toBeVisible();
      await openBuilderStep(page, 'Additional');
      const durationWrapper = field(page, 'Total Course Duration').locator('..');
      await expect(durationWrapper.locator('input').nth(0)).toHaveValue('4');
      await expect(durationWrapper.locator('input').nth(1)).toHaveValue('15');
      await expect(field(page, 'What Will I Learn?').locator('textarea')).toHaveValue(new RegExp(benefitOne));

      // The course renders on the frontend for enrolled and anonymous visitors.
      await studentPage.goto(course.url);
      await expect(studentPage.locator('.tutor-course-details-title')).toContainText(title);
      await expect(studentPage.locator('.tutor-course-details-content')).toContainText(description);
      await expect(studentPage.getByText('What Will You Learn?')).toBeVisible();
      await expect(studentPage.getByText(benefitOne, { exact: true })).toBeVisible();
      await expect(studentPage.getByText(benefitTwo, { exact: true })).toBeVisible();
      await expect(studentPage.getByText(audience, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(material, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(requirement, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(topicTitle).first()).toBeVisible();
      await expect(studentPage.getByText(lessonTitle, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(publicLessonTitle, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(quizTitle, { exact: true }).first()).toBeVisible();
      await expect(studentPage.locator('iframe[src*="youtube"]').first()).toBeVisible();
      await expect(studentPage.getByText('Intermediate', { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText('Web Development', { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText(tag, { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByText('E2E Instructor', { exact: true }).first()).toBeVisible();
      await expect(studentPage.getByRole('link', { name: 'Q&A', exact: true })).toHaveCount(0);

      const sidebarMeta = studentPage.locator('.tutor-single-course-sidebar .tutor-card-footer');
      await expect(sidebarMeta).toContainText('Intermediate');
      await expect(sidebarMeta.locator('li').filter({ hasText: 'Enrollment validity' })).toContainText('15 days');
      const durationMeta = sidebarMeta.locator('li').filter({ hasText: 'Duration' });
      await expect(durationMeta).toContainText('4');
      await expect(durationMeta).toContainText('15');

      const thumbnailName =
        (course.thumbnailUrl || '')
          .split('/')
          .pop()
          ?.replace(/\.[^.]+$/, '') ?? '';
      expect(thumbnailName).not.toBe('');
      await guestPage.goto('/courses/');
      const courseCard = await findCourseCard(guestPage, title);
      await expect(courseCard.locator('img.tutor-card-image-top').first()).toHaveAttribute(
        'src',
        new RegExp(thumbnailName),
      );

      await guestPage.goto(lesson?.url ?? '');
      await expect(guestPage.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
      await expect(guestPage.getByText(lessonContent, { exact: false }).first()).toBeVisible();
      await expect(guestPage.locator('iframe[src*="youtube"]').first()).toBeVisible();
      await expect(guestPage.getByText(/Video - 1:2:3/).first()).toBeVisible();
      await expect(
        guestPage.locator('.tutor-attachment-card-title', { hasText: 'course-attachment' }).first(),
      ).toBeAttached();

      // Public Course lets a guest open a lesson that is not marked as a preview.
      await guestPage.goto(publicLesson?.url ?? '');
      await expect(guestPage.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
      await expect(guestPage.getByText(publicLessonContent).first()).toBeVisible();

      // Enabling Q&A adds the course tab; enrolling shows the expiry date.
      await openCourseBuilder(page, courseId);
      await openBuilderStep(page, 'Basics');
      await openSettingsTab(page, 'General');
      await toggleSwitch(page, 'Q&A', true);
      await saveCourse(page);
      expect(getCourse(courseId).qna).toBe('yes');

      enrollStudent({ courseId, user: 'student' });
      await studentPage.goto(course.url);
      const qnaTab = studentPage.getByRole('link', { name: 'Q&A', exact: true });
      await expect(qnaTab).toBeVisible();
      await qnaTab.click();
      await expect(studentPage.locator('#tutor-course-details-tab-questions')).toBeVisible();
      await expect(studentPage.getByText('This feature has been disabled by the administrator')).toHaveCount(0);
      await expect(studentPage.getByText(/Enrollment valid until/)).toBeVisible();

      // Quiz settings are reflected on the learning-area quiz screen.
      await studentPage.goto(quiz?.url ?? '');
      await expect(studentPage.getByText('Questions 1', { exact: true })).toBeVisible();
      await expect(studentPage.getByText('Quiz Time 10 Minutes', { exact: true })).toBeVisible();
      await expect(studentPage.getByText('Passing Grade 70%', { exact: true })).toBeVisible();
      await expect(studentPage.getByText('Total Attempts 3', { exact: true })).toBeVisible();
    } finally {
      setTutorOption('enable_q_and_a_on_course', previousQna.previous_value);
      setTutorOption('enrollment_expiry_enabled', previousExpiry.previous_value);
      setTutorOption('enable_course_duration', previousDuration.previous_value);
    }
  });

  test('password protected and private visibility options block access as configured', async ({
    instructorPage: page,
    studentPage,
  }) => {
    test.setTimeout(180_000);
    const stamp = Date.now();
    const title = `E2E Visibility Course ${stamp}`;
    const password = `core-secret-${stamp}`;
    const courseId = await createCourseInBuilder(page);

    await fillField(page, 'Title', title);
    await selectCategory(page, 'Web Development');
    await choosePricingModel(page, 'Free');
    await selectField(page, 'Visibility', 'Password Protected');
    await fillField(page, 'Password', password);
    await openBuilderStep(page, 'Curriculum');
    await addTopic(page, `Visibility Topic ${stamp}`);
    await addLesson(page, {
      title: `Visibility Lesson ${stamp}`,
      content: `<p>Visibility lesson content ${stamp}</p>`,
    });
    await saveCourse(page);

    let course = getCourse(courseId);
    expect(course.password).toBe(password);
    expect(course.status).toBe('publish');
    const lessonUrl = course.contents[0].children[0].url;

    await studentPage.goto(course.url);
    const modal = studentPage.locator('#tutor-password-protected-modal');
    await expect(modal).toBeVisible();
    await expect(modal).toContainText(/Course is locked/i);

    await modal.locator('input[name="post_password"]').fill(`wrong-${password}`);
    await modal.locator('button[type="submit"]').click();
    await studentPage.waitForLoadState('domcontentloaded');
    await expect(modal).toBeVisible();

    await modal.locator('input[name="post_password"]').fill(password);
    await modal.locator('button[type="submit"]').click();
    await studentPage.waitForLoadState('domcontentloaded');
    await expect(studentPage.locator('#tutor-password-protected-modal')).toHaveCount(0);
    await expect(studentPage.locator('.tutor-enroll-course-button')).toBeVisible();

    // Changing the same course to private must hide it from students entirely.
    await openCourseBuilder(page, courseId);
    await selectField(page, 'Visibility', 'Private');
    await saveCourse(page);

    course = getCourse(courseId);
    expect(course.status).toBe('private');
    expect(course.password).toBe('');

    const courseResponse = await studentPage.goto(course.url);
    expect(courseResponse?.status()).toBe(404);
    await studentPage.goto(lessonUrl);
    await expect(studentPage.getByRole('img', { name: 'Permission Denied' })).toBeVisible();
    await expect(studentPage.getByText(`Visibility lesson content ${stamp}`)).toHaveCount(0);
  });

  test('administrator changes the course author through the builder', async ({ adminPage }) => {
    test.setTimeout(120_000);
    const stamp = Date.now();
    const course = createCourse({
      title: `E2E Author Change Course ${stamp}`,
      author: 'instructor',
    });

    expect(getCourse(course.courseId).author?.login).toBe('instructor');

    await openCourseBuilder(adminPage, course.courseId);
    await changeAuthor(adminPage, 'E2E Admin');
    await saveCourse(adminPage);

    const updated = getCourse(course.courseId);
    expect(updated.author?.login).toBe('admin');
    expect(updated.author?.name).toBe('E2E Admin');

    await adminPage.goto(updated.url);
    await expect(adminPage.getByText('E2E Admin', { exact: true }).first()).toBeVisible();
  });

  test('maximum student limit blocks enrollment at the cap and 0 means unlimited', async ({
    instructorPage: page,
    studentPage,
    student2Page,
  }) => {
    test.setTimeout(180_000);
    const stamp = Date.now();
    const title = `E2E Enrollment Limit Course ${stamp}`;
    const lessonContent = `Enrollment limit lesson content ${stamp}`;
    const courseId = await createCourseInBuilder(page);

    await fillField(page, 'Title', title);
    await selectCategory(page, 'Web Development');
    await choosePricingModel(page, 'Free');
    await openSettingsTab(page, 'Enrollment');
    await fillField(page, 'Maximum Student', '1');
    await openBuilderStep(page, 'Curriculum');
    await addTopic(page, `Enrollment Topic ${stamp}`);
    await addLesson(page, {
      title: `Enrollment Lesson ${stamp}`,
      content: `<p>${lessonContent}</p>`,
    });
    await saveCourse(page);

    const course = getCourse(courseId);
    expect(course.settings).toMatchObject({ maximum_students: 1 });
    const lessonUrl = course.contents[0].children[0].url;

    // Unenrolled visitors cannot reach the lesson.
    await student2Page.goto(lessonUrl);
    await expect(student2Page.getByRole('img', { name: 'Permission Denied' })).toBeVisible();
    await expect(student2Page.getByText(lessonContent)).toHaveCount(0);

    // The first student takes the only seat.
    await studentPage.goto(course.url);
    await expect(enrollmentButton(studentPage)).toBeVisible();
    await enrollmentButton(studentPage).click();
    await studentPage.waitForLoadState('domcontentloaded');
    await expect(
      studentPage.locator('a:has-text("Start Learning"), a:has-text("Continue Learning")').first(),
    ).toBeVisible();

    // The second student sees the fully booked warning and no enrollment form.
    await student2Page.goto(course.url);
    await expect(student2Page.getByText(fullBookedMessage, { exact: true })).toBeVisible();
    await expect(enrollmentButton(student2Page)).toHaveCount(0);

    // Raising the cap to 0 (unlimited) opens enrollment again.
    await openCourseBuilder(page, courseId);
    await openSettingsTab(page, 'Enrollment');
    await fillField(page, 'Maximum Student', '0');
    await saveCourse(page);
    expect(getCourse(courseId).settings).toMatchObject({ maximum_students: 0 });

    await student2Page.reload();
    await expect(student2Page.getByText(fullBookedMessage, { exact: true })).toHaveCount(0);
    await expect(enrollmentButton(student2Page)).toBeVisible();
    await enrollmentButton(student2Page).click();
    await student2Page.waitForLoadState('domcontentloaded');
    await expect(
      student2Page.locator('a:has-text("Start Learning"), a:has-text("Continue Learning")').first(),
    ).toBeVisible();

    await studentPage.reload();
    await expect(
      studentPage.locator('a:has-text("Start Learning"), a:has-text("Continue Learning")').first(),
    ).toBeVisible();

    await student2Page.goto(lessonUrl);
    await expect(student2Page.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
    await expect(student2Page.getByText(lessonContent).first()).toBeVisible();
  });

  test('native paid purchase completes the order and grants course access', async ({
    instructorPage: page,
    student2Page,
  }) => {
    test.setTimeout(180_000);
    const stamp = Date.now();
    const title = `E2E Native Paid Course ${stamp}`;
    const lessonContent = `Native paid lesson content ${stamp}`;
    setTutorOption('monetize_by', 'tutor');
    const courseId = await createCourseInBuilder(page);

    await fillField(page, 'Title', title);
    await selectCategory(page, 'Web Development');
    await choosePricingModel(page, 'Paid');
    await fillField(page, 'Regular Price', '39');
    await fillField(page, 'Sale Price', '29');
    await openBuilderStep(page, 'Curriculum');
    await addTopic(page, `Native Paid Topic ${stamp}`);
    await addLesson(page, {
      title: `Native Paid Lesson ${stamp}`,
      content: `<p>${lessonContent}</p>`,
    });
    await saveCourse(page);

    const course = getCourse(courseId);
    expect(course.priceType).toBe('paid');
    expect(course.price).toBe('39');
    expect(course.salePrice).toBe('29');
    const lessonUrl = course.contents[0].children[0].url;

    await student2Page.goto(lessonUrl);
    await expect(student2Page.getByRole('img', { name: 'Permission Denied' })).toBeVisible();

    await student2Page.goto(course.url);
    await expect(student2Page.locator('.tutor-course-sidebar-card-pricing')).toContainText('29');
    await expect(student2Page.locator('.tutor-course-sidebar-card-pricing')).toContainText('39');

    const orderId = await payWithNativeCheckout(student2Page, `native-${stamp}@example.test`);
    const completed = completeTutorOrder(orderId);
    expect(completed.orderStatus).toBe('completed');
    expect(completed.paymentStatus).toBe('paid');

    await student2Page.goto(course.url);
    await expect(
      student2Page.locator('a:has-text("Start Learning"), a:has-text("Continue Learning")').first(),
    ).toBeVisible();

    await student2Page.goto(lessonUrl);
    await expect(student2Page.getByText(lessonContent).first()).toBeVisible();
  });

  test('woocommerce product select purchase completes checkout and grants course access', async ({
    instructorPage: page,
    studentPage,
  }) => {
    test.setTimeout(240_000);
    const stamp = Date.now();
    const title = `E2E WooCommerce Course ${stamp}`;
    const productTitle = `E2E WooCommerce Product ${stamp}`;
    const lessonContent = `WooCommerce lesson content ${stamp}`;

    // Cash on delivery is the only gateway available in this environment.
    const previousComingSoon = setOption('woocommerce_coming_soon', 'no');
    setOption('woocommerce_cod_settings', {
      enabled: 'yes',
      title: 'Cash on delivery',
      description: 'Pay with cash upon delivery.',
      instructions: 'Pay with cash upon delivery.',
      enable_for_methods: [],
      enable_for_virtual: 'yes',
    });

    const previousMonetize = setTutorOption('monetize_by', 'wc');
    try {
      const product = createWcProduct({ title: productTitle, price: 65 });
      const courseId = await createCourseInBuilder(page);

      await fillField(page, 'Title', title);
      await selectCategory(page, 'Web Development');
      await choosePricingModel(page, 'Paid');
      await selectField(page, 'Select product', product.title);
      await fillField(page, 'Regular Price', String(product.price));
      await openBuilderStep(page, 'Curriculum');
      await addTopic(page, `WooCommerce Topic ${stamp}`);
      await addLesson(page, {
        title: `WooCommerce Lesson ${stamp}`,
        content: `<p>${lessonContent}</p>`,
      });
      await saveCourse(page);

      const course = getCourse(courseId);
      expect(course.priceType).toBe('paid');
      expect(course.productId).toBe(String(product.productId));
      const lessonUrl = course.contents[0].children[0].url;

      clearWooCommerceCart(3);

      await studentPage.goto(course.url);
      const addToCart = studentPage.locator(
        'button.single_add_to_cart_button, .tutor-btn:has-text("Add to cart"), form.cart button[type="submit"]',
      );
      await expect(addToCart.first()).toBeVisible();
      await addToCart.first().click();
      await studentPage.waitForLoadState('domcontentloaded');
      await expect(studentPage.locator('.tutor-woocommerce-view-cart, a:has-text("View Cart")').first()).toBeVisible();

      await studentPage.goto('/cart/');
      await expect(studentPage.locator('.wp-block-woocommerce-cart, form.woocommerce-cart-form').first()).toBeVisible();
      await expect(studentPage.getByText(title, { exact: true }).first()).toBeVisible();

      const orderId = await payWithWooCommerceCheckout(studentPage, `wc-${stamp}@example.test`);
      const completed = completeWcOrder(orderId);
      expect(completed.status).toBe('completed');

      await studentPage.goto(course.url);
      await expect(
        studentPage.locator('a:has-text("Start Learning"), a:has-text("Continue Learning")').first(),
      ).toBeVisible();

      await studentPage.goto(lessonUrl);
      await expect(studentPage.getByText(lessonContent).first()).toBeVisible();
    } finally {
      setTutorOption('monetize_by', previousMonetize.previous_value);
      setOption('woocommerce_coming_soon', previousComingSoon.previous_value);
    }
  });
});
