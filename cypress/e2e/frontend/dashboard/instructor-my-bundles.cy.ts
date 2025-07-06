import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls, frontendUrls } from '../../../config/page-urls';

describe('Tutor Dashboard My Bundles', () => {
  let bundleData: { post_title: string; post_content: string; course_benefits: string };

  before(() => {
    bundleData = {
      post_title: faker.lorem.sentences(1),
      post_content: faker.lorem.sentences(3),
      course_benefits: faker.lorem.sentences(3),
    };
  });

  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.MY_COURSES}`);
    cy.loginAsInstructor();
    cy.url().should('include', frontendUrls.dashboard.MY_COURSES);

    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_BUNDLE_DETAILS)) {
        req.alias = 'getBundleDetails';
      }

      if (req.body.includes(endpoints.UPDATE_BUNDLE)) {
        req.alias = 'updateBundle';
      }

      if (req.body.includes(endpoints.COURSE_LIST)) {
        req.alias = 'courseList';
      }

      if (req.body.includes(endpoints.ADD_REMOVE_COURSE_TO_BUNDLE)) {
        req.alias = 'addRemoveCourseToBundle';
      }
    });
  });

  it('should create a bundle', () => {
    cy.get('a.tutor-add-new-course-bundle').click();

    cy.waitAfterRequest('getBundleDetails');

    // Only focus on creating basic bundle information
    cy.getByInputName('post_title').clear().type(bundleData.post_title);
    cy.setTinyMceContent('[data-cy=tutor-tinymce]', bundleData.post_content);
    cy.getByInputName('course_benefits').clear().type(bundleData.course_benefits);

    // Add course to bundle
    cy.get('[data-cy=add-course]').click();
    cy.get('[data-cy=tutor-modal]').within(($elements) => {
      cy.wrap($elements).should('contain.text', 'Select Courses');
      cy.waitAfterRequest('courseList');
      if ($elements.text().includes('No Data!')) {
        cy.get('[data-cy=tutor-modal-close]').click();
        return;
      } else {
        cy.get('tbody tr:first-child').find('[data-cy=select-course]').click({ force: true });
        cy.get('button[data-cy=add-selected-courses]').click();
      }
      cy.waitAfterRequest('addRemoveCourseToBundle');
    });

    // Remove course from bundle if any exists
    cy.get('[data-cy=course-selection]').then(($elements) => {
      if ($elements.text().includes('No Courses Added Yet')) {
        cy.log('No courses to remove');
        return;
      }

      cy.get('[data-cy=remove-course]').first().click({ force: true });
      cy.waitAfterRequest('addRemoveCourseToBundle');
    });

    cy.get('[data-cy=bundle-builder-submit-button]').click();
    cy.waitAfterRequest('updateBundle');
    cy.get('[data-cy=tutor-toast]').should('contain.text', 'Course Bundle updated successfully');

    cy.window().then((win) => {
      const tutorObject = win._tutorobject;
      if (tutorObject.settings?.instructor_can_publish_course === 'on') {
        return;
      } else {
        cy.get('[data-cy=tutor-modal]').within(() => {
          cy.get('[data-cy=back-to-course-bundles]').click();
        });
      }
    });
  });

  it('should visit a bundle product', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-course-name a')
          .eq(0)
          .then(($a) => {
            cy.wrap($a).click();
            cy.wrap($a)
              .invoke('attr', 'href')
              .then((link) => {
                cy.url().should('eq', link);
              });
          });
      }
    });
  });

  it('should delete a bundle product', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-course-card button[action-tutor-dropdown=toggle]').eq(0).click();
        cy.get('.tutor-dropdown-parent.is-open a').contains('Delete').click();
        cy.get('.tutor-modal.tutor-is-active button').contains('Yes, Delete This').click();

        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });
});
