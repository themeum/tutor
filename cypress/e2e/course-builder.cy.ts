//course-builder.cy.ts
import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';

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
      cy.login();
    });

    // Navigate to course if ID exists, else prepare for creation
    cy.on('uncaught:exception', () => false);
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

  it('1. edit a course', () => {
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

    cy.get('input[name="post_title"]').should('be.visible').type(courseData.post_title);
    cy.setWPeditorContent(courseData.post_content);

    cy.get('label')
      .contains('Options')
      .next()
      .within(() => {
        cy.get('button[role="tab"]').contains('General').click();
      });
    cy.getSelectInput('course_level', 'Beginner');
    cy.get('input[name="is_public_course"]').should('be.visible').check();

    if (cy.isAddonEnabled(Addons.CONTENT_DRIP)) {
      cy.get('label')
        .contains('Options')
        .next()
        .within(() => {
          cy.get('button[role="tab"]').contains('Content Drip').click();
        });
      cy.get('input[name="contentDripType"]').first().parent('label').click();
    }

    // cy.get('input[name="isScheduleEnabled"]').should('be.visible').check();
    cy.get('button')
      .contains('Upload Thumbnail')
      .click()
      .then(() => {
        cy.get('.media-modal')
          .should('be.visible')
          .within(() => {
            cy.get('.spinner.is-active', {
              timeout: 10000,
            }).should('not.exist');

            cy.wait(5000);

            // if no image is uploaded, upload one else select one
            cy.get('.attachment')
              .its('length')
              .then((length) => {
                if (length === 0) {
                  cy.get('.media-button-select').click();
                } else {
                  cy.get('.attachment')
                    .first()
                    .click()
                    .then(() => {
                      cy.get('.media-button-select').click();
                    });
                }
              });
          });
      });
  });
});
