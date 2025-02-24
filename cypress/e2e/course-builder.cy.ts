//course-builder.cy.ts
import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import { backendUrls } from 'cypress/config/page-urls';

describe('Course Builder', () => {
  let courseData: CourseFormData;
  let courseId: string;

  before(() => {
    courseData = {
      post_title: faker.lorem.sentences(1),
      post_content: faker.lorem.sentences(5),
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

    cy.get('label')
      .contains('Options')
      .next()
      .within(() => {
        cy.get('button[role="tab"]').contains('General').click();
      });
    cy.getSelectInput('course_level', 'Beginner');
    cy.getByInputName('is_public_course').check();

    if (cy.isAddonEnabled(Addons.CONTENT_DRIP)) {
      cy.get('label')
        .contains('Options')
        .next()
        .within(() => {
          cy.get('button[role="tab"]').contains('Content Drip').click();
        });
      cy.get('input[name="contentDripType"]').first().parent('label').click();
    }

    cy.setWPMedia('Featured Image', 'Upload Thumbnail', 'Replace Image');

    cy.get('[data-cy="course-builder-submit-button"]').click();

    cy.wait(1000);

    cy.get("[data-cy='tutor-toast']").should('be.visible').contains('Course updated successfully');
  });
});
