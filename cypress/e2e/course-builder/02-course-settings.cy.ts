import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Settings', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      maximum_students: faker.number.int({ min: 1, max: 100 }),
      enrollment_expiry: faker.number.int({ min: 1, max: 100 }),
      course_price: String(faker.number.int({ min: 50, max: 1000 })),
      course_sale_price: String(faker.number.int({ min: 1, max: 49 })),
    };

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      courseId = fixture.courseId;
    });
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.UPDATE_COURSE)) {
        req.alias = 'updateCourse';
      }
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }
      if (req.body.includes('query-attachments')) {
        req.alias = 'queryAttachments';
      }
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    cy.waitAfterRequest('getCourseDetails');
  });

  it('configures general settings', () => {
    cy.get('[data-cy=course-settings]').within(() => {
      cy.get('button[role="tab"]').contains('General').click();
    });
    cy.getSelectInput('course_level', 'Beginner');
    cy.getByInputName('is_public_course').check({ force: true });

    cy.updateCourse();
  });

  it('configures content drip settings', () => {
    cy.get('[data-cy=course-settings]').within(($elements) => {
      cy.get('button[role="tab"]').contains('Content Drip').click();

      cy.isAddonEnabled(Addons.CONTENT_DRIP).then((isEnabled) => {
        if (!isEnabled) {
          cy.wrap($elements).get('button').contains('Enable Content Drip Addon');
          cy.log('Content Drip addon is not enabled');
          return;
        }

        cy.get('input[name="contentDripType"]').first().parent('label').click();
      });
    });

    cy.updateCourse();
  });

  it('configures enrollment settings', () => {
    cy.get('[data-cy=course-settings]').within(() => {
      cy.get('button[role="tab"]').contains('Enrollment').click();
    });

    cy.isAddonEnabled(Addons.ENROLLMENT).then((isEnabled) => {
      if (!isEnabled) {
        return;
      }

      cy.getByInputName('maximum_students').type(String(courseData.maximum_students));
      cy.window().then((win) => {
        if (win._tutorobject.settings?.enrollment_expiry_enabled === 'on') {
          cy.getByInputName('enrollment_expiry').type(String(courseData.enrollment_expiry));
        }
      });
      cy.getByInputName('course_enrollment_period').check();
      cy.getByInputName('course_enrollment_period')
        .should('be.checked')
        .then(() => {
          cy.selectDate('enrollment_starts_date');
          cy.getSelectInput('enrollment_starts_time', '12:00 AM');
        });
    });

    cy.updateCourse();
  });

  it('configures course media', () => {
    cy.getWPMedia('Featured Image', 'Upload Thumbnail', 'Replace Image');
    cy.getWPMedia('Intro Video', 'Upload Video', 'Replace Thumbnail');

    cy.updateCourse();
  });

  it('configures pricing', () => {
    cy.getByInputName('course_price_type').contains('Paid').click();

    cy.isAddonEnabled(Addons.SUBSCRIPTION).then((isEnabled) => {
      cy.window().then((win) => {
        if (win._tutorobject.settings?.monetize_by === 'tutor' && isEnabled) {
          cy.getSelectInput('course_selling_option', 'All');
        }
      });
    });
    cy.getByInputName('course_price').type(courseData.course_price);
    cy.getByInputName('course_sale_price').type(courseData.course_sale_price);

    cy.updateCourse();
  });
});
