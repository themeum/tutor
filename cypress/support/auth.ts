import { backendUrls } from 'cypress/config/page-urls';

export const loginAsAdmin = () => {
  return cy.session(
    'tutor-login-admin',
    () => {
      cy.visit(backendUrls.LOGIN).wait(500);
      cy.getByInputName('log').clear().type(Cypress.env('admin_username'));
      cy.getByInputName('pwd').clear().type(Cypress.env('admin_password'));
      cy.get('form#loginform').submit();
      cy.url().should('include', '/wp-admin/');
    },
    {
      // Configure session options
      validate: () => {
        // Check for any cookie that starts with wordpress_logged_in_
        cy.getCookies().then((cookies) => {
          const authCookie = cookies.find((cookie) => cookie.name.startsWith('wordpress_logged_in_'));
          if (!authCookie) {
            throw new Error('Authentication cookie not found');
          }
        });
      },
      cacheAcrossSpecs: true, // Allows reusing session across different spec files
    },
  );
};

export const loginAsInstructor = () => {
  return cy.session('tutor-login-instructor', () => {
    cy.visit(backendUrls.LOGIN);
    cy.getByInputName('log').clear().type(Cypress.env('instructor_username'));
    cy.getByInputName('pwd').clear().type(Cypress.env('instructor_password'));
    cy.get('form#loginform').submit();
  });
};

export const loginAsStudent = () => {
  return cy.session('tutor-login-student', () => {
    cy.visit(backendUrls.LOGIN);
    cy.getByInputName('log').clear().type(Cypress.env('student_username'));
    cy.getByInputName('pwd').clear().type(Cypress.env('student_password'));
    cy.get('#tutor-login-form button').contains('Sign In').click();
  });
};
