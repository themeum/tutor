import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { loginAsAdmin, loginAsInstructor } from 'cypress/support/auth';
import { backendUrls, frontendUrls } from '../../../config/page-urls';

describe('Give Instructor Course Publish Permissions', () => {
  beforeEach(() => {
    loginAsAdmin();
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab=general`);
  });
  it('should allow instructors to publish courses', () => {
    cy.toggle('tutor_option[instructor_can_publish_course]', '#field_instructor_can_publish_course', true);
    cy.saveTutorSettings();
  });
});

describe('Course Creation for Announcement', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      post_title: 'Announcement Course',
      post_content: 'This is a paid course created as part of the announcement test.',
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
    loginAsInstructor();

    // Check if file exists and handle accordingly
    cy.task('checkFileExists', 'cypress/fixtures/course.json').then((exists) => {
      if (!exists) {
        cy.writeFile('cypress/fixtures/course.json', {});
        cy.log('Created new course.json file');
      } else {
        // File exists, read and verify it
        cy.readFile('cypress/fixtures/course.json').then((content) => {
          if (!content || Object.keys(content).length === 0) {
            cy.writeFile('cypress/fixtures/course.json', {});
          } else {
            cy.log(`Course ID already exists in course.json: ${content.courseId}`);
            courseId = content.courseId;
            cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSE_BUILDER}${courseId}`);
          }
        });
      }
    });
  });

  it('creates a new course', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSES}`);
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
    cy.waitAfterRequest('getCourseDetails');

    cy.getByInputName('post_title').clear().type(courseData.post_title);
    cy.setTinyMceContent('[data-cy=tutor-tinymce]', courseData.post_content);

    cy.updateCourse();
    cy.get('[data-cy=tutor-toast]').should('contain', 'Course updated successfully');
  });
});

describe('Tutor Dashboard Announcements', () => {
  const announcements = {
    title: faker.lorem.sentence(),
    summary: faker.lorem.sentences(3),
  };
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ANNOUNCEMENTS}`);
    cy.loginAsInstructor();
    cy.url().should('include', frontendUrls.dashboard.DASHBOARD);
  });

  it('should filter announcements', () => {
    cy.get('.tutor-col-12 > .tutor-js-form-select').click();
    cy.get(
      '.tutor-col-12 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options span[tutor-dropdown-item]',
    ).then(($options) => {
      const randomIndex = Cypress._.random(1, $options.length - 1);
      const $randomOption = $options.eq(randomIndex);
      cy.wrap($randomOption).click({ force: true });
      const selectedOptionText = $randomOption.text().trim();
      cy.get('body').then(($body) => {
        if ($body.text().includes('No Data Found.')) {
          cy.log('No data available');
        } else {
          cy.get('.tutor-fs-7.tutor-fw-medium.tutor-color-muted').each(($announcement) => {
            cy.wrap($announcement).should('contain.text', selectedOptionText);
          });
        }
      });
    });
  });

  it('should create a new announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get('button[data-tutor-modal-target=tutor_announcement_new]').click();
    cy.get('.tutor-modal.tutor-is-active .tutor-form-select-label').then(($modal) => {
      if ($modal.text().includes('No Data Found')) {
        cy.log('No data found');
        return;
      }
      cy.get('#tutor_announcement_new input[name=tutor_announcement_title]').type(announcements.title);
      cy.get('#tutor_announcement_new textarea[name=tutor_announcement_summary]').type(announcements.summary);
      cy.get('#tutor_announcement_new button').contains('Publish').click();

      cy.get('.tutor-table').should('contain.text', announcements.title);
    });
  });

  it('should view and delete an announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found.')) {
        cy.log('No data found');
      } else {
        cy.get('button.tutor-announcement-details').eq(0).click();
        cy.get('.tutor-modal.tutor-is-active button.tutor-modal-btn-delete').click();
        cy.get('.tutor-modal.tutor-is-active button').contains('Yes, Delete This').click();
        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });
});

describe('Delete Course', () => {
  it('should delete the course', () => {
    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      if (!fixture.courseId) throw new Error('Course ID not found in fixture');
      cy.deleteCourseById(fixture.courseId);
    });
  });
});
