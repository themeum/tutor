import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Withdraw Request", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.WITHDRAW_REQUESTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.WITHDRAW_REQUESTS);
  });

  // it("should be able to search any assignement", () => {
  //     const searchInputSelector = "#tutor-backend-filter-search";
  //     const searchQuery = "tutor";
  //     const courseLinkSelector = ".tutor-color-black.tutor-fs-6.tutor-fw-medium";
  //     const submitButtonSelector=""
  //     const submitWithButton=false;

  //     cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
  //   });

  //   it("Should filter announcements by a specific date", () => {
  //     const filterFormSelector =
  //       ".react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control";
  //     const elementDateSelector = "tbody>tr>td>:nth-child(1).tutor-fs-7";
  //     cy.filterElementsByDate(filterFormSelector, elementDateSelector);
  //   });
  // it("should check if the elements are sorted", () => {
  //     const formSelector = ".tutor-wp-dashboard-filter-items > :nth-child(2) > .tutor-js-form-select";
  //     const itemSelector =
  //      ".tutor-color-black.tutor-fs-6.tutor-fw-medium";
  //     function checkSorting(order) {
  //       cy.get(formSelector).click();
  //       cy.get(`span[title=${order}]`).click();
  //       cy.get("body").then(($body) => {
  //         if (
  //           $body.text().includes("No Data Available in this Section")
  //         ) {
  //           cy.log("No data available");
  //         }else{
  //           cy.get(itemSelector).then(($items) => {
  //             const itemTexts = $items.map((index, item) => item.innerText.trim()).get().filter(text => text);
  //             const sortedItems = order === 'ASC' ? itemTexts.sort() : itemTexts.sort().reverse();
  //             expect(itemTexts).to.deep.equal(sortedItems);
  //           });
  //         }
  //       })
  //     }
  //     checkSorting("ASC");
  //     checkSorting("DESC")
  //   });
  //   it("should approve a withdraw request", () => {
  //     cy.intercept(
  //       "POST",
  //       `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
  //       (req) => {
  //         if (req.body.includes("tutor_admin_withdraw_action")) {
  //           req.alias = "ajaxRequest";
  //         }
  //       }
  //     ).as("ajaxRequest");

  //     cy.get("tbody tr")
  //       .first()
  //       .then(($row) => {
  //         cy.wrap($row)
  //           .find('td[data-th="Status"]')
  //           .then(($status) => {
  //             const statusText = $status.text().trim();

  //             if (statusText === "Pending") {
  //               cy.wrap($row)
  //                 .find('button:contains("Approve")')
  //                 .should("be.visible")
  //                 .click();
  //               cy.get("button")
  //                 .contains("Yes, Approve Withdrawal")
  //                 .click();
  //               cy.wait("@ajaxRequest").then((interception) => {
  //                 expect(interception.request.body).to.include(
  //                   "tutor_admin_withdraw_action"
  //                 );

  //                 expect(interception.response.statusCode).to.equal(200);
  //               });
  //             } else {
  //               cy.log("Already approved or rejected");
  //             }
  //           });
  //       });
  //   });

  it("should reject a withdraw request", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_admin_withdraw_action")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");

    cy.get("tbody tr")
      .first()
      .then(($row) => {
        cy.wrap($row)
          .find('td[data-th="Status"]')
          .then(($status) => {
            const statusText = $status.text().trim();

            if (statusText === "Pending") {
              cy.wrap($row)
                .find('button:contains("Reject")')
                .should("be.visible")
                .click();
              cy.get(".tutor-modal-body > .tutor-js-form-select").click();
              cy.get("div.tutor-form-select-option")
                .contains("Invalid Request")
                .click();
              cy.get("button")
                .contains("Yes, Reject Withdrawal")
                .click();
              cy.wait("@ajaxRequest").then((interception) => {
                expect(interception.request.body).to.include(
                  "tutor_admin_withdraw_action"
                );
                expect(interception.response.statusCode).to.equal(200);
              });
            } else {
              cy.log("Already approved or rejected");
            }
          });
      });
  });
});
