import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Publish and Delete', () => {
  let courseId: string;

  before(() => {
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
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    cy.wait(500);
    cy.waitAfterRequest('getCourseDetails');
  });

  it('publishes the course', () => {
    cy.get('[data-cy="course-builder-submit-button"]').click();
    cy.waitAfterRequest('updateCourse');
    cy.get('[data-cy=tutor-toast]').should('contain', 'Course updated successfully');
  });

  it('deletes the course', () => {
    cy.get('[data-cy=dropdown-trigger]').click();
    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=move-to-trash]').click();
    });

    cy.waitAfterRequest('updateCourse');
    cy.wait(1000);
    cy.url().should('include', '/wp-admin/admin.php?page=tutor');

    cy.exec('rm cypress/fixtures/course.json').then(() => {
      cy.log('File removed');
    });
  });
});
