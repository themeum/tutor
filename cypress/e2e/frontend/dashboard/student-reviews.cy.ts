import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student Reviews", () => {
    const randomNumber = Math.floor(Math.random() * 5);
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.REVIEWS}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.REVIEWS)
    })

    it("should edit a review", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {

                cy.get(".tutor-nav").then(()=>{
                    cy.get(".tutor-nav-item").eq(1).click().then(()=>{
                        cy.get(".tutor-btn.tutor-btn-ghost").eq(0).click()
                    })
                })

                cy.get(".tutor-modal.tutor-is-active").find(".tutor-ratings-stars i").eq(randomNumber).click()
                cy.get(".tutor-modal.tutor-is-active").find("textarea[name=review]").clear().type("Just completed the course, and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze.")
                cy.get(".tutor-modal.tutor-is-active").find(".tutor_submit_review_btn").click()

                cy.wait('@ajaxRequest').then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
            }
        })
    })
    

    it("should delete a review", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                // cy.get(".tutor-dashboard-single-review").eq(0).find("span").contains("Delete").click()

                cy.get(".tutor-nav").then(()=>{
                    cy.get(".tutor-nav-item").eq(1).click().then(()=>{
                        cy.get(".tutor-btn.tutor-btn-ghost").eq(1).click()
                    })
                })
                cy.get(".tutor-modal.tutor-is-active").find("button").contains("Yes, Delete This").click()

                cy.wait('@ajaxRequest').then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
            }
        })
    })
})
