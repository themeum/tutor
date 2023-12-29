describe("Tutor Student registration", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/student-registration`)
    });

    it("should register a new user with valid information", () => {
        cy.getByInputName("first_name").type("John")
        cy.getByInputName("last_name").type("Due")
        cy.getByInputName("user_login").type("john_due2")
        cy.getByInputName("email").type("john_due2@gmail.com")
        cy.getByInputName("password").type("test123")
        cy.getByInputName("password_confirmation").type("test123")

        cy.get("button[name=tutor_register_student_btn]").contains("Register").click()

        cy.url().then((url) => {
            if (url.includes('/student-registration')) {
                cy.get(".tutor-alert").should("contain", "Sorry, that username already exists!")
            } else {
                cy.location("pathname").should("eq", "/dashboard/")
            }
        })
    })

    it('should display errors for incomplete form submission', () => {
        cy.get("button[name=tutor_register_student_btn]").contains("Register").click()
        cy.location("pathname").should("eq", "/student-registration/")
    });

    it('should display an error for mismatched password confirmation', () => {
        cy.getByInputName("first_name").type("John")
        cy.getByInputName("last_name").type("Due")
        cy.getByInputName("user_login").type("john")
        cy.getByInputName("email").type("john@gmail.com")
        cy.getByInputName("password").type('test123');
        cy.getByInputName("password_confirmation").type('test456');
    
        cy.get("button[name=tutor_register_student_btn]").contains("Register").click()
    
        cy.get('.tutor-alert').should('contain', 'Your passwords should match each other.');
    });
})