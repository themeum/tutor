import { frontendUrls } from "../../../config/page-urls";

describe("Tutor Dashboard Student Wishlist", () => {
  beforeEach(() => {
    cy.visit(
      `${Cypress.env("base_url")}/${frontendUrls.dashboard.ENROLLED_COURSES}`
    );
    cy.loginAsStudent();
    cy.url().should("include", frontendUrls.dashboard.ENROLLED_COURSES);
  });
  it("should show active courses", () => {
    cy.get(".tutor-nav-link")
      .eq(1)
      .click();
    cy.get(".list-item-button").each(($btn) => {
      cy.wrap($btn)
        .invoke("text")
        .then((text) => {
          expect(text.trim()).to.match(/^(Start Learning|Continue Learning)$/);
          cy.log(`Button text "${text.trim()}" matches expected values.`);
        });
    });
  });
  it("should show completed courses", () => {
    cy.get(".tutor-nav-link")
      .eq(2)
      .click();
      cy.get(".list-item-button").each(($btn) => {
        cy.wrap($btn)
          .invoke("text")
          .then((text) => {
            expect(text.trim()).to.match(/^(Download Certificate)$/);
            cy.log(`Button text "${text.trim()}" matches expected values.`);
          });
      });
  });
});
