import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Question and Answers", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.QUESTION_AND_ANSWER}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.QUESTION_AND_ANSWER);
  });

  it("should visit and reply a question", () => {
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

      cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Available in this Section")) {
              cy.log("No data found")
          } else {
              cy.get(".tutor-table tbody tr").eq(0).find("a").contains("Reply").click()
              cy.setTinyMceContent(".tutor-qna-reply-editor", "Hello there!")
              cy.get("button").contains("Reply").click()

              cy.wait('@ajaxRequest').then((interception) => {
                  expect(interception.response.body.success).to.equal(true);
              });

              cy.get("a").contains("Back").click({force:true})
          }
      })
  })

  it("should mark a question as read or unread", () => {
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

      cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Available in this Section")) {
              cy.log("No data found")
          } else {
              cy.get(".tutor-table tbody tr").eq(0).find(".tutor-icon-kebab-menu").parent().click()
              cy.get(".tutor-dropdown-parent.is-open .tutor-dropdown-item").eq(1).click()

              cy.wait('@ajaxRequest').then((interception) => {
                  expect(interception.response.body.success).to.equal(true)
              })
          }
      })
  })

  it("should archive a question", () => {
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

      cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Available in this Section")) {
              cy.log("No data found")
          } else {
              cy.get(".tutor-table tbody tr").eq(0).find(".tutor-icon-kebab-menu").parent().click()
              cy.get(".tutor-dropdown-parent.is-open .tutor-dropdown-item").eq(0).click()

              cy.wait('@ajaxRequest').then((interception) => {
                  expect(interception.response.body.success).to.equal(true)
              })
          }
      })
  })

  it("should delete a question", () => {
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

      cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Available in this Section")) {
              cy.log("No data found")
          } else {
              cy.get(".tutor-table tbody tr").eq(0).find(".tutor-icon-kebab-menu").parent().click()
              cy.get(".tutor-dropdown-parent.is-open .tutor-dropdown-item").eq(2).click()
              cy.get(".tutor-modal.tutor-is-active").find("button").contains("Yes, Delete This").click()

              cy.wait('@ajaxRequest').then((interception) => {
                  expect(interception.response.body.success).to.equal(true)
              })
          }
      })
  })

  it("should be able to search any q&a", () => {
      const searchInputSelector = "#tutor-backend-filter-search";
      const searchQuery = "test";
      const courseLinkSelector = ".tutor-qna-content.tutor-fs-6.tutor-fw-bold.tutor-color-black>p";
      const submitButtonSelector=""
      const submitWithButton=false;

      cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
    });

  it("should perform bulk action on a q&a", () => {
    cy.intercept(
        "POST",
        `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
        (req) => {
          if (req.body.includes("delete")) {
            req.alias = "ajaxRequest";
          }
        }
      );
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        
        cy.getByInputName("tutor-bulk-checkbox-all").eq(0).check();
        cy.get(".tutor-mr-12 > .tutor-js-form-select").click();
        cy.get(
          ".tutor-mr-12 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis"
        )
          .contains("Delete Permanently")
          .click();
        cy.get("#tutor-admin-bulk-action-btn").click();
        cy.get("#tutor-confirm-bulk-action")
          .contains("Yes, I'am Sure")
          .click();
        // cy.contains("No Data Available in this Section");
        cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);
          });
      }
    });
  });
});
