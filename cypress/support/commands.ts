declare namespace Cypress {
  interface Chainable {
    getByInputName(dataTestAttribute: string): Chainable<JQuery<HTMLElement>>
    setTinyMceContent(selector: string, content: string): Chainable<JQuery<HTMLElement>>
    loginAsAdmin(): Chainable<JQuery<HTMLElement>>
    loginAsInstructor(): Chainable<JQuery<HTMLElement>>
    loginAsStudent(): Chainable<JQuery<HTMLElement>>
  }
}

Cypress.Commands.add("getByInputName", (selector) => {
  return cy.get(`input[name="${selector}"]`)
})

Cypress.Commands.add("setTinyMceContent", (selector, content) => {
  // wait for tinymce to be loaded
  cy.window().should('have.property', 'tinymce');

  // wait for the editor to be rendered
  cy.get(selector).find('textarea').as('editorTextarea').should('exist');
  
  // set the content for the editor by its dynamic id
  cy.window().then((win) =>
    cy.get('@editorTextarea').then((element) => {
      const editorId = element.attr('id');
      const editorInstance = (win as any).tinymce.EditorManager.get().filter((editor) => editor.id === editorId)[0];
      editorInstance.setContent(content);
    })
  )
})

Cypress.Commands.add("loginAsAdmin", () => {
  cy.getByInputName("log").type(Cypress.env("admin_username"))
  cy.getByInputName("pwd").type(Cypress.env("admin_password"))
  cy.get("#tutor-login-form button").contains("Sign In").click()
})

Cypress.Commands.add("loginAsInstructor", () => {
  cy.getByInputName("log").type(Cypress.env("instructor_username"))
  cy.getByInputName("pwd").type(Cypress.env("instructor_password"))
  cy.get("#tutor-login-form button").contains("Sign In").click()
})

Cypress.Commands.add("loginAsStudent", () => {
  cy.getByInputName("log").type(Cypress.env("student_username"))
  cy.getByInputName("pwd").type(Cypress.env("student_password"))
  cy.get("#tutor-login-form button").contains("Sign In").click()
})
