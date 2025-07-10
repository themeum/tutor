import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Paid Course Creation for Native E-Commerce Guest Checkout', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      post_title: 'Native E-Commerce Guest Checkout Course',
      post_content: 'This is a paid course created as part of the Native E-Commerce course guest checkout journey.',
      course_price: faker.number.int({ min: 50, max: 1000 }).toString(),
      course_sale_price: faker.number.int({ min: 1, max: 49 }).toString(),
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

  it('configures pricing', () => {
    cy.waitAfterRequest('getCourseDetails');

    cy.getByInputName('course_price_type').should('be.visible').as('priceTypeContainer');

    cy.get('@priceTypeContainer').contains('Paid').should('be.visible').click({ force: true });

    cy.isAddonEnabled(Addons.SUBSCRIPTION).then((isEnabled) => {
      cy.window().then((win) => {
        if (win._tutorobject.settings?.monetize_by === 'tutor' && isEnabled) {
          cy.getSelectInput('course_selling_option', 'All');
        }
      });
    });
    cy.getByInputName('course_price').should('be.visible').clear().type(courseData.course_price, { delay: 100 });
    cy.getByInputName('course_sale_price')
      .should('be.visible')
      .clear()
      .type(courseData.course_sale_price, { delay: 100 });

    cy.wait(500);

    cy.get('[data-cy=course-slug]').should('be.visible').invoke('text').as('slugValue');

    cy.get('@slugValue').then((slugValue) => {
      cy.log(`Slug: ${slugValue}`);
      cy.readFile('cypress/fixtures/course.json').then((fixture) => {
        const updatedFixture = { ...fixture, slug: slugValue };
        cy.writeFile('cypress/fixtures/course.json', updatedFixture);
        cy.log('Fixture updated with slug:', updatedFixture);
      });
    });

    cy.updateCourse();
  });
});
