import { backendUrls } from "../../../config/page-urls";

describe("Tutor settings general", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.SETTINGS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.SETTINGS);
  });

  it("should be able to select dashboard page", () => {
    cy.selectPageFromDropdownAndSaveChanges(
      "#field_tutor_dashboard_page_id",
      "Save Changes",
      "tutor_option[tutor_dashboard_page_id]",
      "data-value"
    );
  });

  it("should be able to select terms and consitions page", () => {
    cy.selectPageFromDropdownAndSaveChanges(
      "#field_tutor_toc_page_id",
      "Save Changes",
      "tutor_option[tutor_toc_page_id]",
      "data-value"
    );
  });
});
