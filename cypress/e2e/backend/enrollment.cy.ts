import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin ENROLLMENTS", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.ENROLLMENTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.ENROLLMENTS);
  });

  it("should enroll a student", () => {
    cy.get("button")
      .contains("Enroll a student")
      .click();
    cy.get(".tutor-mb-32 > .tutor-js-form-select").click();

    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-form-select-option > span").then(($options) => {
          const filteredOptions = $options.filter(
            (index, option) =>
              option.getAttribute("title") !== "Select a course/bundle"
          );
          const randomIndex = Math.floor(
            Math.random() * filteredOptions.length
          );
          const selectedOption = filteredOptions[randomIndex].getAttribute(
            "title"
          );

          cy.get(".tutor-mr-12 > .tutor-js-form-select").click({ force: true });

          cy.wrap(filteredOptions[randomIndex])
            .click({ force: true })
            .then(() => {
              cy.log(`Selected option: ${selectedOption}`);
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
          // search student
          cy.get("body").then(($body) => {         
              cy.get(
                ":nth-child(2) > .tutor-form-wrap > .tutor-form-control"
              ).type("tutor",{force:true});
              cy.wait(1000);
              if ($body.text().includes("No Student Found!")) {
                cy.log("No data available");
              }
              if ($body.text().includes("Selected Student")) {
                cy.get(".tutor-modal-footer > .tutor-btn-primary")
                  .click();
              } else {
                cy.get(".tutor-ml-auto > .tutor-iconic-btn").click();
                cy.get(".tutor-modal-footer > .tutor-btn-primary")
                  .click();
              }  
          });
        });
      }
    });
  });

    it("should be able to search any enrollment", () => {
      const searchInputSelector = "#tutor-backend-filter-search";
      const searchQuery = "Intro to JavaScript";
      const courseLinkSelector =
        "tr>td>div.tutor-d-flex.tutor-align-center.tutor-gap-2";
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

    it("should perform bulk action on all announcements", () => {
      cy.intercept(
        "POST",
        `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
        (req) => {
          if (req.body.includes("tutor_enrollment_bulk_action")) {
            req.alias = "performBulkActionAjax";
          }
        }
      );

      cy.get("body").then(($body) => {
        if ($body.text().includes("No Data Available in this Section")) {
          cy.log("No data found");
        } else {
          const bulkOption = (option) => {
            cy.get("#tutor-bulk-checkbox-all").click();
            cy.get(".tutor-mr-12 > .tutor-js-form-select").click();
            cy.get(`.tutor-form-select-option>span[title="${option}"]`)
              .contains(`${option}`)
              .click();
            cy.get("#tutor-admin-bulk-action-btn")
              .contains("Apply")
              .click();

            cy.get("#tutor-confirm-bulk-action")
              .contains("Yes, I'am Sure")
              .click();

            cy.wait("@performBulkActionAjax").then((interception) => {
              expect(interception.response.body.success).to.equal(true);
            });
          };
          bulkOption("Cancel");
          bulkOption("Approve");
        }
      });
    });

    it("should filter enrollments", () => {
      const selectedOptionText = "intro-to-js-paid";
      cy.get(":nth-child(2) > .tutor-js-form-select").click();
      cy.get(
        ":nth-child(2) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options"
      )
        .contains(selectedOptionText)
        .click({ force: true });
      cy.get("body").then(($body) => {
        if ($body.text().includes("No Data Found from your Search/Filter")) {
          cy.log("No data available");
        } else {
          cy.get(".tutor-d-flex.tutor-align-center.tutor-gap-2").each(
            ($announcement) => {
              cy.wrap($announcement).should("contain.text", selectedOptionText);
            }
          );
        }
      });
    });

    it("should check if the elements are sorted", () => {
      const formSelector = ":nth-child(3) > .tutor-js-form-select";
      const itemSelector = ".tutor-d-flex.tutor-align-center.tutor-gap-2";
      function checkSorting(order) {
        cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Found from your Search/Filter")) {
            cy.log("No data available");
          } else {
            cy.get(formSelector).click();
            cy.get(`span[title="${order}"]`).click();
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
    it("Should filter enrollments by a specific date", () => {
      cy.get("input[placeholder='MMMM d, yyyy']").click();

      cy.get(".dropdown-years").click();
      cy.get(".dropdown-years>.dropdown-list")
        .contains("2025")
        .click();
      cy.get(".dropdown-months > .dropdown-label").click();
      cy.get(".dropdown-months > .dropdown-list")
        .contains("June")
        .click();
      cy.get(".react-datepicker__day--011")
        .contains("11")
        .click();

      cy.get("body").then(($body) => {
        if ($body.text().includes("No Data Found from your Search/Filter")) {
          cy.log("No data available");
        } else {
          cy.wait(2000);
          cy.get(".tutor-fs-7 > span").each(($el) => {
            const dateText = $el.text().trim();
            expect(dateText).to.contain("June 11, 2025");
          });
        }
      });
    });
});
