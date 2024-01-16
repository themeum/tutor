import { backendUrls } from "../../config/page-urls"

describe("Tutor Dashboard Quiz Attempts", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.QUIZ_ATTEMPTS}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.QUIZ_ATTEMPTS)
    })

    it ("should review a quiz successfully", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-quiz-attempts a").contains("Review").eq(0).click()
                cy.setTinyMceContent(".tutor-instructor-feedback-wrap", "Nice work! You got it right. If not, don't worry—just a small tweak needed. Keep it up!")
                cy.get(".quiz-attempt-answers-wrap button.tutor-instructor-feedback").click()

                cy.wait("@ajaxRequest").then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
                
                cy.get(".tutor-quiz-attempt-details-wrapper a").contains("Back").click()
            }
        })
    })

    it ("should mark a question as correct", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("review_quiz_answer")) {
                req.alias = "ajaxRequest"
            }
        })

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-quiz-attempts a").contains("Review").eq(0).click()
                
                cy.get(".tutor-quiz-attempt-details.tutor-table-data-td-target tbody tr").eq(0).find("td").last().find("a").eq(0).click()
                
                cy.wait("@ajaxRequest").then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
                
                cy.get(".tutor-quiz-attempt-details.tutor-table-data-td-target tbody tr").eq(0).find("td.result").should("contain.text", "Correct")
            }
        })
    })

    it ("should mark a question as incorrect", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("review_quiz_answer")) {
                req.alias = "ajaxRequest"
            }
        })

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-table-quiz-attempts a").contains("Review").eq(0).click()
                
                cy.get(".tutor-quiz-attempt-details.tutor-table-data-td-target tbody tr").eq(0).find("td").last().find("a").eq(1).click()
                
                cy.wait("@ajaxRequest").then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
                
                cy.get(".tutor-quiz-attempt-details.tutor-table-data-td-target tbody tr").eq(0).find("td.result").should("contain.text", "Incorrect")
            }
        })
    })
})
