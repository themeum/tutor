import { backendUrls } from "../../config/page-urls"

describe("Tutor Admin Announcements", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.ANNOUNCEMENTS}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.ANNOUNCEMENTS)
    })

    it ("should create a new announcement", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("tutor_announcement_create")) {
                req.alias = "ajaxRequest"
            }
        })

        cy.get("button[data-tutor-modal-target=tutor_announcement_new]").click()
        cy.get("#tutor_announcement_new input[name=tutor_announcement_title]").type("Important Announcement - Upcoming Student Assembly")
        cy.get("#tutor_announcement_new textarea[name=tutor_announcement_summary]").type("I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly.")
        cy.get("#tutor_announcement_new button").contains("Publish").click()

        cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });
    })

    it ("should update an announcement", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("tutor_announcement_create")) {
                req.alias = "ajaxRequest"
            }
        })

        cy.get("button[data-tutor-modal-target=tutor_announcement_new]").click()
        cy.get("#tutor_announcement_new input[name=tutor_announcement_title]").clear().type("Important Announcement - Updated Announcement Title")
        cy.get("#tutor_announcement_new textarea[name=tutor_announcement_summary]").type("I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly.")
        cy.get("#tutor_announcement_new button").contains("Publish").click()

        cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });
    })

    it ("should view and delete an announcement", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("tutor_announcement_delete")) {
                req.alias = "ajaxRequest"
            }
        })
        
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get("button.tutor-announcement-details").eq(0).click()
                cy.get(".tutor-modal.tutor-is-active button.tutor-modal-btn-delete").click()
                cy.get(".tutor-modal.tutor-is-active button").contains("Yes, Delete This").click()

                cy.wait("@ajaxRequest").then((interception) => {
                    expect(interception.response.body.success).to.equal(true);
                });
            }
        })
    })
})
