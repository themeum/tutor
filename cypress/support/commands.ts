import { type Addon } from '@TutorShared/utils/util';

/* eslint-disable @typescript-eslint/no-namespace */
export {};
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
Cypress.Commands.add('login', () => {
  cy.visit('/wp-login.php');

  cy.on('uncaught:exception', () => false);

  cy.get('#user_login').type(Cypress.env('username'));
  cy.get('#user_pass').type(Cypress.env('password'));

  cy.get('#user_login').should('have.value', Cypress.env('username'));
  cy.get('#user_pass').should('have.value', Cypress.env('password'));

  cy.get('#wp-submit').click();

  cy.url().should('include', '/wp-admin');
});

Cypress.Commands.add('setWPeditorContent', (content: string) => {
  cy.window({
    timeout: 1000,
  }).then((win) => {
    const editorId = win.tinymce.activeEditor.id;
    cy.get(`#${editorId}_ifr`).then(($iframe) => {
      const doc = $iframe.contents();
      const body = doc.find('body > p');
      cy.wrap(body).type(content);
    });
  });
});

Cypress.Commands.add('getSelectInput', (name: string, value: string) => {
  cy.get(`input[name="${name}"]`).should('be.visible').click({ timeout: 150 });
  cy.get('.tutor-portal-popover')
    .should('be.visible')
    .within(() => {
      cy.get('li').contains(value).click();
    });
});

Cypress.Commands.add('isAddonEnabled', (addon: Addon) => {
  return cy.window().then((win) => {
    const isEnabled = !!win._tutorobject.addons_data.find(
      (item: { base_name: string; is_enabled: boolean }) => item.base_name === addon,
    )?.is_enabled;
    return cy.wrap(isEnabled);
  });
});

declare global {
  namespace Cypress {
    interface Chainable {
      login(): Chainable<void>;
      setWPeditorContent(content: string): Chainable<void>;
      getSelectInput(name: string, value: string): Chainable<void>;
      isAddonEnabled(addon: Addon): Chainable<boolean>;
    }
  }
}
