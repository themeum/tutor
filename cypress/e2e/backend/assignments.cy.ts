import { backendUrls } from "../../config/page-urls"

describe("Tutor Admin Assignments", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.ASSIGNMENTS}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.ASSIGNMENTS)
    })

    it("should evaluate an assignment", () => {

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-assignments tbody tr").eq(0).then(($row) => {
                    if ($row.text().includes("Evaluate")) {
                        cy.wrap($row).find("a").contains("Evaluate").click()
                    } else {
                        cy.wrap($row).find("a").contains("Details").click()
                    }

                    cy.url().should("include", "view_assignment")
                    cy.url().then((url) => {
                        cy.intercept(
                            "POST",
                            `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
                          ).as("ajaxRequest");
                        
                        cy.get("input[type=number]").clear().type("5")
                        cy.get("textarea").clear().type("The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking.")
                        cy.get("button").contains("Evaluate this submission").click()
                        
                        cy.wait("@ajaxRequest").then((interception) => {
                            expect(interception.response.body.success).to.equal(true);
                        });
                        
                        cy.get("a").contains("Back").click()
                    })
                })
            }
        })
    })

    it("should be able to search any assignement", () => {
        const searchInputSelector = "#tutor-backend-filter-search";
        const searchQuery = "assignment";
        const courseLinkSelector = "td>a";
        const submitButtonSelector=""
        const submitWithButton=false;
    
        cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
      });
})
