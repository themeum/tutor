import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Creation', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      post_title: faker.lorem.sentences(1),
      post_content: faker.lorem.sentences(5),
    };
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      // Course related actions
      if (req.body.includes(endpoints.UPDATE_COURSE)) {
        req.alias = 'updateCourse';
      }
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit('/wp-admin/admin.php?page=tutor');
  });

  it('creates a new course', () => {
    cy.get('.wp-menu-name').contains('Tutor LMS').click();
    cy.get('a.tutor-create-new-course').click();

    // Extract courseId from URL
    cy.url()
      .should('include', 'course_id=')
      .then((url) => {
        courseId = url.split('course_id=')[1].split('&')[0];
        cy.log(`Course ID: ${courseId}`);
        cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');

        // Save course ID to file for other tests to use
        cy.writeFile('cypress/fixtures/course.json', { courseId });
      });

    cy.get('h6').should('have.text', 'Course Builder');
  });

  it('fills basic course information', () => {
    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      courseId = fixture.courseId;
      cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
      cy.waitAfterRequest('getCourseDetails');

      cy.getByInputName('post_title').clear().type(courseData.post_title);
      cy.setTinyMceContent('[data-cy=tutor-tinymce]', courseData.post_content);

      cy.updateCourse();
      cy.get('[data-cy=tutor-toast]').should('contain', 'Course updated successfully');
    });
  });

  it('opens an existing course', () => {
    let courseTitle = '';
    cy.get('.wp-menu-name').contains('Tutor LMS').click();
    cy.get('table.table-dashboard-course-list tbody tr')
      .first()
      .within(() => {
        cy.get('a.tutor-table-link')
          .first()
          .click()
          .invoke('text')
          .then((text) => {
            courseTitle = text.trim();
          });
      });

    cy.url().should('include', 'course_id=');
    cy.get('h6').should('have.text', 'Course Builder');
    cy.waitAfterRequest('getCourseDetails');

    cy.getByInputName('post_title').then(($input) => {
      const title = $input.val();
      cy.wrap(title).should('eq', courseTitle);
    });
  });
});
