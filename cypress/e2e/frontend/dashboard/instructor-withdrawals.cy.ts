import { frontendUrls } from "../../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.WITHDRAWS}`);
    cy.loginAsInstructor();
    cy.url().should("include", frontendUrls.dashboard.WITHDRAWS);
  });
  it("should make withdrawal request", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_make_an_withdraw")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");

    cy.get("button")
      .contains("Withdrawal Request")
      .click();
    cy.get("input[name='tutor_withdraw_amount']")
      .clear()
      .type("1000");
    cy.get("#tutor-earning-withdraw-btn").click();
    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.request.body).to.include("tutor_make_an_withdraw");
      expect(interception.response.body.success).to.equal(true);
    });
  });
});
