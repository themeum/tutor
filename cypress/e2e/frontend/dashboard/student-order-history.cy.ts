import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Student Order History", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.ORDER_HISTORY}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.ORDER_HISTORY)
    })
    it("should filter from date range",()=>{
        
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
              cy.log("No data found");
            } else{
                cy.get("input[placeholder=' MMMM d, yyyy -- MMMM d, yyyy ']").click()
                cy.get(".react-datepicker__day.react-datepicker__day--001").eq(0).click()
                cy.get(".react-datepicker__day.react-datepicker__day--030").eq(1).click()
                cy.get("button").contains("Apply").click()

                // write assertion
            }})
    })
    // it("should filter based on today, monthly and yearly",()=>{

    // })
})
