import { faker } from '@faker-js/faker';
import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Instructors', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.INSTRUCTORS}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.INSTRUCTORS);
  });

  it('should create a instructor successfully', () => {
    const instructorInfo = {
      firstName: faker.person.firstName(),
      lastName: faker.person.lastName(),
      userLogin: faker.internet.username(),
      phoneNumber: faker.phone.number(),
      email: faker.internet.email(),
      password: faker.internet.password(),
    };

    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_add_instructor')) {
        req.alias = 'addInstructorAjax';
      }
    });

    cy.get('button').contains('Add New').click();

    cy.get('#tutor-instructor-add-new [name="first_name"]').type(instructorInfo.firstName);
    cy.get('#tutor-instructor-add-new [name="last_name"]').type(instructorInfo.lastName);
    cy.get('#tutor-instructor-add-new [name="user_login"]').type(instructorInfo.userLogin);
    cy.get('#tutor-instructor-add-new [name="phone_number"]').type(instructorInfo.phoneNumber);
    cy.get('#tutor-instructor-add-new [name="email"]').type(instructorInfo.email);
    cy.get('#tutor-instructor-add-new [name="password"]').type(instructorInfo.password);
    cy.get('#tutor-instructor-add-new [name="password_confirmation"]').type(instructorInfo.password);

    cy.get('#tutor-new-instructor-form').submit();

    cy.wait('@addInstructorAjax').then((interception) => {
      expect(interception.response?.body.status_code).to.equal(undefined);
    });
  });

  it('should update a instructor successfully', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_update_instructor_data')) {
        req.alias = 'updateInstructorAjax';
      }
    });
    cy.get('.tutor-table tbody tr').eq(0).find('a').contains('Edit').click();
    cy.get('form.tutor-instructor-edit-modal.tutor-is-active').submit();
    cy.wait('@updateInstructorAjax').then((interception) => {
      expect(interception.response?.body.status_code).to.equal(undefined);
    });
  });

  it('should be able to search any instructor', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'John Doe';
    const courseLinkSelector = '.tutor-d-flex.tutor-align-center.tutor-gap-1';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should be able to perform bulk actions on all instructors', () => {
    const options = ['pending', 'blocked', 'approved'];
    options.forEach((option) => {
      cy.performBulkAction(option);
    });
  });

  it('should show validation error message for password mismatch', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_add_instructor')) {
        req.alias = 'addInstructorAjax';
      }
    });
    cy.get('button').contains('Add New').click();

    cy.get('#tutor-instructor-add-new [name="first_name"]').type('John');
    cy.get('#tutor-instructor-add-new [name="last_name"]').type('Doe');
    cy.get('#tutor-instructor-add-new [name="user_login"]').type(`john_doe`);
    cy.get('#tutor-instructor-add-new [name="email"]').type(`john.doe@example.com`);
    cy.get('#tutor-instructor-add-new [name="password"]').type('password123');
    cy.get('#tutor-instructor-add-new [name="password_confirmation"]').type('password1234');

    cy.get('#tutor-new-instructor-form').submit();

    cy.wait('@addInstructorAjax').then((interception) => {
      expect(interception.response?.body.success).to.equal(false);
    });
    cy.get('.tutor-alert-text').should('include.text', 'Your passwords should match each other. Please recheck.');
  });

  it('should change a instructor status successfully', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_instructor_bulk_action')) {
        req.alias = 'statusUpdateAjax';
      }
    });

    cy.get('body').then(() => {
      cy.get('.tutor-table tbody tr')
        .eq(0)
        .then(($row) => {
          for (let index = 0; index < 2; index++) {
            cy.wrap($row)
              .find('.tutor-table-row-status-update')
              .invoke('val')
              .then((status) => {
                if (status !== 'pending') {
                  cy.wrap($row).find('.tutor-table-row-status-update').select('pending');
                } else {
                  cy.wrap($row).find('.tutor-table-row-status-update').select('approved');
                }
              });

            cy.wait('@statusUpdateAjax').then((interception) => {
              expect(interception.response?.body.success).to.equal(true);
            });
          }
        });
    });
  });

  it('should visit a instructor profile successfully', () => {
    cy.get('.tutor-table tbody tr')
      .eq(0)
      .find('td')
      .eq(1)
      .then(($data) => {
        cy.wrap($data)
          .find('a')
          .invoke('attr', 'href')
          .then((link) => {
            if (link) {
              cy.visit(link);
            } else {
              cy.log('Link not found');
            }
          });
        cy.url().should('include', 'profile');
        cy.get('.tutor-user-profile-content').should('include.text', 'Biography');
      });
  });
});
