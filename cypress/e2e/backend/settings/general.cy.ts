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

  it("should enable or disable marketplace", () => {
    const inputName = "tutor_option[enable_course_marketplace]";
    const idName = "#field_enable_course_marketplace";
    cy.toggle(inputName, idName);
  });

  it("should check pagination value", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_option_save")) {
          req.alias = "ajaxRequest";
        }
      }
    );
    cy.getByInputName("tutor_option[pagination_per_page]")
      .invoke("attr", "value")
      .then((dataValue) => {
        cy.contains("Save Changes").click({ force: true });
        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.body.success).to.equal(true);

          const requestBody = interception.request.body;
          const params = new URLSearchParams(requestBody);

          const tutorOptionId = params.get("tutor_option[pagination_per_page]");

          expect(tutorOptionId).to.equal(dataValue);
        });
      });
  });

  it("should allow or disallow instructors to publish courses", () => {
    const inputName = "tutor_option[instructor_can_publish_course]";
    const idName = "#field_instructor_can_publish_course";
    cy.toggle(inputName, idName);
  });

  it("should enable or disable become an intructor", () => {
    const inputName = "tutor_option[enable_become_instructor_btn]";
    const idName = "#field_enable_become_instructor_btn";
    cy.toggle(inputName, idName);
  });
});
