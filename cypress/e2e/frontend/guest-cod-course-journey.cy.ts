describe("Tutor Student Paid Course Journey", () => {
  beforeEach(() => {
    cy.visit(
      `${Cypress.env("base_url")}/courses/${Cypress.env("paid_course_slug")}/`
    );
  });

  it("should be able to enroll in a paid course, view cart, and manage items as a guest", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");

    cy.isEnrolled().then((isEnrolled) => {
      if (!isEnrolled) {
        cy.get("button[name='add-to-cart']")
          .contains("Add to cart")
          .click();

          // cy.url().then((url) => {
          //   if (url.includes("/cart")) {
          //     cy.get(".wc-block-cart__submit-button")
          //       .contains("Proceed to Checkout")
          //       .click();
          //     cy.url().should("include", "/checkout");

          //     const randomEmail = `guest${Math.random()
          //             .toString()
          //             .slice(2)}@gmail.com`;

          //     cy.get("#email").clear().type(randomEmail)
  
          //     cy.get("#billing-first_name")
          //       .clear()
          //       .type("Guest1");
          //     cy.get("#billing-last_name")
          //       .clear()
          //       .type("Test");
          //     cy.get("#billing-address_1")
          //       .clear()
          //       .type("123 Main Street");
  
          //     cy.get("#billing-city")
          //       .clear()
          //       .type("New York");
  
          //     cy.get("#components-form-token-input-1")
          //       .clear()
          //       .type("Florida");
  
          //     cy.get("#billing-postcode")
          //       .clear()
          //       .type("96799");
          //     cy.get("#billing-phone")
          //       .clear()
          //       .type("+8801555123456");
  
          //     cy.get(".wc-block-components-radio-control-accordion-option")
          //       .eq(2)
          //       .then(() => {
          //         cy.get(
          //           ":nth-child(3) > .wc-block-components-radio-control__option"
          //         ).click();
          //       });
  
          //     cy.get("body").then(($body) => {
          //       if ($body.find(".tutor-icon-times").length > 0) {
          //         cy.get(".tutor-icon-times").click();
          //       }
          //     });
  
          //     // cy.get('button')
          //     //   .contains("Place Order")
          //     //   .click({force:true});

          //     cy.get('.wc-block-components-button').click({force:true})
  
          //     cy.url().should("include", "/order-received");
  
          //     // redirect to admin dashboard and login
          //     cy.visit(`${Cypress.env("base_url")}/wp-login.php`);
          //     cy.loginAsAdmin();
          //     cy.visit(
          //       `${Cypress.env("base_url")}/wp-admin/admin.php?page=wc-orders`
          //     );
  
          //     cy.get("input[name='id[]']")
          //       .eq(0)
          //       .invoke("attr", "value")
          //       .then((value) => {
          //         const selector = `#cb-select-${value}`;
  
          //         cy.get(selector)
          //           .should("be.visible")
          //           .check();
          //       });
  
          //     cy.get("#bulk-action-selector-top")
          //       .select("Change status to completed")
          //       .should("have.value", "mark_completed");
  
          //     cy.get("#doaction")
          //       .contains("Apply")
          //       .click();
  
          //     // redirect to course
          //     cy.visit(
          //       `${Cypress.env("base_url")}/courses/${Cypress.env(
          //         "cod_course_slug"
          //       )}/}`
          //     );
          //   }
          // });

        cy.url().then((url) => {
          if (url.includes("/cart")) {
            cy.get(".wc-block-cart__submit-button")
              .contains("Proceed to Checkout")
              .click();
            cy.url().should("include", "/checkout");

                const randomEmail = `guest${Math.random()
                      .toString()
                      .slice(2)}@gmail.com`;

              cy.get("#email").clear().type(randomEmail)
  
              cy.get("#billing-first_name")
                .clear()
                .type("Guest1");
              cy.get("#billing-last_name")
                .clear()
                .type("Test");
              cy.get("#billing-address_1")
                .clear()
                .type("123 Main Street");
  
              cy.get("#billing-city")
                .clear()
                .type("New York");
  
              cy.get("#components-form-token-input-1")
                .clear()
                .type("Florida");
  
              cy.get("#billing-postcode")
                .clear()
                .type("96799");
              
            cy.get("#billing-phone")
              .clear()
              .type("+8801555123456");

              cy.get(':nth-child(3) > .wc-block-components-radio-control__option').click()

        //     // accept terms
        //     cy.get("#terms").check();

            cy.get("button").contains("Place Order").click();

            cy.url().should("include", "/checkout");

            // login as admin
            cy.visit(`${Cypress.env("base_url")}/wp-login.php`);
            // Login as a admin
            cy.loginAsAdmin()

            
            // redirect to admin dashboard
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
          }
        });
      }
    });

    cy.visit(`${Cypress.env("base_url")}/dashboard-2-2/enrolled-courses/`);
    cy.get(".tutor-course-name")
      .eq(0)
      .click();

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
              cy.handleAssignment(isLastItem);
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
