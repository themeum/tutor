import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Admin or Instructor Announcements", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.DASHBOARD}`)
        cy.loginAsAdmin() // Or Instructor
        cy.url().should("include", frontendUrls.dashboard.DASHBOARD)
    })

    it ("should create a new announcement", () => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.ANNOUNCEMENTS}`)
        cy.get("button[data-tutor-modal-target=tutor_announcement_new]").click()

        cy.get("#tutor_announcement_new input[name=tutor_announcement_title]").type("Important Announcement - Upcoming Student Assembly")
        cy.get("#tutor_announcement_new textarea[name=tutor_announcement_summary]").type("I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly.")
        cy.get("#tutor_announcement_new button").contains("Publish").click()
    })

    it ("should view and delete an announcement", () => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.ANNOUNCEMENTS}`)

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get("button.tutor-announcement-details").eq(0).click()
                cy.get(".tutor-modal.tutor-is-active button.tutor-modal-btn-delete").click()
                cy.get(".tutor-modal.tutor-is-active button").contains("Yes, Delete This").click()
            }
        })
    })
})
