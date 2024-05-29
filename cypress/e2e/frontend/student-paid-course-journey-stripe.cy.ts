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
        // Login as a student
        cy.getByInputName("log").type(Cypress.env("student_username"));
        cy.getByInputName("pwd").type(Cypress.env("student_password"));
        cy.get("#tutor-login-form button")
          .contains("Sign In")
          .click();
        cy.url().should("include", Cypress.env("paid_course_slug"));

        cy.get("body").then(($body) => {
          if ($body.find("button[name='add-to-cart']").length > 0) {
            cy.get("button[name='add-to-cart']")
              .contains("Add to cart")
              .click();
          } else if ($body.find(".tutor-woocommerce-view-cart").length > 0) {
            cy.get(".tutor-woocommerce-view-cart")
              .contains("View Cart")
              .click();
          }
        });

        cy.url().then((url) => {
          if (url.includes("/cart")) {
            cy.get(".wc-block-cart__submit-button")
              .contains("Proceed to Checkout")
              .click();
            cy.url().should("include", "/checkout");

            cy.get("#billing_first_name")
              .clear()
              .type("Guest");
            cy.get("#billing_last_name")
              .clear()
              .type("Test");
            cy.get("#billing_company")
              .clear()
              .type("Company");

            cy.get(".select2-selection.select2-selection--single")
              .eq(0)
              .click();
            cy.get("#select2-billing_country-results").then((options) => {
              const randomIndex = Math.floor(Math.random() * options.length);
              cy.wrap(options[randomIndex]).click();
            });

            cy.get("#billing_address_1")
              .clear()
              .type("123 Main Street");
            cy.get("#billing_address_2")
              .clear()
              .type("Apt 4B");

            cy.get("#billing_city")
              .clear()
              .type("Dhaka");

            cy.get(".select2-selection.select2-selection--single")
              .eq(1)
              .click();
            cy.get("#select2-billing_state-results").then((options) => {
              const randomIndex = Math.floor(Math.random() * options.length);
              cy.wrap(options[randomIndex]).click();
            });

            cy.get("#billing_postcode")
              .clear()
              .type("96799");
            cy.get("#billing_phone")
              .clear()
              .type("+8801555123456");

            const randomEmail = `guest${Math.random()
              .toString()
              .slice(2)}@gmail.com`;

            cy.get("#billing_email")
              .clear()
              .type(randomEmail);

            cy.get("#payment_method_stripe").click();

            // if previous cards are added

            cy.get("ul.woocommerce-SavedPaymentMethods").then(($ul) => {
              // Check if there are more than one li items
              if ($ul.children("li").length > 1) {
                // Click the "Use a new payment method" option
                cy.get("#wc-stripe-payment-token-new")
                  .check()
                  .should("be.checked");
              }
            });

            // card number
            cy.frameLoaded(
              "#stripe-card-element > .__PrivateStripeElement > iframe"
            );

            cy.iframe(
              "#stripe-card-element > .__PrivateStripeElement > iframe"
            ).within(() => {
              cy.get('input[name="cardnumber"]').type("4242424242424242");
            });
            // card expiry date
            cy.frameLoaded(
              "#stripe-exp-element > .__PrivateStripeElement > iframe"
            );

            cy.iframe(
              "#stripe-exp-element > .__PrivateStripeElement > iframe"
            ).within(() => {
              cy.get('input[name="exp-date"]').type("12/25");
            });

            // cvv
            cy.frameLoaded(
              "#stripe-cvc-element > .__PrivateStripeElement > iframe"
            );

            cy.iframe(
              "#stripe-cvc-element > .__PrivateStripeElement > iframe"
            ).within(() => {
              cy.get('input[name="cvc"]').type("123");
            });
            // jwt error
            cy.get("body").then(($body) => {
              if ($body.find(".tutor-icon-times").length > 0) {
                cy.get(".tutor-icon-times").click();
              }
            });

            cy.get("#place_order").click();

            cy.wait("@ajaxRequest", { timeout: 15000 }).then((interception) => {
              expect(interception.response.body.success).to.equal(true);
            });

            cy.url().should("include", "/enrolled-courses");

            cy.get(".tutor-course-name")
              .eq(0)
              .click();
          }
        });
      }
    });

    cy.handleCourseStart();

    cy.url().should("include", Cypress.env("paid_course_slug"));

    cy.isEnrolled().then((isEnrolled) => {
      if (isEnrolled) {
        cy.get(".tutor-course-topic-item").each(($topic, index, $list) => {
          const isLastItem = index === $list.length - 1;

          cy.url().then(($url) => {
            if ($url.includes("/lesson")) {
              cy.completeLesson();
              cy.handleNextButton();
            }

            if ($url.includes("/assignments")) {
              cy.handleAssignment();
            }

            if ($url.includes("/quizzes")) {
              cy.handleQuiz();
            }

            if ($url.includes("/meet-lessons")) {
              cy.handleMeetingLesson(isLastItem);
            }

            if ($url.includes("/zoom-lessons")) {
              cy.handleZoomLesson(isLastItem);
            }
          });
        });
      }
    });

    cy.completeCourse();
    cy.submitCourseReview();
    cy.viewCertificate();
  });
});
