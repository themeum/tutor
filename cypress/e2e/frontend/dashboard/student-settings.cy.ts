import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard Settings", () => {
    
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}${frontendUrls.dashboard.SETTINGS}`)
        cy.loginAsStudent()
        cy.url().should("include", frontendUrls.dashboard.SETTINGS)
    })

    it ("should update profile", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.getByInputName("first_name").clear().type("Claire")
        cy.getByInputName("last_name").clear().type("Hewitt")
        cy.getByInputName("phone_number").clear().type("9823458204")
        cy.getByInputName("tutor_profile_job_title").clear().type("UX/UI Designer")
        cy.setTinyMceContent("#wp-tutor_profile_bio-editor-container", "Passionate UX designer with a flair for creating intuitive and visually appealing digital experiences. Adept at blending creativity with user-centric design principles to deliver impactful solutions.")
        cy.get("button").contains("Update Profile").click()
        
        cy.wait('@ajaxRequest').then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });
    })

    it ("should reset password", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("a.tutor-nav-link").contains("Password").click()

        cy.getByInputName("previous_password").type(Cypress.env("student_password"))
        cy.getByInputName("new_password").type(Cypress.env("student_password"))
        cy.getByInputName("confirm_new_password").type(Cypress.env("student_password"))
        cy.get("button").contains("Reset Password").click()

        cy.wait('@ajaxRequest').then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });

        cy.loginAsStudent()
    })


    it ("should select a withdraw method", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");
        
        cy.get("a.tutor-nav-link").contains("Withdraw").click()
        
        cy.getByInputName("tutor_selected_withdraw_method").click()
        cy.getByInputName("withdraw_method_field[bank_transfer_withdraw][account_name]").clear().type("John Doe")
        cy.getByInputName("withdraw_method_field[bank_transfer_withdraw][account_number]").clear().type("46728376439")
        cy.getByInputName("withdraw_method_field[bank_transfer_withdraw][bank_name]").clear().type("National Bank")
        cy.getByInputName("withdraw_method_field[bank_transfer_withdraw][iban]").clear().type("46728376439")
        cy.getByInputName("withdraw_method_field[bank_transfer_withdraw][swift]").clear().type("46728376439")

        cy.get("button").contains("Save Withdrawal Account").click()

        cy.wait('@ajaxRequest').then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });
    })
    
// fails api
    it ("should update social profiles", () => {

        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`).as("ajaxRequest");

        cy.get("a.tutor-nav-link").contains("Social Profile").click()

        cy.getByInputName("_tutor_profile_facebook").clear().type("https://facebook.com/username")
        cy.getByInputName("_tutor_profile_twitter").clear().type("https://twitter.com/username")
        cy.getByInputName("_tutor_profile_linkedin").clear().type("https://linkedin.com/username")
        cy.getByInputName("_tutor_profile_website").clear().type("https://example.com/")
        cy.getByInputName("_tutor_profile_github").clear().type("https://github.com/username")

        cy.get("button").contains("Update Profile").click()
        
        // testcase fails because api call does not happen
        cy.wait('@ajaxRequest').then((interception) => {
            expect(interception.response.body.success).to.equal(true);
        });

    })
})
