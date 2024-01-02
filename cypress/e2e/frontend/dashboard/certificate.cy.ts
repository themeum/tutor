import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Certificate", () => {
    const randomNumber = Math.floor(Math.random() * 11);
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.CERTIFICATE}`)
        cy.loginAsAdmin()
        // cy.loginAsInstructor // Or as an instructor
        cy.url().should("include", frontendUrls.dashboard.DASHBOARD)
    })

    it ("should create a new certificate", () => {
        cy.get("a").contains("Create certificate").click()
        cy.get(".single-template").eq(randomNumber).find("button").contains("Use this template").click({ force: true })
        cy.get(".tutor-certificate-name input").type("My New Template")
        cy.wait(5000)
        cy.get("button").contains("Publish").click()
        cy.wait(5000)
        cy.get(".tutor-certificate-builder-header-toolbar a").eq(2).click()
    })

    it ("should delete a certificate", () => {
        cy.get(".tutor-card-list-item").eq(0).invoke("attr", "id").then((id) => {
            const certificateId = id.split('-')[4]
            cy.get(`span[data-tutor-modal-target=tutor-modal-tutor-cb-row-del-${certificateId}`).click()
            cy.get(".tutor-modal.tutor-is-active").find("button").contains("Yes, Delete This").click()
            cy.wait(1000)
            cy.get(`#tutor-cb-row-id-${certificateId}`).should('not.exist');
        })
    })
})
