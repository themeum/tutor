import { faker } from '@faker-js/faker';

describe('Purchase Course', () => {
  let orderId: string;
  let courseSlug: string;

  it('should purchase a paid course', () => {
    const billingData = {
      first_name: faker.person.firstName(),
      last_name: faker.person.lastName(),
      address: faker.location.streetAddress(),
      email: faker.internet.email(),
      city: faker.location.city(),
      zipcode: faker.location.zipCode(),
      phone: faker.phone.number(),
    };

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      const { slug } = fixture;
      if (!slug) {
        throw new Error('Course slug not found in fixture');
      }
      cy.log(`Course Slug: ${slug}`);
      courseSlug = slug;
      cy.visit(courseSlug);
    });

    cy.get('button#tutor-native-add-to-cart').click();
    cy.loginAsStudent();
    cy.get('body').then((body) => {
      if (body.find('a#tutor-native-view-cart').length) {
        cy.get('a#tutor-native-view-cart').click();
      } else {
        cy.get('button#tutor-native-add-to-cart').click();
        cy.get('a#tutor-native-view-cart').click();
      }
    });

    cy.wait(1000);
    cy.url().should('include', Cypress.env('cart'));

    cy.get('a#tutor-native-checkout-button').click();
    cy.wait(1000);
    cy.url().should('include', Cypress.env('checkout'));

    cy.getByInputName('billing_first_name').clear().type(billingData.first_name);
    cy.getByInputName('billing_last_name').clear().type(billingData.last_name);
    cy.getByInputName('billing_email').clear().type(billingData.email);
    cy.getByInputName('billing_city').clear().type(billingData.city);
    cy.get('select[name="billing_state"]').select(1);
    cy.getByInputName('billing_zip_code').clear().type(billingData.zipcode);
    cy.getByInputName('billing_address').clear().type(billingData.address);
    cy.getByInputName('billing_phone').clear().type(billingData.phone);
    cy.get('input[type="radio"][name="payment_method"][value="paypal"]').check({ force: true });

    cy.get('button#tutor-checkout-pay-now-button').click();

    cy.wait(1000);

    cy.origin('https://www.sandbox.paypal.com/', () => {
      cy.url().then((url) => {
        if (url.includes('https://www.sandbox.paypal.com/checkoutnow')) {
          cy.get('input[name=login_email]').type(Cypress.env('paypal_personal_email'));
          cy.get('button#btnNext').click();
          cy.get('body').then((body) => {
            if (body.find('.scTrack:secondaryLink').length) {
              cy.get('.scTrack:secondaryLink').click();
            }
          });
          cy.get('input[name=login_password]').type(Cypress.env('paypal_personal_password'));
          cy.get('button#btnLogin').click();

          cy.get('button#payment-submit-btn').click();
        }
      });
    });

    cy.url()
      .should('include', 'tutor_order_placement=success&order_id=')
      .then((url) => {
        const orderIdMatch = url.match(/order_id=(\d+)/);
        if (orderIdMatch) {
          orderId = orderIdMatch[1];
          cy.log(`Order ID: ${orderId}`);
        } else {
          throw new Error('Order ID not found in URL');
        }
      });

    cy.get('a#tutor-native-order-history').click();
  });

  // it('should verify the order in WooCommerce and mark as completed', () => {
  //   cy.visit(`${Cypress.env('base_url')}${backendUrls.WOOCOMMERCE_ORDERS}`);
  //   cy.loginAsAdmin();
  //   cy.getByInputName('s').clear().type(orderId);
  //   cy.get('input#search-submit').click();
  //   cy.wait(1000);
  //   cy.get('table>tbody#the-list>tr>td:nth-of-type(1)').contains(orderId).should('exist').click();
  //   cy.url().should('include', `page=wc-orders&action=edit&id=${orderId}`);
  //   cy.get('#select2-order_status-container').click();
  //   cy.get('#select2-order_status-results').contains('Completed').click();
  //   cy.get('button.save_order').click();
  // });

  // it('should verify the order status', () => {
  //   cy.visit(`${Cypress.env('base_url')}${backendUrls.WOOCOMMERCE_ORDERS}`);
  //   cy.loginAsAdmin();
  //   cy.getByInputName('s').clear().type(orderId);
  //   cy.get('input#search-submit').click();
  //   cy.wait(1000);
  //   cy.get('table>tbody#the-list>tr>td:nth-of-type(1)').contains(orderId).should('exist');
  //   cy.get('table>tbody#the-list>tr>td:nth-of-type(3)').contains('Completed').should('exist');
  // });

  // it("should be in student's account", () => {
  //   cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ENROLLED_COURSES}`);
  //   cy.loginAsStudent();
  //   cy.get('.tutor-course-name a').then(($links) => {
  //     const hrefs = Cypress._.map($links, (el) => el.getAttribute('href'));
  //     console.log(hrefs);
  //     expect(hrefs).to.include(`${courseSlug}/`);
  //   });
  // });
});
