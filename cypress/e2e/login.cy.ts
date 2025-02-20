describe('WP Admin Login', () => {
  it('should login with valid credentials', () => {
    cy.visit('/wp-login.php');

    // ignore console errors
    cy.on('uncaught:exception', () => false);

    cy.get('#user_login').type(Cypress.env('username'));
    cy.get('#user_pass').type(Cypress.env('password'));

    // should have username and password
    cy.get('#user_login').should('have.value', Cypress.env('username'));
    cy.get('#user_pass').should('have.value', Cypress.env('password'));

    cy.get('#wp-submit').click();

    cy.url().should('include', '/wp-admin');
  });
});
