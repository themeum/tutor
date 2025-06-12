import { backendUrls } from 'cypress/config/page-urls';

describe('WooCommerce Setup', () => {
  it('should select WooCommerce as monetization engine', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=monetization`);
    cy.loginAsAdmin();
    cy.getPHPSelectInput('tutor_option[monetize_by]', 'WooCommerce');
    cy.saveTutorSettings();
  });
});
