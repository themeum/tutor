import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Assignments", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.ASSIGNMENTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.ASSIGNMENTS);
  });

  it("should evaluate an assignment", () => {
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No request found") ||
        $body.text().includes("No Data Available in this Section") ||
        $body.text().includes("No records found") ||
        $body.text().includes("No Records Found")
      ) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-table-assignments tbody tr")
          .eq(0)
          .then(($row) => {
            if ($row.text().includes("Evaluate")) {
              cy.wrap($row)
                .find("a")
                .contains("Evaluate")
                .click();
            } else {
              cy.wrap($row)
                .find("a")
                .contains("Details")
                .click();
            }
            cy.url().should("include", "view_assignment");
            cy.url().then((url) => {
              cy.intercept(
                "POST",
                `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
              ).as("ajaxRequest");

              cy.get("input[type=number]")
                .clear()
                .type("5");
              cy.get("textarea")
                .clear()
                .type(
                  "The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking."
                );
              cy.get("button")
                .contains("Evaluate this submission")
                .click();

              cy.wait("@ajaxRequest").then((interception) => {
                expect(interception.response.body.success).to.equal(true);
              });

              cy.get("a")
                .contains("Back")
                .click();
            });
          });
      }
    });
  });

  it("should be able to search any assignement", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "assignment";
    const courseLinkSelector = "td>a";
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

  it("should check if the elements are sorted", () => {
    const formSelector =
      ":nth-child(3) > .tutor-form-control.tutor-form-select.tutor-js-form-select";
    const itemSelector = "tbody>tr>td>a";
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

  it("should filter assignments", () => {
    const filterFormSelector = ":nth-child(2) > .tutor-js-form-select";
    const dropdownSelector = ".tutor-form-select-options";
    const dropdownOptionSelector = ".tutor-form-select-option";
    const dropdownTextSelector = "span[tutor-dropdown-item]";
    const elementTitleSelector = ".tutor-fw-normal";
    cy.filterElements(
      filterFormSelector,
      dropdownSelector,
      dropdownOptionSelector,
      dropdownTextSelector,
      elementTitleSelector
    );
  });

  it("Should filter assignments by a specific date", () => {
    const filterFormSelector =
      ".react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control";
    const elementDateSelector = ".tutor-fs-7 > span";
    cy.filterElementsByDate(filterFormSelector, elementDateSelector);
  });
});
