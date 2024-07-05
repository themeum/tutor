import { backendUrls } from "../../config/page-urls";

describe("Tutor Students", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.STUDENTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.STUDENTS);
  });
  it("should be able to search any announcement", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "John Doe";
    const courseLinkSelector =
      ".tutor-d-flex.tutor-align-center.tutor-gap-1>span";
    const submitButtonSelector = "";
    const submitWithButton = false;

    cy.search(
      searchInputSelector,
      searchQuery,
      courseLinkSelector,
      submitButtonSelector,
      submitWithButton
    );
  });

  it("should perform bulk action on all students", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_student_bulk_action")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-form-check-input.tutor-bulk-checkbox")
          .eq(0)
          .click();
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

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.request.body).to.include(
            "tutor_student_bulk_action"
          );
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });
  });
});
