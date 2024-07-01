import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student Question and Answers", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.QUESTION_AND_ANSWER}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.QUESTION_AND_ANSWER)
    })

    it("should visit and reply a question", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get("tbody tr").eq(0).find("a").contains("Reply").click()
                cy.setTinyMceContent(".tutor-qna-reply-editor", "Hello there!")
                cy.get("button").contains("Reply").click()

                cy.wait('@ajaxRequest').then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });

                cy.get("a").contains("Back").click({force:true})
            }
        })
    })

    it("should mark a question as read or unread", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get("tbody tr").eq(0).find(".tutor-icon-kebab-menu").parent().click()
                cy.get(".tutor-dropdown-parent.is-open .tutor-dropdown-item").eq(0).click()
                
                cy.wait('@ajaxRequest').then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
            }
        })
    })

    it("should delete a question", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get("tbody tr").eq(0).find(".tutor-icon-kebab-menu").parent().click()
                cy.get("a").contains("Delete").click()
                cy.get(".tutor-modal.tutor-is-active").find("button").contains("Yes, Delete This").click()
                
                cy.wait('@ajaxRequest').then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
            }
        })
    })
})
