import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Quiz Attempts", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.QUIZ_ATTEMPTS}`)
        cy.loginAsAdmin() // Or Instructor
        cy.url().should("include", frontendUrls.dashboard.DASHBOARD)
    })

    it ("should review a quiz", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-quiz-attempts a").contains("Review").eq(0).click()
                cy.window().scrollTo('bottom', { duration: 500, easing: 'linear' })
                cy.setTinyMceContent(".tutor-instructor-feedback-wrap", "Nice work! You got it right. If not, don't worry—just a small tweak needed. Keep it up!")
                cy.get(".quiz-attempt-answers-wrap button.tutor-instructor-feedback").click()
                cy.get(".tutor-quiz-attempt-details-wrapper a").contains("Back").click()
            }
        })
    })
})
