/// <reference types="cypress" />
// ***********************************************
// This example commands.ts shows you how to
// create various custom commands and overwrite
// existing commands.
//
// For more comprehensive examples of custom
// commands please read more here:
// https://on.cypress.io/custom-commands
// ***********************************************
//
//
// -- This is a parent command --
// Cypress.Commands.add('login', (email, password) => { ... })
//
//
// -- This is a child command --
// Cypress.Commands.add('drag', { prevSubject: 'element'}, (subject, options) => { ... })
//
//
// -- This is a dual command --
// Cypress.Commands.add('dismiss', { prevSubject: 'optional'}, (subject, options) => { ... })
//
//
// -- This will overwrite an existing command --
// Cypress.Commands.overwrite('visit', (originalFn, url, options) => { ... })
//
// declare global {
//   namespace Cypress {
//     interface Chainable {
//       login(email: string, password: string): Chainable<void>
//       drag(subject: string, options?: Partial<TypeOptions>): Chainable<Element>
//       dismiss(subject: string, options?: Partial<TypeOptions>): Chainable<Element>
//       visit(originalFn: CommandOriginalFn, url: string, options: Partial<VisitOptions>): Chainable<Element>
//     }
//   }
// }

declare namespace Cypress {
  interface Chainable {
    getByInputName(dataTestAttribute: string): Chainable<JQuery<HTMLElement>>
    setTinyMceContent(selector: string, content: string): Chainable<JQuery<HTMLElement>>
  }
}

Cypress.Commands.add("getByInputName", (selector) => {
  return cy.get(`input[name=${selector}]`)
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
