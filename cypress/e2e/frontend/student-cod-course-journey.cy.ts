describe("Tutor Student Paid Course Journey", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/courses/${Cypress.env("cod_course_slug")}/`);
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

            cy.contains("Cash on delivery").click();

            cy.get("body").then(($body) => {
              if ($body.find(".tutor-icon-times").length > 0) {
                cy.get(".tutor-icon-times").click();
              }
            });

            cy.contains("Place order").click();

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
