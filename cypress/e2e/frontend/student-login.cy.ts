describe("Tutor Student login", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/dashboard/`)
    });

    it("should log in with valid credentials", () => {
        cy.getByInputName("log").type("john@gmail.com")
        cy.getByInputName("pwd").type("test123")
        cy.getByInputName("rememberme").click()
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.url().should('include', '/dashboard');
    })

    it('should display an error for invalid username', () => {
        cy.getByInputName("log").type('invalidUsername');
        cy.getByInputName("pwd").type('validPassword');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.get('.tutor-alert').should('contain', 'is not registered on this site.');
    });

    it('should display an error for invalid password', () => {
        cy.getByInputName("log").type('john');
        cy.getByInputName("pwd").type('invalidPassword');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.get('.tutor-alert').should('contain', 'is incorrect.');
    });

    // it('should display an error for empty username', () => {
    //     cy.getByInputName("pwd").type('test123');
    //     cy.get("#tutor-login-form button").contains("Sign In").click()
    //     cy.get('.tutor-alert').should('contain', 'Username is required');
    // });
    
    // it('should display an error for empty password', () => {
    //     cy.getByInputName("log").type('validUsername');
    //     cy.get("#tutor-login-form button").contains("Sign In").click()
    //     cy.get('.tutor-alert').should('contain', 'Password is required');
    // });
    
    // it('should display errors for empty username and password', () => {
    //     cy.get("#tutor-login-form button").contains("Sign In").click()
    //     cy.get('.tutor-alert').should('contain', 'Username is required');
    //     cy.get('.tutor-alert').should('contain', 'Password is required');
    // });
    
    it('should trim leading/trailing spaces in username', () => {
        cy.getByInputName("log").type('   john   ');
        cy.getByInputName("pwd").type('test123');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.url().should('include', '/dashboard');
    });
    
    it('should trim leading/trailing spaces in password', () => {
        cy.getByInputName("log").type('john');
        cy.getByInputName("pwd").type('   test123   ');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.url().should('include', '/dashboard');
    });
    
    it('should be case-insensitive for username and password', () => {
        cy.getByInputName("log").type('JoHn');
        cy.getByInputName("pwd").type('Test123');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.url().should('include', '/dashboard');
    })
    
    // it('should allow password reset via "Forgot Password"', () => {});
    
    it('should prevent Cross-Site Scripting (XSS) attacks', () => {
        cy.getByInputName("log").type('<script>alert("XSS attack");</script>');
        cy.getByInputName("pwd").type('test123');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.get('.tutor-alert').should('contain', 'The username field is empty.');
    });
})