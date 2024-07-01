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
        cy.getByInputName("log").type('student1');
        cy.getByInputName("pwd").type('invalidPassword');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.get('.tutor-alert').should('contain', 'is incorrect.');
    });
    
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
    
    // it('should allow password reset via "Forgot Password"', () => {
    //     // Click on the "Forgot Password" link or button
    //     cy.contains('Forgot?').click();
    
    //     // Fill out the email or username field with a valid email address or username
    //     // cy.getByInputName("log").type('john');
    //     cy.get('#user_login').type("student1")
    
    //     // Submit the form to request a password reset
    //     cy.contains('Reset password').click();
    
    //     // Check if a success message or confirmation is displayed
    //     cy.get('.tutor-alert-text').should('be.visible');
    
    //     // (Optional) Verify if the password reset email is sent to the provided email address
    //     // cy.task('checkEmail').should('contain', 'Password reset instructions');
    
    //     // (Optional) Check if the user can successfully reset the password using the link sent to their email
    //     // cy.visit(passwordResetLink);
    //     // cy.getByTestId('new_password').type('newpassword123');
    //     // cy.getByTestId('confirm_new_password').type('newpassword123');
    //     // cy.contains('Reset Password').click();
    //     // cy.contains('Password successfully reset');
    // });
    
    it('should prevent Cross-Site Scripting (XSS) attacks', () => {
        cy.getByInputName("log").type('<script>alert("XSS attack");</script>');
        cy.getByInputName("pwd").type('test123');
        cy.get("#tutor-login-form button").contains("Sign In").click()
        cy.get('.tutor-alert').should('contain', 'The username field is empty.');
    });
})