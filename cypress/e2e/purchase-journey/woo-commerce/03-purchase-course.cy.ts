import { faker } from '@faker-js/faker';
import { backendUrls } from 'cypress/config/page-urls';

describe('Purchase Course', () => {
  let orderId: string;

  it('should purchase a paid course', () => {
    const billingData = {
      first_name: faker.person.firstName(),
      last_name: faker.person.lastName(),
      address: faker.location.streetAddress(),
      city: faker.location.city(),
      postalCode: faker.location.zipCode(),
      phone: faker.phone.number(),
    };

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      const { slug } = fixture;
      cy.visit(slug);
    });

    cy.get('button[name=add-to-cart]').click();
    cy.loginAsStudent();
    cy.get('body').then((body) => {
      if (body.find('a.tutor-woocommerce-view-cart').length) {
        cy.get('a.tutor-woocommerce-view-cart').click();
      } else {
        cy.get('button[name=add-to-cart]').click();
        cy.get('a.tutor-woocommerce-view-cart').click();
      }
    });

    cy.wait(1000);
    cy.url().should('include', Cypress.env('woo_commerce_cart'));

    cy.get('a.wc-block-cart__submit-button').click();
    cy.wait(1000);
    cy.url().should('include', Cypress.env('woo_commerce_checkout'));

    cy.get('body').then((body) => {
      if (body.find('.wc-block-components-address-card__edit')) {
        cy.get('.wc-block-components-address-card__edit').click();
      }
    });

    cy.get('input#billing-first_name').clear().type(billingData.first_name);
    cy.get('input#billing-last_name').clear().type(billingData.last_name);
    cy.get('input#billing-address_1').clear().type(billingData.address);
    cy.get('input#billing-city').clear().type(billingData.city);
    cy.get('input#billing-postcode').clear().type(billingData.postalCode);

    cy.get('.wc-block-components-payment-method-label').contains('Cash on delivery').click();

    cy.get('button.wc-block-components-checkout-place-order-button').click();

    cy.wait(1000);

    cy.url()
      .should('include', 'order-received')
      .then((url) => {
        orderId = url.split('order-received/')[1].split('/')[0];
        cy.log(`Order ID: ${orderId}`);
        cy.wrap(orderId).should('be.a', 'string').and('not.be.empty');
      });
  });

  it('should verify the order in WooCommerce', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.WOOCOMMERCE_ORDERS}`);
    cy.loginAsAdmin();
    cy.getByInputName('s').clear().type(orderId);
    cy.get('input#search-submit').click();
    cy.wait(1000);
    cy.get('table>tbody#the-list>tr>td:nth-of-type(1)').contains(orderId).should('exist');
  });
});
