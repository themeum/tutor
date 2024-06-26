import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GRADEBOOK}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.GRADEBOOK);
  });
  //   grade settings
  

});
