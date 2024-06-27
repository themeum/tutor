import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student Calendar", () => {
    beforeEach(() => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("get_calendar_materials")) {
                req.alias = "calendarAjaxRequest"
            }
        });
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.DASHBOARD}`)
        cy.loginAsStudent()
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.CALENDER}`)
        cy.url().should("include", frontendUrls.dashboard.CALENDER)
    })
    it ("should visit all the upcoming events", () => {
        cy.wait('@calendarAjaxRequest') 
        cy.get("body").then(($body) => {
            if ($body.text().includes("No data found in this section")) {
                cy.log("No data found")
            } else {
                cy.get(".meta-info>a").each(($item) => {
                    cy.wrap($item).invoke('attr', 'href').then((link) => {
                        cy.visit(link)
                        cy.url().should('eq', link)
                        cy.go('back')
                        cy.wait('@calendarAjaxRequest')
                    })
                })
            }
        })
    })
})
