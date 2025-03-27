describe('Tutor Student login', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}/dashboard/`);
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
  });

  it('should log in with valid credentials', () => {
    cy.getByInputName('log').type(Cypress.env('student_username'));
    cy.getByInputName('pwd').type(Cypress.env('student_password'));
    cy.getByInputName('rememberme').click();
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.waitAfterRequest('ajaxRequest');
    cy.url().should('include', '/dashboard');
  });

  it('should display an error for invalid username', () => {
    cy.getByInputName('log').type('invalidUsername');
    cy.getByInputName('pwd').type('validPassword');
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.get('.tutor-alert').should('contain', 'is not registered on this site.');
  });

  it('should display an error for invalid password', () => {
    cy.getByInputName('log').type('student1');
    cy.getByInputName('pwd').type('invalidPassword');
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.get('.tutor-alert').should('contain', 'is incorrect.');
  });

  it('should trim leading/trailing spaces in username', () => {
    cy.getByInputName('log').type(`   ${Cypress.env('student_username')}   `);
    cy.getByInputName('pwd').type(Cypress.env('student_password'));
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.waitAfterRequest('ajaxRequest');
    cy.url().should('include', '/dashboard');
  });

  it('should trim leading/trailing spaces in password', () => {
    cy.getByInputName('log').type(Cypress.env('student_username'));
    cy.getByInputName('pwd').type(`   ${Cypress.env('student_password')}   `);
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.waitAfterRequest('ajaxRequest');
    cy.url().should('include', '/dashboard');
  });

  it('should be case-insensitive for username', () => {
    const randomCaseUsername = Cypress.env('student_username')
      .split('')
      .map((char: string) => {
        return Math.random() > 0.5 ? char.toUpperCase() : char.toLowerCase();
      })
      .join('');

    cy.getByInputName('log').type(randomCaseUsername);
    cy.getByInputName('pwd').type(Cypress.env('student_password'));
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.waitAfterRequest('ajaxRequest');
    cy.url().should('include', '/dashboard');
  });

  it('should prevent Cross-Site Scripting (XSS) attacks', () => {
    cy.getByInputName('log').type('<script>alert("XSS attack");</script>');
    cy.getByInputName('pwd').type('test123');
    cy.get('#tutor-login-form button').contains('Sign In').click();
    cy.get('.tutor-alert').should('contain', 'The username field is empty.');
  });
});
