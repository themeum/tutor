import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GRADEBOOK}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.GRADEBOOK);
  });
  // overview
  it("should filter grades by course", () => {
    cy.get(":nth-child(2) > .tutor-js-form-select").click();
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Records Found")) {
        cy.log("No data available");
      } else {
        cy.get(".tutor-form-select-options")
          .eq(1)
          .then(() => {
            cy.get(".tutor-form-select-option")
              .then(($options) => {
                cy.get(".tutor-form-select-options>div:nth-child(2)")
                  .eq(0)
                  .click();
              })
              .then(() => {
                cy.get("span.tutor-form-select-label[tutor-dropdown-label]")
                  .eq(0)
                  .invoke("text")
                  .then((retrievedText) => {
                    cy.get(
                      ".tutor-wp-dashboard-filter-item >.tutor-js-form-select >.tutor-form-select-dropdown >.tutor-form-select-options >.tutor-form-select-option >.tutor-nowrap-ellipsis"
                    ).each(($category) => {
                      cy.wrap($category)
                        .invoke("text")
                        .then((categoryText) => {
                          if (categoryText.trim() === retrievedText.trim()) {
                            cy.wrap($category).click({ force: true });
                          }
                        });
                    });
                  });
              });
          });
      }
    });
  });

  it("should be able to search any announcement", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "meet";
    const courseLinkSelector =":nth-child(2)>.tutor-d-flex.tutor-align-center.tutor-gap-2"
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

  it("should filter assignments", () => {
    const filterFormSelector = ":nth-child(2) > .tutor-js-form-select";
    const dropdownSelector = ".tutor-form-select-options";
    const dropdownOptionSelector = ".tutor-form-select-option";
    const dropdownTextSelector = "span[tutor-dropdown-item]";
    const elementTitleSelector =
      ":nth-child(2)>.tutor-d-flex.tutor-align-center.tutor-gap-2"
    cy.filterElements(
      filterFormSelector,
      dropdownSelector,
      dropdownOptionSelector,
      dropdownTextSelector,
      elementTitleSelector
    );
  });

  it("should check if the elements are sorted", () => {
    const formSelector =
      ":nth-child(3) > .tutor-form-control.tutor-form-select.tutor-js-form-select";
    const itemSelector = "tbody>tr>td>div";
    function checkSorting(order) {
      cy.get(formSelector).click();
      cy.get(`span[title=${order}]`).click();
      cy.get("body").then(($body) => {
        if ($body.text().includes("No Data Available in this Section")) {
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

  it("Should filter by a specific date", () => {
    const filterFormSelector =
      ".react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control";
    const elementDateSelector = "tbody>tr>td:first-child";
    cy.filterElementsByDate(filterFormSelector, elementDateSelector);
  });
  //   grade settings
  it("should add new grade",()=>{
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("add_new_gradebook")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`)
    cy.get("button").contains("Add New").click();
    cy.get("input[name=grade_name]").eq(0).type("T")
    cy.get("input[name=grade_point]").eq(0).type("5.00")
    cy.get("input[name=percent_to]").eq(0).type("100")
    cy.get("input[name=percent_from]").eq(0).type("95")
    cy.get(".button.wp-color-result").eq(0).click()
    cy.get(".wp-picker-input-wrap").eq(0).type("#1f86c1")
    cy.get("button").contains("Add new Grade").click();
    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.request.body).to.include("add_new_gradebook");
      expect(interception.response.body.success).to.equal(true);
    });
  })
  it("should edit a grade",()=>{
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("update_gradebook")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`)
    cy.get(".gradebook-edit-btn").contains("Edit").click();
    cy.get("input[name=grade_name]").eq(1).clear().type("E")
    cy.get("input[name=grade_point]").eq(1).clear().type("4.50")
    cy.get("input[name=percent_to]").eq(1).clear().type("99")
    cy.get("input[name=percent_from]").eq(1).clear().type("97")
    cy.get(".button.wp-color-result").eq(1).click()
    cy.get('#tutor-update-grade-color').clear().type("#1f8632")
    cy.get("button").contains("Update Grade").click();

    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.request.body).to.include("update_gradebook");
      expect(interception.response.body.success).to.equal(true);
    });
  })
   it("should delete a grade",()=>{
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`)
    cy.get(".gradebook-delete-btn").eq(0).contains("Delete").click();
    cy.get("button").contains("Yes, Delete This").click()
    cy.contains("The grade has been deleted successfully")
  })
});
