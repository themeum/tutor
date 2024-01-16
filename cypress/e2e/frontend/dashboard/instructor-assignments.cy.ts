import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Assignments", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.ASSIGNMENTS}`)
        cy.loginAsInstructor()
        cy.url().should("include", frontendUrls.dashboard.ASSIGNMENTS)
    })

    it("should evaluate all the assignments", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".table-assignment tbody tr").each(($row, $index) => {
                    const totalSubmits = Number($row.find("td").eq(2).text())
                    if (totalSubmits > 0) { // Only evaluate assignment which has any submission
                        cy.get(".table-assignment tbody tr a.tutor-btn-outline-primary").eq($index).invoke('attr', 'href').then((link) => {
                            cy.visit(link)

                            cy.url().should("include", "assignments/submitted")
                            cy.get("body").then(($body) => {
                                if ($body.text().includes("Evaluate")) {
                                    cy.get("table td a").contains("Evaluate").click()
                                    cy.url().should("include", "view_assignment")

                                    cy.url().then((url) => {
                                        cy.intercept("POST", url).as("ajaxRequest")

                                        cy.get("input[type=number]").type("5")
                                        cy.get("textarea").type("The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking.")
                                        cy.get("button").contains("Evaluate this submission").click()
                                        
                                        cy.wait("@ajaxRequest").then((interception) => {
                                            expect(interception.response.body.success).to.equal(true);
                                        });
                                        
                                        cy.get("a").contains("Back").click()
                                        cy.get("a").contains("Back").click()
                                    })
                                } else {
                                    cy.get("a").contains("Back").click()
                                }
                            })
                        })
                    }
                })
            }
        })
    })
})
