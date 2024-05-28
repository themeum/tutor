describe("Tutor Student Paid Course Journey", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${Cypress.env("cod_course_slug")}/`);
  });

  it("should be able to enroll in a paid course for cash on delivery, view cart, and manage items", () => {
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
        cy.url().should("include", Cypress.env("cod_course_slug"));

        cy.get("body").then(($body) => {
          if ($body.find("button[name='add-to-cart']").length > 0) {
            cy.get("button[name='add-to-cart']")
              .contains("Add to cart")
              .click();
          }
          if ($body.find(".tutor-woocommerce-view-cart").length > 0) {
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
            // fill up the checkout form
            // contact info
            cy.get("#email")
              .clear()
              .type("johndoe@gmail.com");
            // billing address
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

            cy.contains("Cash on delivery").click();

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

            cy.url().should("include", "/order-received");

            //   redirect to admin dashboard
            cy.visit(
              `${Cypress.env("base_url")}/wp-admin/admin.php?page=wc-orders`
            );

            cy.get("input[name='id[]']")
              .eq(0)
              .invoke("attr", "value")
              .then((value) => {
                const selector = `#cb-select-${value}`;
                console.log(selector);
                // Use the dynamic selector to perform actions
                cy.get(selector)
                  .should("be.visible")
                  .check();
              });

            cy.get("#bulk-action-selector-top")
              .select("Change status to completed")
              .should("have.value", "mark_completed");

            cy.get("#doaction")
              .contains("Apply")
              .click();

            // redirect to course

            cy.visit(
              `${Cypress.env("base_url")}/courses/${Cypress.env(
                "cod_course_slug"
              )}/}`
            );
          }
        });
      }
    });

    cy.handleCourseStart();

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
