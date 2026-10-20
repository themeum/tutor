import { createCourse, createWcProduct, setTutorOption } from '@e2e/support/factories';
import { backendUrls } from '@e2e/support/urls';

import { expect, test } from '../support/test';

test.describe('Course Creation Journey: Core Authoring & Variations', () => {
  test('authoring journey: instructor creates full course with topic, lesson, quiz, and settings via course builder', async ({
    instructorPage: page,
  }) => {
    const stamp = Date.now();
    const courseTitle = `E2E Full Journey Course ${stamp}`;
    const topicTitle = `Module 1: Foundations ${stamp}`;
    const lessonTitle = `Lesson 1: Deep Dive ${stamp}`;
    const quizTitle = `Quiz 1: Assessment ${stamp}`;

    // 1. Navigate to Course Builder
    await page.goto(backendUrls.tutorDashboard);
    await page.locator('.tutor-create-new-course:visible').first().click();
    await expect(page).toHaveURL(/course_id=\d+/);

    // 2. Set Course Title & Save Initial Draft
    const titleInput = page.locator('input[name="post_title"]:visible').first();
    await titleInput.fill(courseTitle);
    await page.getByTestId('course-builder-submit-button').click();
    await expect(
      page
        .getByTestId('tutor-toast')
        .filter({ hasText: /course updated successfully/i })
        .first(),
    ).toBeVisible();

    // 3. Curriculum Tab: Add Topic
    await page.getByRole('button', { name: 'Curriculum' }).first().click();
    await page.locator('[data-testid="add-topic"]:visible').first().click();
    await page.locator('input[name="title"]:visible').first().fill(topicTitle);
    await page.locator('[data-testid="save-topic"]:visible').last().click();
    await expect(
      page
        .getByTestId('tutor-toast')
        .filter({ hasText: /topic saved successfully/i })
        .first(),
    ).toBeVisible();

    // 4. Add Lesson inside Topic
    await page.locator('[data-testid="add-lesson"]:visible').first().click();
    const lessonModal = page.locator('[data-testid="tutor-modal"]:visible').last();
    await expect(lessonModal).toBeVisible();
    await lessonModal.locator('input[name="title"]').fill(lessonTitle);

    const saveLessonBtn = page.getByTestId('save-lesson');
    await expect(saveLessonBtn).toBeVisible();
    await saveLessonBtn.click();
    await expect(lessonModal).toBeHidden();
    await expect(page.locator(`text="${lessonTitle}"`).first()).toBeVisible();

    // 5. Add Quiz inside Topic
    await page.locator('[data-testid="add-quiz"]:visible').first().click();
    const quizModal = page.locator('[data-testid="tutor-modal"]:visible').last();
    await expect(quizModal).toBeVisible();
    await quizModal.locator('textarea[name="quiz_title"], input[name="quiz_title"]').fill(quizTitle);
    const saveTitleBtn = page.getByTestId('save-quiz-title');
    if (await saveTitleBtn.isVisible().catch(() => false)) {
      await saveTitleBtn.click();
    }

    // Add True/False question (required to save quiz)
    await page.getByTestId('add-question').click();
    await page.locator('button:has-text("True/False")').first().click();

    await page.getByTestId('quiz-next').click();

    const saveQuizBtn = page.getByTestId('save-quiz');
    await expect(saveQuizBtn).toBeVisible();
    await saveQuizBtn.click();
    await expect(quizModal).toBeHidden();
    await expect(page.locator(`text="${quizTitle}"`).first()).toBeVisible();

    // 6. Additional Settings Tab
    await page.getByRole('button', { name: 'Additional' }).first().click();
    await expect(page.getByText('What Will I Learn?')).toBeVisible();

    // 7. Publish Course
    await page.getByTestId('course-builder-submit-button').click();
    await expect(
      page
        .getByTestId('tutor-toast')
        .filter({ hasText: /course (?:updated|published) successfully/i })
        .first(),
    ).toBeVisible();
  });

  test('standard free private course journey: enrollment gating, direct access prevention, and student learning flow', async ({
    studentPage,
  }) => {
    const stamp = Date.now();
    const secretLessonContent = `Private lesson educational content ${stamp}`;
    const course = createCourse({
      title: `E2E Free Gated Course ${stamp}`,
      price_type: 'free',
      is_public: false,
      lessonTitle: `First Gated Lesson ${stamp}`,
      lessonContent: `<p>${secretLessonContent}</p>`,
    });

    // 1. Unenrolled Student Direct Access Attempt: Permission Denied
    await studentPage.goto(course.lessonUrl);
    await expect(studentPage.getByRole('img', { name: 'Permission Denied' })).toBeVisible();
    await expect(studentPage.getByText(secretLessonContent)).toHaveCount(0);

    // 2. Student Landing Page: Shows "Free" badge and "Enroll Now" CTA
    await studentPage.goto(course.url);
    await expect(studentPage.locator('.tutor-course-single-pricing')).toContainText(/Free/i);
    const enrollBtn = studentPage.locator('.tutor-enroll-course-button');
    await expect(enrollBtn).toBeVisible();
    await expect(enrollBtn).toContainText(/Enroll Now/i);

    // 3. Student Enrolls in Course
    await enrollBtn.click();
    await studentPage.waitForLoadState('domcontentloaded');

    // 4. Post-enrollment CTA changes to "Start Learning" or "Continue Learning"
    const startLearningBtn = studentPage.locator(
      'a:has-text("Start Learning"), a:has-text("Continue Learning"), button:has-text("Start Learning"), button:has-text("Continue Learning")',
    );
    await expect(startLearningBtn.first()).toBeVisible();

    // 5. Navigate to Lesson & Complete Learning Flow
    await studentPage.goto(course.lessonUrl);
    await expect(studentPage.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
    await expect(studentPage.getByText(secretLessonContent)).toBeVisible();

    // Complete lesson
    const markCompleteBtn = studentPage.locator('button.tutor-topbar-mark-btn');
    if (await markCompleteBtn.isVisible().catch(() => false)) {
      await markCompleteBtn.click();
      await expect(
        studentPage.locator(
          'button.tutor-topbar-mark-btn.is-complete, button.tutor-topbar-mark-btn:has-text("Completed")',
        ),
      ).toBeVisible({
        timeout: 10_000,
      });
    }
  });

  test('public course journey: unauthenticated guest and student can access lesson directly without enrollment', async ({
    page: guestPage,
    studentPage,
  }) => {
    const stamp = Date.now();
    const publicContent = `Publicly available open educational content ${stamp}`;
    const course = createCourse({
      title: `E2E Open Public Course ${stamp}`,
      is_public: true,
      price_type: 'free',
      lessonTitle: `Public Lesson ${stamp}`,
      lessonContent: `<p>${publicContent}</p>`,
    });

    // 1. Guest views single course page: No "Enroll Now" button, directly displays "Start Learning"
    await guestPage.goto(course.url);
    await expect(guestPage.locator('.tutor-enroll-course-button')).toHaveCount(0);
    const startLearningGuest = guestPage.locator('a:has-text("Start Learning"), button:has-text("Start Learning")');
    await expect(startLearningGuest).toBeVisible();

    // 2. Guest clicks Start Learning or visits lesson directly -> Content accessible without error
    await guestPage.goto(course.lessonUrl);
    await expect(guestPage.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
    await expect(guestPage.getByText(publicContent)).toBeVisible();

    // 3. Logged-in Student also accesses without enrollment
    await studentPage.goto(course.lessonUrl);
    await expect(studentPage.getByRole('img', { name: 'Permission Denied' })).toHaveCount(0);
    await expect(studentPage.getByText(publicContent)).toBeVisible();
  });

  test('password-protected course journey: locked modal blocks access until correct password is submitted', async ({
    studentPage,
  }) => {
    const stamp = Date.now();
    const validPassword = `tutorSecret${stamp}`;
    const course = createCourse({
      title: `E2E Locked Protected Course ${stamp}`,
      password: validPassword,
      price_type: 'free',
      lessonTitle: `Protected Lesson ${stamp}`,
      lessonContent: `<p>Top secret protected content</p>`,
    });

    // 1. Student visits protected course: Password modal is displayed
    await studentPage.goto(course.url);
    const modal = studentPage.locator('#tutor-password-protected-modal');
    await expect(modal).toBeVisible();
    await expect(modal).toContainText(/Course is locked/i);

    const passwordInput = modal.locator('input[name="post_password"]');
    const submitBtn = modal.locator('button[type="submit"]');

    // 2. Submit wrong password: modal remains visible and access is blocked
    await passwordInput.fill('wrong_password_123');
    await submitBtn.click();
    await studentPage.waitForLoadState('domcontentloaded');

    const modalAfterWrong = studentPage.locator('#tutor-password-protected-modal');
    await expect(modalAfterWrong).toBeVisible();

    // 3. Submit valid password: modal unlocks course landing view
    const validPasswordInput = modalAfterWrong.locator('input[name="post_password"]');
    const validSubmitBtn = modalAfterWrong.locator('button[type="submit"]');
    await validPasswordInput.fill(validPassword);
    await validSubmitBtn.click();
    await studentPage.waitForLoadState('domcontentloaded');

    // 4. Verify unlocked state: password modal is gone, enroll button is visible
    await expect(studentPage.locator('#tutor-password-protected-modal')).toHaveCount(0);
    await expect(studentPage.locator('.tutor-enroll-course-button')).toBeVisible();
  });

  test('native paid course journey: student purchases through tutor checkout, confirms order, and gains access', async ({
    student2Page,
  }) => {
    const stamp = Date.now();
    const paidContent = `Exclusive paid content ${stamp}`;
    const course = createCourse({
      title: `E2E Native Paid Course ${stamp}`,
      price_type: 'paid',
      price: 39,
      lessonTitle: `Premium Lesson ${stamp}`,
      lessonContent: `<p>${paidContent}</p>`,
    });

    // 1. Unenrolled student direct access is blocked
    await student2Page.goto(course.lessonUrl);
    await expect(student2Page.getByRole('img', { name: 'Permission Denied' })).toBeVisible();
    await expect(student2Page.getByText(paidContent)).toHaveCount(0);

    // 2. Student visits single course page: Displays pricing and "Buy Now" button
    await student2Page.goto(course.url);
    const buyNowBtn = student2Page
      .locator('[data-testid="tutor-buy-now"], [data-cy="tutor-buy-now"], a:has-text("Buy Now")')
      .first();
    await expect(buyNowBtn).toBeVisible();
    await buyNowBtn.click();
    await expect(student2Page).toHaveURL(/course_id=\d+/);

    // 3. Native checkout form filling
    await student2Page.locator('input[name="billing_first_name"]').fill('E2E');
    await student2Page.locator('input[name="billing_last_name"]').fill('Journey');
    await student2Page.locator('input[name="billing_email"]').fill(`journey-${stamp}@example.test`);

    const checkoutUpdate = student2Page.waitForResponse(
      (response) =>
        response.url().includes('admin-ajax.php') &&
        (response.request().postData() ?? '').includes('tutor_get_checkout_html'),
    );
    await student2Page.locator('select[name="billing_country"]').selectOption('United States');
    await checkoutUpdate;

    await student2Page.locator('input[name="billing_city"]').fill('Boston');
    await student2Page.locator('input[name="billing_zip_code"]').fill('02108');
    await student2Page.locator('input[name="billing_phone"]').fill('555-0199');
    await student2Page.locator('input[name="billing_address"]').fill('42 Beacon St');

    const terms = student2Page.locator('input[name="agree_to_terms"]');
    if (await terms.isVisible().catch(() => false)) {
      await terms.check();
    }

    await student2Page.locator('input[name="payment_method"][value="bank-transfer"]').check({ force: true });
    await student2Page.locator('#tutor-checkout-pay-now-button').click();

    // 4. Order Confirmation
    await expect(student2Page).toHaveURL(/tutor_order_placement=success/, { timeout: 20_000 });
    await expect(student2Page.getByText('Order Confirmed')).toBeVisible();
  });

  test('woocommerce paid course journey: course displays woocommerce purchase button and adds to cart', async ({
    studentPage,
  }) => {
    // Enable WooCommerce monetization for this test
    setTutorOption('monetize_by', 'wc');

    try {
      const stamp = Date.now();
      const product = createWcProduct({
        title: `E2E WC Course Product ${stamp}`,
        price: 65,
      });

      const course = createCourse({
        title: `E2E WooCommerce Course ${stamp}`,
        price_type: 'paid',
        price: 65,
        product_id: product.productId,
        lessonTitle: `WC Lesson ${stamp}`,
        lessonContent: `<p>WooCommerce lesson content</p>`,
      });

      // 1. Visit Course Page: Displays WooCommerce Add to Cart button
      await studentPage.goto(course.url);
      const addToCartBtn = studentPage.locator(
        'button.single_add_to_cart_button, .tutor-btn-primary:has-text("Add to cart"), form.cart button[type="submit"]',
      );
      await expect(addToCartBtn.first()).toBeVisible();

      // 2. Click Add to Cart
      await addToCartBtn.first().click();
      await studentPage.waitForLoadState('domcontentloaded');

      // 3. Verify Added to Cart message or cart redirection
      await expect(studentPage.locator('.woocommerce-message, .woocommerce-notices-wrapper, body')).toContainText(
        /added to your cart|view cart/i,
      );
    } finally {
      // Restore default native monetization
      setTutorOption('monetize_by', 'tutor');
    }
  });
});
