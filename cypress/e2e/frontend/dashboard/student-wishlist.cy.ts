import { frontendUrls } from '../../../config/page-urls';

describe('Tutor Dashboard Student Wishlist', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.WISHLIST}`);
    cy.loginAsStudent();
    cy.url().should('include', frontendUrls.dashboard.WISHLIST);
  });

  it('should add a course to wishlist', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.visit(`${Cypress.env('base_url')}/${frontendUrls.COURSES}`);
        cy.get('.tutor-icon-bookmark-line').eq(0).parent().click();

        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });
});
