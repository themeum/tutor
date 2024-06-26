import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.REPORTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.REPORTS);
  });
  //   courses
  it("should be able to search any meeting", () => {
    cy.visit(
      `${Cypress.env("base_url")}/${backendUrls.REPORTS}&sub_page=courses`
    );
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "test";
    const courseLinkSelector = "tbody>tr>td>a";
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
  it("Should filter courses by a specific date", () => {
    cy.visit(
      `${Cypress.env("base_url")}/${backendUrls.REPORTS}&sub_page=courses`
    );
    cy.get(
      ".react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
    ).click();

    cy.get(".dropdown-years").click();
    cy.get(".dropdown-years>.dropdown-list")
      .contains("2024")
      .click();
    cy.get(".dropdown-months > .dropdown-label").click();
    cy.get(".dropdown-months > .dropdown-list")
      .contains("June")
      .click();
    cy.get(".react-datepicker__day--006")
      .contains("6")
      .click();
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
      }
    });
  });
  it("should check if the elements are sorted", () => {
    cy.visit(
      `${Cypress.env("base_url")}/${backendUrls.REPORTS}&sub_page=courses`
    );
    const formSelector =
      ":nth-child(3) > .tutor-form-control.tutor-form-select.tutor-js-form-select";
    const itemSelector = "tbody>tr>td>a";
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
  // reviews
  it("should change status of a review", () => {
    cy.visit(
      `${Cypress.env("base_url")}/${backendUrls.REPORTS}&sub_page=reviews`
    );
    cy.get("select[title$='Update review status']")
      .eq(0)
      .as("statusDropdown");
    cy.get("@statusDropdown").then(($dropdown) => {
      const selectedValue = $dropdown.val();

      if (selectedValue === "approved") {
        cy.get("@statusDropdown").select("Unpublished");
        cy.get("@statusDropdown")
          .should("have.value", "hold")
          .find("option:selected")
          .should("have.attr", "data-status_class", "select-warning");
      } else if (selectedValue === "hold") {
        cy.get("@statusDropdown").select("Published");
        cy.get("@statusDropdown")
          .should("have.value", "approved")
          .find("option:selected")
          .should("have.attr", "data-status_class", "select-success");
      } else {
        throw new Error("Unexpected dropdown value");
      }
    });
  });
  it("should delete a review", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_delete_review")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");
    cy.visit(
      `${Cypress.env("base_url")}/${backendUrls.REPORTS}&sub_page=reviews`
    );
    cy.get("button[action-tutor-dropdown$='toggle']")
      .eq(0)
      .click();
    cy.get("a.tutor-dropdown-item")
      .contains("Delete")
      .click();
    cy.get("button")
      .contains("Yes, I'am Sure")
      .click();
    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.request.body).to.include("tutor_delete_review");
      expect(interception.response.body.success).to.equal(true);
    });
  });
  //   overview
  it("should visit all tabs of graph", () => {
    cy.get(".tutor-analytics-graph .tutor-nav-link").as("navLinks");
    cy.get("@navLinks").each(($el, index) => {
      cy.wrap($el).click();
    });
  });
});
