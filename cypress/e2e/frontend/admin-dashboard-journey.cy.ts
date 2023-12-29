import { frontendUrls } from "../../config/page-urls"

describe("Tutor Admin Dashboard Journey", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}${frontendUrls.dashboard.DASHBOARD}`)
    })

    it ("should be able visit all the admin dashboard pages", () => {
        // Login as an admin
        cy.getByInputName("log").type(Cypress.env("admin_username"))
        cy.getByInputName("pwd").type(Cypress.env("admin_password"))
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.url().should("include", frontendUrls.dashboard.DASHBOARD)

        // Visit dashboard pages 
        cy.get("a.tutor-dashboard-menu-item-link:not(.is-active)").each((item) => {
            cy.wrap(item).invoke('attr', 'href').then((link) => {
                cy.visit(link)
                if (!link.endsWith('logout')) {
                    cy.url().should('eq', `${link}${link.endsWith('/') ? '' : '/'}`)
                }
                cy.wait(500)
                cy.window().scrollTo('bottom', { duration: 500, easing: 'linear' })

                // Visit nested pages if available 
                cy.get('body').then(($body) => {
                    if ($body.find(".tutor-nav").length) {
                        cy.get("a.tutor-nav-link:not(.is-active):not(.tutor-nav-more-item)").each((item) => {
                            cy.wrap(item).invoke('attr', 'href').then((link) => {
                                cy.visit(link)
                                cy.url().should('eq', `${link}${link.endsWith('/') ? '' : '/'}`)
                                cy.wait(500)
                                cy.window().scrollTo('bottom', { duration: 500, easing: 'linear' })
                            })
                        })
                    }
                })
            })
        })
    })
})
