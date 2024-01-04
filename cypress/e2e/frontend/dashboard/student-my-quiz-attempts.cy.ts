import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student My Quiz Attempts", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.MY_QUIZ_ATTEMPTS}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.MY_QUIZ_ATTEMPTS)
    })

    it("should visit quiz a attempts", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-quiz-attempts tbody tr").eq(0).then(($quiz) => {
                    if ($quiz.text().includes("Details")) {
                        cy.wrap($quiz).find("a").contains("Details").click()
                        cy.url().should("include", "view_quiz_attempt_id")
                        cy.get("a").contains("Back").click()
                    }
                })
            }
        })
    })
})
