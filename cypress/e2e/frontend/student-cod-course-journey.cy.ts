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

            cy.get(".wc-block-components-radio-control__option")
              .eq(1)
              .contains("Cash on delivery")
              .click();

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

            cy.visit(`${Cypress.env("base_url")}/wp-admin/admin.php?page=wc-orders`)
            // cy.get("#wp-admin-bar-site-name>a.ab-item").click();
            // cy.get(
            //   "#toplevel_page_woocommerce > .wp-has-submenu > .wp-menu-name"
            // )
            //   .contains("WooCommerce")
            //   .click();
            // cy.wait(2000);
            // cy.get(".wp-submenu-wrap")
            //   .contains("li", "Orders")
            //   .click();

            // cy.get("input[type='checkbox']").eq(1).click()

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

            cy.visit(`${Cypress.env("base_url")}/courses/${Cypress.env("cod_course_slug")}/}`)
          }
        });
      }
    });

      cy.get("body").then(($body) => {
        // start the course
        if ($body.text().includes("Retake This Course")) {
          cy.get(".tutor-btn.tutor-btn-block.tutor-btn-outline-primary.start-continue-retake-button.tutor-course-retake-button")
            .contains("Retake This Course")
            .click();
          cy.get("button")
            .contains("Reset Data")
            .click();

          cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);
          });
        } else if ($body.text().includes("Continue Learning")) {
          cy.get("a")
            .contains("Continue Learning")
            .click();
        } else if ($body.text().includes("Start Learning")) {
          cy.get("a")
            .contains("Start Learning")
            .click();
        }
      });

    //   cy.url().should("include", `${Cypress.env("cod_course_slug")}`);

          cy.get(".tutor-course-topic-item").each(($topic, index, $list) => {
            const isLastItem = index === $list.length - 1;

            cy.url().then(($url) => {
              if ($url.includes("/lesson")) {
                cy.get("body").then(($body) => {
                  if ($body.text().includes("Mark as Complete")) {
                    cy.get("button")
                      .contains("Mark as Complete")
                      .click();
                    cy.wait(1000);
                    cy.get("body").should("not.contain", "Mark as Complete");
                  }
                });

                cy.get("a")
                  .contains("Next")
                  .parent()
                  .then(($element) => {
                    if ($element.attr("disabled")) {
                      cy.get(
                        ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
                      )
                        .parent()
                        .click();
                    } else {
                      cy.wrap($element).click();
                    }
                  });
              }

              if ($url.includes("/assignments")) {
                cy.get("body").then(($body) => {
                  if (
                    $body
                      .text()
                      .includes(
                        "You have missed the submission deadline. Please contact the instructor for more information."
                      )
                  ) {
                    cy.get("a")
                      .contains("Skip To Next")
                      .click();
                  }
                });

                cy.get("body").then(($body) => {
                  if ($body.text().includes("Start Assignment Submit")) {
                    cy.get("#tutor_assignment_start_btn").click();
                    cy.wait("@ajaxRequest").then((interception) => {
                      expect(interception.response.statusCode).to.equal(200);
                    });
                    cy.url().should("include", "assignments");

                    cy.setTinyMceContent(
                      ".tutor-assignment-text-area",
                      "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sit amet purus lacinia diam pretium interdum. Nullam elementum tincidunt ipsum vel fringilla."
                    );
                    cy.get("#tutor_assignment_submit_btn").click();
                    cy.get("body").should("contain", "Your Assignment");
                  }
                });

                cy.get("body").then(($body) => {
                  if ($body.text().includes("Submit Assignment")) {
                    cy.setTinyMceContent(
                      ".tutor-assignment-text-area",
                      "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sit amet purus lacinia diam pretium interdum. Nullam elementum tincidunt ipsum vel fringilla."
                    );
                    cy.get("#tutor_assignment_submit_btn").click();
                    cy.get("body").should("contain", "Your Assignment");
                  }
                });

                cy.get("body").then(($body) => {
                  if ($body.text().includes("Continue Lesson")) {
                    cy.get("a")
                      .contains("Continue Lesson")
                      .click();
                  } else if (isLastItem) {
                    cy.get(
                      ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
                    )
                      .parent()
                      .click();
                  }
                });
              }

              if ($url.includes("/quizzes")) {
                cy.get("body").then(($body) => {
                  if ($body.text().includes("Start Quiz")) {
                    cy.get("button[name=start_quiz_btn]").click();
                  }

                  if (
                    $body.text().includes("Start Quiz") ||
                    $body.text().includes("Submit & Next") ||
                    $body.text().includes("Submit Quiz")
                  ) {
                    cy.get(".quiz-attempt-single-question").each(
                      ($question, $index) => {
                        if ($question.find("textarea").length) {
                          cy.wrap($question)
                            .find("textarea")
                            .type(
                              "It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout."
                            );
                        }

                        if ($question.find("input[type=text]").length) {
                          cy.wrap($question)
                            .find("input[type=text]")
                            .each(($input) => {
                              cy.wrap($input).type("Example question answer.");
                            });
                        }

                        if (
                          $question.find("#tutor-quiz-single-multiple-choice")
                            .length
                        ) {
                          cy.wrap($question)
                            .find(".tutor-quiz-answer-single")
                            .eq(0)
                            .find("input")
                            .click();
                        }

                        cy.get("button.tutor-quiz-next-btn-all")
                          .eq($index)
                          .click();
                      }
                    );
                  }

                  cy.get("a")
                    .contains("Next")
                    .parent()
                    .then(($element) => {
                      if ($element.attr("disabled")) {
                        cy.get(
                          ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
                        )
                          .parent()
                          .click();
                      } else {
                        cy.wrap($element).click();
                      }
                    });
                });
              }

              if ($url.includes("/meet-lessons")) {
                cy.get("body").then(($body) => {
                  if ($body.text().includes("Mark as Complete")) {
                    cy.get("button")
                      .contains("Mark as Complete")
                      .click();
                  } else {
                    if (isLastItem) {
                      cy.get(
                        ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
                      )
                        .parent()
                        .click();
                    } else {
                      cy.get(".tutor-course-topic-item")
                        .eq(index)
                        .children("a")
                        .click({ force: true });
                    }
                  }
                });
              }

              if ($url.includes("/zoom-lessons")) {
                cy.get("body").then(($body) => {
                  if ($body.text().includes("Mark as Complete")) {
                    cy.get("button")
                      .contains("Mark as Complete")
                      .click();
                  } else {
                    if (isLastItem) {
                      cy.get(
                        ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
                      )
                        .parent()
                        .click();
                    } else {
                      cy.get(".tutor-course-topic-item")
                        .eq(index)
                        .children("a")
                        .click({ force: true });
                    }
                  }
                });
              }
            });
          });

      cy.get("body").then(($body) => {
        if ($body.text().includes("Complete Course")) {
          cy.get("button")
            .contains("Complete Course")
            .click();
        }
      });

      // Review submit after course complete
      cy.get("body").then(($body) => {
        if ($body.text().includes("How would you rate this course?")) {
          cy.get(".tutor-modal-content .tutor-icon-star-line")
            .eq(4)
            .click();
          cy.get(".tutor-modal-content textarea[name=review]").type(
            "Just completed a course on TutorLMS, and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze. The interactive quizzes and discussions keep you engaged, and the user-friendly interface enhances the overall experience. The flexibility to learn at your own pace is a game-changer for busy professionals."
          );
          cy.get(".tutor-modal-content button.tutor_submit_review_btn").click();

          cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);
          });

          cy.wait(5000);
        }
      });

      // View certificate
      cy.get("body").then(($body) => {
        if ($body.text().includes("View Certificate")) {
          cy.get("a")
            .contains("View Certificate")
            .click();
          cy.url().should("include", "tutor-certificate");
          cy.wait(5000);
        }
      });
  });
});
