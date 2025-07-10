import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Paid Course Creation for WooCommerce', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      post_title: 'WooCommerce Paid Course',
      post_content: 'This is a paid course created as part of the WooCommerce course single purchase journey.',
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

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      if (fixture.courseId) {
        cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${fixture.courseId}`);
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

    cy.get('[data-cy=course-slug]').then((slug) => {
      const slugValue = slug.text();
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
