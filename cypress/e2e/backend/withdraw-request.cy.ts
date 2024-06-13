import { backendUrls } from "../../config/page-urls"

describe("Tutor Admin Withdraw Request", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.WITHDRAW_REQUESTS}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.WITHDRAW_REQUESTS)
    })

    it("should be able to search any assignement", () => {
        const searchInputSelector = "#tutor-backend-filter-search";
        const searchQuery = "withdraw request";
        const courseLinkSelector = "td>a";
        const submitButtonSelector=""
        const submitWithButton=false;
    
        cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
      });
})
