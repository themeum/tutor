import { frontendUrls } from "../../../config/page-urls";

describe("Tutor Admin Dashboard Journey", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}${frontendUrls.dashboard.ANALYTICS}`);
    cy.loginAsInstructor();
  });
  it("should visit all analytics pages", () => {
    cy.get("body").then(($body) => {
      cy.get(".tutor-nav>.tutor-nav-item")
        .eq(1)
        .click();
      if ($body.find(".tutor-nav").length) {
        cy.get(
          ".tutor-nav-link:not(.is-active):not(.tutor-nav-more-item)"
        ).each(($item) => {
          cy.wrap($item)
            .invoke("attr", "href")
            .then((link) => {
              cy.visit(link);
              cy.url().should("eq", `${link}${link.endsWith("/") ? "" : "/"}`);
              console.log(link);
              cy.get(`a[href="${link}"]`).should("have.class", "is-active");
            });
        });
      }
    });
  });
  // courses
  it("should be able to search courses", () => {
    cy.get(".tutor-nav>.tutor-nav-item")
      .contains("Courses")
      .click();
    const searchInputSelector = "input[name='search']";
    const searchQuery = "javascript";
    const courseLinkSelector = "tbody>tr>td>span";
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
  // statements
  it("should filter statements", () => {
    cy.get(".tutor-nav>.tutor-nav-item")
      .contains("Statements")
      .click();
    cy.get(
      ".tutor-form-control.tutor-form-select.tutor-js-form-select"
    ).click();
    cy.get(".tutor-form-select-options span[tutor-dropdown-item]").then(
      ($options) => {
        const randomIndex = Cypress._.random(1, $options.length - 1);
        const $randomOption = $options.eq(randomIndex);
        cy.wrap($randomOption).click({ force: true });
        const selectedOptionText = $randomOption.text().trim();
        cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Found from your Search/Filter")) {
            cy.log("No data available");
          } else {
            cy.get(".td-statement-info >.tutor-mt-8")
              .eq(0)
              .each(($announcement) => {
                cy.wrap($announcement).should(
                  "contain.text",
                  selectedOptionText
                );
              });
          }
        });
      }
    );
  });
  // students
  it("should be able to search students", () => {
    cy.get(".tutor-nav>.tutor-nav-item")
      .contains("Students")
      .click();
    const searchInputSelector = "input[name='search']";
    const searchQuery = "tutor";
    const courseLinkSelector = ".tutor-ml-16>div";
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

  // export
  it("should be able to dowonload csv", () => {
    cy.get(".tutor-nav-link")
      .contains("Export")
      .click();
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("export_analytics")) {
          req.alias = "ajaxRequest";
        }
      }
    );
    cy.get("#download_analytics").click();
    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

});
