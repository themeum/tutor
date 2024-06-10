import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.COURSES}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.COURSES);
  });

  it("should filter by category", () => {
    cy.filterByCategory();
  });
  it("should check if the elements are sorted", () => {
    const formSelector = ":nth-child(3) > .tutor-js-form-select";
    const itemSelector =
     ".tutor-d-flex.tutor-align-center.tutor-gap-2 > div > a.tutor-table-link";
    function checkSorting(order) {
      cy.get(formSelector).click();
      cy.get(`span[title=${order}]`).click()
      cy.get("body").then(($body) => {
        if (
          $body.text().includes("No Data Available in this Section")
        ) {
          cy.log("No data available");
        }else{
          cy.get(itemSelector).then(($items) => {
            const itemTexts = $items.map((index, item) => item.innerText.trim()).get().filter(text => text);
            const sortedItems = order === 'ASC' ? itemTexts.sort() : itemTexts.sort().reverse();
            expect(itemTexts).to.deep.equal(sortedItems);
          });
        }
      })
    }
    checkSorting("ASC");
    checkSorting("DESC")
  });
  it("should show warning when no course is selected", () => {
    cy.get(".tutor-form-select-label").then(() => {
      cy.get("#tutor-admin-bulk-action-btn")
        .contains("Apply")
        .click();
    });
    cy.contains("Nothing was selected for bulk action.");
  });
  it("should be able to search any course", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "js";
    const courseLinkSelector = ".tutor-table-link";
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
  it("should be able to duplicate a course successfully", () => {
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-iconic-btn[action-tutor-dropdown]")
          .eq(0)
          .click();
        cy.get(".tutor-dropdown-item")
          .contains("Duplicate")
          .click();
        cy.get(".tutor-table-link")
          .eq(0)
          .invoke("text")
          .then((courseName) => {
            const duplicatedCourseName = `${courseName.trim()}`;
            expect(duplicatedCourseName.includes("(Copy")).to.be.true;
            cy.contains(duplicatedCourseName).should("exist");
          });
      }
    });
  });
  it("should be able to delete a course successfully", () => {
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-iconic-btn[action-tutor-dropdown]")
          .eq(0)
          .click();
        cy.get(".tutor-dropdown-item")
          .contains("Delete Permanently")
          .click();
        cy.get(
          "#tutor-common-confirmation-form > .tutor-d-flex > .tutor-btn-primary"
        )
          .contains("Yes, I'am Sure")
          .click();
        cy.get(".tutor-table-link")
          .eq(0)
          .invoke("text")
          .then((courseName) => {
            cy.contains(courseName).should("not.exist");
          });
      }
    });
  });
  it("should perform bulk actions on selected course", () => {
    const options = ["publish", "pending", "draft", "trash"];
    options.forEach((option) => {
      cy.performBulkActionOnSelectedElement(option);
    });
  });
  it("should be able to perform bulk actions on all courses", () => {
    const options = ["publish", "pending", "draft", "trash"];
    options.forEach((option) => {
      cy.performBulkAction(option);
    });
  });

});
