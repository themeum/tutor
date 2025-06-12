import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Native E-Commerce Guest Checkout', () => {
  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes('tutor_payment_gateways')) {
        req.alias = 'paymentGateways';
      }
      if (req.body.includes('tutor_payment_settings')) {
        req.alias = 'paymentSettings';
      }
    });
    loginAsAdmin();
  });

  it('should select the native e-commerce as monetization engine', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=monetization`);
    cy.getPHPSelectInput('tutor_option[monetize_by]', 'Native');
    cy.saveTutorSettings();
  });

  it('should disable guest checkout and enable buy now button', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=ecommerce_checkout`);
    cy.toggle('tutor_option[is_enable_buy_now]', '#field_is_enable_buy_now', true);
    cy.toggle('tutor_option[is_enable_guest_checkout]', '#field_is_enable_guest_checkout', true);
  });

  it('should setup paypal payment gateways', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.SETTINGS}&tab_page=ecommerce_payment`);
    cy.waitAfterRequest('paymentGateways');
    cy.waitAfterRequest('paymentSettings');
    cy.get('[data-payment-item-paypal=true] button[data-cy=collapse-button]').click();

    cy.get('[data-payment-item-paypal=true]').within(() => {
      cy.get('input[name*="fields.1.value"]').clear().type(Cypress.env('paypal_merchant_email'));
      cy.get('input[name*="fields.2.value"]').clear().type(Cypress.env('paypal_client_id'));
      cy.get('input[name*="fields.3.value"]').clear().type(Cypress.env('paypal_secret_id'));
      cy.get('input[name*="fields.4.value"]').clear().type(Cypress.env('paypal_webhook_id'));

      cy.get('input[name*="is_active"]').check({ force: true });
    });

    cy.saveTutorSettings();
  });
});
