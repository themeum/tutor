import { backendUrls } from "../../config/page-urls"

describe("Tutor Admin Assignments", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.ASSIGNMENTS}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.ASSIGNMENTS)
    })

    // it("should evaluate an assignment", () => {

    //     cy.get("body").then(($body) => {
    //         if ($body.text().includes("No Data Available in this Section")) {
    //             cy.log("No data found")
    //         } else {
    //             cy.get(".tutor-table-assignments tbody tr").eq(0).then(($row) => {
    //                 if ($row.text().includes("Evaluate")) {
    //                     cy.wrap($row).find("a").contains("Evaluate").click()
    //                 } else {
    //                     cy.wrap($row).find("a").contains("Details").click()
    //                 }

    //                 cy.url().should("include", "view_assignment")
    //                 cy.url().then((url) => {
    //                     cy.intercept(
    //                         "POST",
    //                         `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    //                       ).as("ajaxRequest");
                        
    //                     cy.get("input[type=number]").clear().type("5")
    //                     cy.get("textarea").clear().type("The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking.")
    //                     cy.get("button").contains("Evaluate this submission").click()
                        
    //                     cy.wait("@ajaxRequest").then((interception) => {
    //                         expect(interception.response.body.success).to.equal(true);
    //                     });
                        
    //                     cy.get("a").contains("Back").click()
    //                 })
    //             })
    //         }
    //     })
    // })

    // it("should be able to search any assignement", () => {
    //     const searchInputSelector = "#tutor-backend-filter-search";
    //     const searchQuery = "assignment";
    //     const courseLinkSelector = "td>a";
    //     const submitButtonSelector=""
    //     const submitWithButton=false;
    
    //     cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
    //   });

      it("should check if the elements are sorted", () => {
        const formSelector = ":nth-child(3) > .tutor-form-control.tutor-form-select.tutor-js-form-select";
        const itemSelector =
          "tbody>tr>td>a";
        function checkSorting(order) {
          cy.get(formSelector).click();
          cy.get(`span[title=${order}]`).click();
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

       //   it("should filter meetings", () => {
    //     cy.get(":nth-child(2) > .tutor-js-form-select").click();
    //     cy.get("body").then(($body) => {
    //       if ($body.text().includes("No Records Found")) {
    //         cy.log("No data available");
    //       } else {
    //         cy.get(".tutor-form-select-options")
    //           .eq(1)
    //           .then(() => {
    //             cy.get(".tutor-form-select-option")
    //               .then(($options) => {
    //                 cy.get('[data-key="14144"]').click();
    //               })
    //               .then(() => {
    //                 cy.get("span.tutor-form-select-label[tutor-dropdown-label]")
    //                   .eq(0)
    //                   .invoke("text")
    //                   .then((retrievedText) => {
    //                     cy.get(
    //                       ".tutor-wp-dashboard-filter-item >.tutor-js-form-select >.tutor-form-select-dropdown >.tutor-form-select-options >.tutor-form-select-option >.tutor-nowrap-ellipsis"
    //                     ).each(($category) => {
    //                       cy.wrap($category)
    //                         .invoke("text")
    //                         .then((categoryText) => {
    //                           if (categoryText.trim() === retrievedText.trim()) {
    //                             cy.wrap($category).click({ force: true });
    //                           }
    //                         });
    //                     });
    //                   });
    //               });
    //           });
    //       }
    //     });
    //   });
    //   it("Should filter courses by a specific date", () => {
    //     cy.get(
    //       ".tutor-wp-dashboard-filter-items > :nth-child(3) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
    //     ).click();
    
    //     cy.get(".dropdown-years").click();
    //     cy.get(".dropdown-years>.dropdown-list")
    //       .contains("2025")
    //       .click();
    //     cy.get(".dropdown-months > .dropdown-label").click();
    //     cy.get(".dropdown-months > .dropdown-list")
    //       .contains("June")
    //       .click();
    //     cy.get(".react-datepicker__day--011")
    //       .contains("11")
    //       .click();
    
    //     cy.get("body").then(($body) => {
    //       if ($body.text().includes("No Data Found from your Search/Filter")) {
    //         cy.log("No data available");
    //       } else {
    //         cy.wait(2000);
    //         cy.get(".tutor-fs-7 > span").each(($el) => {
    //           const dateText = $el.text().trim();
    //           expect(dateText).to.contain("June 11, 2025");
    //         });
    //       }
    //     });
    //   });

})
