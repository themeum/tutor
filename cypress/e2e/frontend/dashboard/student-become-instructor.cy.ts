import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student Become an Instructor", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.DASHBOARD}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.DASHBOARD)
    })

    it("should apply to become an instructor successfully", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("Become an instructor")) {
                cy.get("a[id=tutor-become-instructor-button]").click()
                cy.url().should("include", "instructor-registration")

                cy.get("button").contains("Apply Now").click()
                cy.get("body").should("contain", "Your application will be reviewed and the results will be sent to you by email.")

                cy.get("a").contains("Go to Dashboard").click()
                cy.get(".tutor-frontend-dashboard-header").should("contain", "Your Application is pending")
            }
        })
    })
})
