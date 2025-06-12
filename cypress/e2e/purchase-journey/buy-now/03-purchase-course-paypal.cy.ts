import { faker } from '@faker-js/faker';
import { backendUrls, frontendUrls } from 'cypress/config/page-urls';

// Types
interface BillingData {
  first_name: string;
  last_name: string;
  address: string;
  email: string;
  city: string;
  zipcode: string;
  phone: string;
}

// Constants
const SELECTORS = {
  BUY_NOW: 'a[data-cy=tutor-buy-now]',
  CART_TOTAL: '.tutor-cart-summery-item',
  CHECKOUT_TOTAL: '.tutor-checkout-summary-item',
  PAY_NOW_BUTTON: 'button#tutor-checkout-pay-now-button',
  ORDER_HISTORY: 'a[data-cy=tutor-native-order-history]',
  TERM_AND_CONDITIONS: 'agree_to_terms',
} as const;

// Utilities
const extractAmount = (text: string): string => {
  const regex = /[\d,]+(\.\d+)?/;
  const match = text.match(regex);
  if (!match) {
    throw new Error('Amount not found in the string');
  }
  const amount = match[0].replace(/,/g, '');
  if (isNaN(Number(amount))) {
    throw new Error('Amount is not a valid number');
  }
  return amount;
};

const fillBillingForm = (data: BillingData) => {
  const fields = [
    { name: 'billing_first_name', value: data.first_name },
    { name: 'billing_last_name', value: data.last_name },
    { name: 'billing_email', value: data.email },
    { name: 'billing_city', value: data.city },
    { name: 'billing_zip_code', value: data.zipcode },
    { name: 'billing_address', value: data.address },
    { name: 'billing_phone', value: data.phone },
  ];

  fields.forEach(({ name, value }) => {
    cy.getByInputName(name).clear().type(value);
  });
};

describe('Purchase Course', () => {
  let orderId: string;
  let courseSlug: string;
  let orderAmount: string;

  it('should purchase a paid course', () => {
    const billingData: BillingData = {
      first_name: faker.person.firstName(),
      last_name: faker.person.lastName(),
      address: faker.location.streetAddress(),
      email: faker.internet.email(),
      city: faker.location.city(),
      zipcode: faker.location.zipCode(),
      phone: faker.phone.number(),
    };

    // Load course and visit
    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      if (!fixture.slug) throw new Error('Course slug not found in fixture');
      courseSlug = fixture.slug;
      cy.visit(courseSlug);
    });

    // Add to cart flow
    cy.get(SELECTORS.BUY_NOW).click();
    cy.loginAsStudent();
    cy.get(SELECTORS.BUY_NOW).click();

    cy.url().should('include', Cypress.env('checkout'));

    // Verify checkout total
    cy.get(SELECTORS.CHECKOUT_TOTAL)
      .contains('Grand Total')
      .parent()
      .find('.tutor-checkout-grand-total')
      .invoke('text')
      .then((grandTotal) => {
        orderAmount = extractAmount(grandTotal);
        cy.log(`Order Amount: ${orderAmount}`);
      });

    // Fill billing form
    fillBillingForm(billingData);
    cy.get('select[name="billing_state"]').select(1);
    cy.get('input[type="radio"][name="payment_method"][value="paypal"]').check({ force: true });
    cy.get('body').then((body) => {
      const $body = Cypress.$(body);
      if ($body.find(`input[name=${SELECTORS.TERM_AND_CONDITIONS}]`).length) {
        cy.getByInputName(SELECTORS.TERM_AND_CONDITIONS).check({ force: true });
      }
    });

    // Process payment
    cy.get(SELECTORS.PAY_NOW_BUTTON).click();

    // PayPal checkout process
    cy.origin('https://www.sandbox.paypal.com/', () => {
      cy.url().then((url) => {
        if (!url.includes('https://www.sandbox.paypal.com/checkoutnow')) return;

        // Login to PayPal
        cy.get('input[name=login_email]').type(Cypress.env('paypal_personal_email'));
        cy.get('button#btnNext').click();

        cy.wait(2000);
        // Handle secondary authentication if present
        cy.get('body').then((body) => {
          const $body = Cypress.$(body);
          if ($body.find('a.scTrack.secondaryLink').length) {
            cy.get('a.scTrack.secondaryLink').click();
          }
        });

        cy.get('input[name=login_password]').type(Cypress.env('paypal_personal_password'));
        cy.get('button#btnLogin').click();

        cy.get('button[data-id=payment-submit-btn]').click();

        cy.wait(2000);
      });
    });

    cy.wait(2000);

    // Verify order completion
    cy.url()
      .should('include', 'tutor_order_placement=success&order_id=')
      .then((url) => {
        const orderIdMatch = url.match(/order_id=(\d+)/);
        if (!orderIdMatch) throw new Error('Order ID not found in URL');
        orderId = orderIdMatch[1];
        cy.log(`Order ID: ${orderId}`);
      });

    cy.get(SELECTORS.ORDER_HISTORY).click();
  });

  it('should verify the order', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.ORDERS}`);
    cy.loginAsAdmin();
    cy.getByInputName('search').clear().type(orderId).type('{enter}');
    cy.wait(1000);
    cy.get('table>tbody>tr>td:nth-of-type(2)').contains(orderId).should('exist');
    cy.get('table>tbody>tr>td:nth-of-type(5)').contains('PayPal').should('exist');
    cy.get('table>tbody>tr>td:nth-of-type(6)').contains('Paid').should('exist');
    cy.get('table>tbody>tr>td:nth-of-type(7)').contains('Completed').should('exist');
    cy.get('table>tbody>tr>td:nth-of-type(8)').contains(orderAmount).should('exist');
  });

  it("should be in student's account", () => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ENROLLED_COURSES}`);
    cy.loginAsStudent();
    cy.get('.tutor-course-name a').then(($links) => {
      const hrefs = Cypress._.map($links, (el) => el.getAttribute('href'));
      expect(hrefs).to.include(`${courseSlug}/`);
    });
  });

  it('should delete the course', () => {
    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      if (!fixture.courseId) throw new Error('Course ID not found in fixture');
      cy.deleteCourseById(fixture.courseId);
    });
  });
});
