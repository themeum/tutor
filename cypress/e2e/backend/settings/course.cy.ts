import { backendUrls } from "../../../config/page-urls";

describe("Tutor settings course", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.SETTINGS_COURSE}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.SETTINGS_COURSE);
  });

  it("should enable or disable course visibility", () => {
    const inputName = "tutor_option[student_must_login_to_view_course]";
    const fieldId = "#field_student_must_login_to_view_course";
    cy.toggle(inputName, fieldId);
  });

  it("should enable or disable course content acess", () => {
    const inputName = "tutor_option[course_content_access_for_ia]";
    const fieldId = "#field_course_content_access_for_ia";
    cy.toggle(inputName, fieldId);
  });

  it("should enable or disable showing content summary", () => {
    const inputName = "tutor_option[course_content_summary]";
    const fieldId = "#field_course_content_summary";
    cy.toggle(inputName, fieldId);
  });

  it("should enable or disable course content acess", () => {
    const inputName = "tutor_option[course_content_access_for_ia]";
    const fieldId = "#field_course_content_access_for_ia";
    cy.toggle(inputName, fieldId);
  });
  it("should enable or disable course visibility", () => {
    const inputName = "tutor_option[student_must_login_to_view_course]";
    const fieldId = "#field_student_must_login_to_view_course";
    cy.toggle(inputName, fieldId);
  });

});
