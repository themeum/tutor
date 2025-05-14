import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Native E-Commerce', () => {
  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes('tutor_payment_gateways')) {
        req.alias = 'paymentGateways';
      }
    });
    loginAsAdmin();
  });

  it('should select the native e-commerce as monetization engine', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=monetization`);
    cy.getPHPSelectInput('tutor_option[monetize_by]', 'Native');
    cy.saveTutorSettings();
  });

  // it('should setup payment gateways', () => {
  //   cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=ecommerce_payment`);
  //   cy.waitAfterRequest('paymentGateways');
  //   cy.getByInputName('payment_methods.0.is_active').check({ force: true });
  // });
});
