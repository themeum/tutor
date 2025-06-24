import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls, frontendUrls } from '../../../config/page-urls';

describe('Tutor Dashboard Instructor Settings', () => {
  it('should give instructor access to publish courses', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}`);
    cy.loginAsAdmin();

    cy.toggle('tutor_option[instructor_can_publish_course]', '#field_instructor_can_publish_course', true);
    cy.saveTutorSettings();
  });
});

describe('Tutor Dashboard My Courses', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.MY_COURSES}`);
    cy.loginAsInstructor();
    cy.url().should('include', frontendUrls.dashboard.MY_COURSES);

    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }
    }).as('ajaxRequest');
  });

  it('should create a new course', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

    cy.get('a.tutor-create-new-course.tutor-dashboard-create-course').click();

    cy.waitAfterRequest('ajaxRequest');
    cy.waitAfterRequest('getCourseDetails');

    cy.get('[data-cy=course-builder-submit-button]').click();
  });

  it('should draft and publish again a course', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No courses to draft');
        return;
      }

      // Draft a course
      cy.get('.tutor-card-footer button[action-tutor-dropdown=toggle]').eq(0).click();
      cy.get('.tutor-dropdown-parent.is-open a').contains('Move to Draft').click();
      cy.url().should('include', 'draft-courses');
      cy.window().then((win) => {
        const tutorObject = win._tutorobject;
        if (tutorObject.settings?.instructor_can_publish_course === 'off') {
          cy.log('Instructor cannot publish course');
          return;
        } else {
          // Publish a course from draft again
          cy.get('.tutor-card-footer button[action-tutor-dropdown=toggle]').eq(0).click();
          cy.get('.tutor-dropdown-parent.is-open a').contains('Publish').click();
          cy.url().should('not.include', 'draft-courses');
          cy.url().should('include', 'my-courses');
        }
      });
    });
  });

  it('should duplicate a course', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No courses to delete');
        return;
      }
      cy.get('.tutor-card-footer button[action-tutor-dropdown=toggle]').eq(0).click();
      cy.get('.tutor-dropdown-parent.is-open a').contains('Duplicate').click();

      cy.url().should('include', 'draft-courses');
    });
  });

  it('should delete a course', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No courses to delete');
        return;
      }
      cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

      cy.get('.tutor-card-footer button[action-tutor-dropdown=toggle]').eq(0).click();
      cy.get('.tutor-dropdown-parent.is-open a').contains('Delete').click();
      cy.get('.tutor-modal.tutor-is-active button').contains('Yes, Delete This').click();

      cy.wait('@ajaxRequest').then((interception) => {
        expect(interception.response?.body.success).to.equal(true);
      });
    });
  });
});
