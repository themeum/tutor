//course-builder.cy.ts
import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import { backendUrls } from 'cypress/config/page-urls';

describe('Course Builder', () => {
  let courseData: CourseFormData;
  let courseId: string;

  before(() => {
    // @ts-ignore
    courseData = {
      post_title: faker.lorem.sentences(1),
      post_content: faker.lorem.sentences(5),
      maximum_students: faker.number.int({ min: 1, max: 100 }),
      enrollment_expiry: faker.number.int({ min: 1, max: 100 }),
      course_price: String(faker.number.int({ min: 50, max: 1000 })),
      course_sale_price: String(faker.number.int({ min: 1, max: 49 })),
      course_benefits: faker.lorem.lines(2),
      course_target_audience: faker.lorem.lines(2),
      course_duration_hours: faker.number.int({ min: 1, max: 10 }),
      course_duration_minutes: faker.number.int({ min: 1, max: 59 }),
      course_material_includes: faker.lorem.lines(2),
      course_requirements: faker.lorem.lines(2),
    };
  });

  beforeEach(() => {
    cy.session('tutor-login', () => {
      cy.visit(backendUrls.LOGIN);
      cy.loginAsAdmin();
    });

    if (courseId) {
      cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
      cy.get('h6').should('have.text', 'Course Builder'); // Ensure page loads
    } else {
      cy.visit('/wp-admin/admin.php?page=tutor');
    }
  });

  // it('1. creates a new course', () => {
  //   cy.get('.wp-menu-name').contains('Tutor LMS').click();
  //   cy.get('a.tutor-create-new-course').click();

  //   // Extract courseId from URL
  //   cy.url()
  //     .should('include', 'course_id=')
  //     .then((url) => {
  //       courseId = url.split('course_id=')[1].split('&')[0];
  //       cy.log(`Course ID: ${courseId}`);
  //       cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');
  //     });

  //   cy.get('h6').should('have.text', 'Course Builder');
  // });

  it('1. open a course in course builder', () => {
    cy.get('.wp-menu-name').contains('Tutor LMS').click();

    cy.get('table.table-dashboard-course-list tbody tr')
      .first()
      .within(() => {
        cy.get('a.tutor-table-link').first().click();

        // Extract courseId from URL
        cy.url()
          .should('include', 'course_id=')
          .then((url) => {
            courseId = url.split('course_id=')[1].split('&')[0];
            cy.log(`Course ID: ${courseId}`);
            cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');
          });
      });

    cy.get('h6').should('have.text', 'Course Builder');
  });

  it('2. fills course basics', () => {
    // Ensure courseId is set from previous test
    cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');

    cy.getByInputName('post_title').type(courseData.post_title);
    cy.setTinyMceContent('[data-cy=tutor-tinymce]', courseData.post_content);

    cy.get('[data-cy=course-settings]').within(() => {
      cy.get('button[role="tab"]').contains('General').click();
    });
    cy.getSelectInput('course_level', 'Beginner');
    cy.getByInputName('is_public_course').check();

    cy.get('[data-cy=course-settings]').within(($elements) => {
      cy.get('button[role="tab"]').contains('Content Drip').click();

      cy.isAddonEnabled(Addons.CONTENT_DRIP).then((isEnabled) => {
        if (!isEnabled) {
          cy.wrap($elements).get('button').contains('Enable Content Drip Addon');
          return;
        }

        cy.get('input[name="contentDripType"]').first().parent('label').click();
      });
    });

    cy.get('[data-cy=course-settings]').within(() => {
      cy.get('button[role="tab"]').contains('Enrollment').click();
    });

    cy.isAddonEnabled(Addons.ENROLLMENT).then((isEnabled) => {
      if (!isEnabled) {
        return;
      }

      cy.getByInputName('maximum_students').type(String(courseData.maximum_students));
      cy.getByInputName('enrollment_expiry').type(String(courseData.enrollment_expiry));
      cy.getByInputName('course_enrollment_period').check();
      cy.getByInputName('course_enrollment_period')
        .should('be.checked')
        .then(() => {
          cy.selectDate('enrollment_starts_date');
          cy.getSelectInput('enrollment_starts_time', '12:00 AM');
        });
    });

    cy.getWPMedia('Featured Image', 'Upload Thumbnail', 'Replace Image');
    cy.getWPMedia('Intro Video', 'Upload Video', 'Replace Thumbnail');

    cy.getByInputName('course_price_type').contains('Paid').click();

    cy.isAddonEnabled(Addons.SUBSCRIPTION).then((isEnabled) => {
      cy.window().then((win) => {
        if (win._tutorobject.settings?.monetize_by === 'tutor' && isEnabled) {
          cy.getByInputName('course_price').type(courseData.course_price);
          cy.getByInputName('course_sale_price').type(courseData.course_sale_price);
        }
      });
    });

    cy.updateCourse();
  });

  it('3. fills course curriculum', () => {
    cy.wait(1000);

    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });
  });

  it('4. fills course additional', () => {
    cy.wait(1000);

    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Additional').click();
    });

    cy.getByInputName('course_benefits').clear().type(courseData.course_benefits);
    cy.getByInputName('course_target_audience').clear().type(courseData.course_target_audience);
    cy.getByInputName('course_duration_hours').clear().type(String(courseData.course_duration_hours));
    cy.getByInputName('course_duration_minutes').clear().type(String(courseData.course_duration_minutes));
    cy.getByInputName('course_material_includes').clear().type(courseData.course_material_includes);
    cy.getByInputName('course_requirements').clear().type(courseData.course_requirements);
    cy.getWPMedia('Attachments', 'Upload Attachment', '');

    cy.updateCourse();
  });
});
