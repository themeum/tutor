describe("Tutor Student Paid Course Journey", () => {
    beforeEach(() => {
      cy.visit(`${Cypress.env("base_url")}/${Cypress.env("paid_course_slug")}/`);
    });
  
    it("should be able to enroll in a paid course, view cart, and manage items", () => {
      cy.intercept(
        "POST",
        `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
      ).as("ajaxRequest");
  
      cy.isEnrolled().then((isEnrolled) => {
        if (!isEnrolled) {
          cy.get("button[name='add-to-cart']")
            .contains("Add to cart")
            .click();
  
          cy.url().then((url) => {
            if (url.includes("/cart")) {
              cy.get(".wc-block-cart__submit-button")
                .contains("Proceed to Checkout")
                .click();
              cy.url().should("include", "/checkout");
              
              cy.get("#email")
                .clear()
                .type("johndoe3@gmail.com");
              cy.get("#billing-first_name")
                .clear()
                .type("John");
              cy.get("#billing-last_name")
                .clear()
                .type("Doe");
              cy.get("#billing-address_1")
                .clear()
                .type("123 Main Street");
              cy.get("#billing-address_2")
                .clear()
                .type("Apt 4B");
  
              cy.get("#components-form-token-input-0").click();
              cy.get("#components-form-token-suggestions-0").then((options) => {
                const randomIndex = Math.floor(Math.random() * options.length);
                cy.wrap(options[randomIndex]).click();
              });
  
              cy.get("#billing-city")
                .clear()
                .type("Dhaka");
  
              cy.get("#billing-state")
                .clear()
                .type("Dhaka");
  
              cy.get("#billing-postcode")
                .clear()
                .type("96799");
              cy.get("#billing-phone")
                .clear()
                .type("+8801555123456");
  
              cy.get(
                "#radio-control-wc-payment-method-options-stripe__label > .wc-block-components-payment-method-label"
              ).click();
              cy.get(".wc-card-number-element > label").click();
  
              // card number
              cy.frameLoaded(
                "#wc-stripe-card-number-element > .__PrivateStripeElement > iframe"
              );
  
              cy.iframe(
                "#wc-stripe-card-number-element > .__PrivateStripeElement > iframe"
              ).within(() => {
                cy.get('input[name="cardnumber"]').type("4242424242424242");
              });
  
              // card expiry date
              cy.get('.wc-card-expiry-element > label').click()
              cy.frameLoaded(
                "#wc-stripe-card-expiry-element > .__PrivateStripeElement > iframe"
              );
  
              cy.iframe(
                "#wc-stripe-card-expiry-element > .__PrivateStripeElement > iframe"
              ).within(() => {
                cy.get('input[name="exp-date"]').type("12/25");
              });
  
              // cvv
              cy.get('.wc-card-cvc-element > label').click()
              cy.frameLoaded(
                "#wc-stripe-card-code-element > .__PrivateStripeElement > iframe"
              );
  
              cy.iframe(
                "#wc-stripe-card-code-element > .__PrivateStripeElement > iframe"
              ).within(() => {
                 cy.get('input[name="cvc"]').type("123");
              });
  
              // add a note to order
              cy.get("#checkbox-control-0").click();
              cy.get(".wc-block-components-textarea")
                .clear()
                .type("Great service");
  
              cy.get("body").then(($body) => {
                if ($body.find(".tutor-icon-times").length > 0) {
                  cy.get(".tutor-icon-times").click();
                }
              });
  
              cy.contains("Place Order").click();
  
              cy.wait('@ajaxRequest', { timeout: 10000 }).then((interception) => { 
                expect(interception.response.body.success).to.equal(true);
              });

              cy.visit(`${Cypress.env("base_url")}/${Cypress.env("paid_course_slug")}/`);
  
            }
          });
        }
      });
    });
  });
  