import { frontendUrls } from "../../../config/page-urls";

describe("Tutor Dashboard Quiz Attempts", () => {
  beforeEach(() => {
    cy.visit(
      `${Cypress.env("base_url")}/${frontendUrls.dashboard.QUIZ_ATTEMPTS}`
    );
    cy.loginAsInstructor();
    cy.url().should("include", frontendUrls.dashboard.QUIZ_ATTEMPTS);
  });

  it("should check if the elements are sorted", () => {
    const formSelector = ":nth-child(2) > .tutor-js-form-select";
    const itemSelector = ".tutor-mt-4";
    function checkSorting(order) {
      cy.get(formSelector).click();
      cy.get(`span[title=${order}]`).click();
      cy.get("body").then(($body) => {
        if (
          $body.text().includes("No Data Found from your Search/Filter") ||
          $body.text().includes("No request found") ||
          $body.text().includes("No Data Available in this Section") ||
          $body.text().includes("No records found") ||
          $body.text().includes("No Records Found")
        ) {
          cy.log("No data available");
        } else {
          cy.get(itemSelector).then(($items) => {
            const itemTexts = $items
              .map((index, item) => item.innerText.trim())
              .get()
              .filter((text) => text);
            const sortedItems =
              order === "ASC" ? itemTexts.sort() : itemTexts.sort().reverse();
            expect(itemTexts).to.deep.equal(sortedItems);
          });
        }
      });
    }
    checkSorting("ASC");
    checkSorting("DESC");
  });

  it("should review a quiz", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-table-quiz-attempts a")
          .eq(0)
          .click();
        cy.window().scrollTo("bottom", { duration: 500, easing: "linear" });
        cy.setTinyMceContent(
          ".tutor-instructor-feedback-wrap",
          "Nice work! You got it right. If not, don't worry—just a small tweak needed. Keep it up!"
        );
        cy.get(
          ".quiz-attempt-answers-wrap button.tutor-instructor-feedback"
        ).click();

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.body.success).to.equal(true);
        });
        cy.get(".tutor-quiz-attempt-details-wrapper a")
          .contains("Back")
          .click();
      }
    });
  });
  it("should delete a quiz attempt", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-quiz-attempt-delete")
          .eq(0)
          .click();
        cy.get(".tutor-btn.tutor-btn-primary.tutor-ml-16")
          .contains("Yes, I'am Sure")
          .click();
        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });
  });
});
