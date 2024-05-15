declare namespace Cypress {
  interface Chainable {
    getByInputName(dataTestAttribute: string): Chainable<JQuery<HTMLElement>>
    setTinyMceContent(selector: string, content: string): Chainable<JQuery<HTMLElement>>
    loginAsAdmin(): Chainable<JQuery<HTMLElement>>
    loginAsInstructor(): Chainable<JQuery<HTMLElement>>
    loginAsStudent(): Chainable<JQuery<HTMLElement>>
    performBulkAction(option: string): Chainable<JQuery<HTMLElement>>
    performBulkActionOnSelectedCourses(option: string): Chainable<JQuery<HTMLElement>>
    search(searchInputSelector: string, searchQuery: string, courseLinkSelector: string,submitButtonSelector:string, submitWithButton: boolean): Chainable<JQuery<HTMLElement>>
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
  cy.wait(500)
  cy.getByInputName("log").type(Cypress.env("admin_username"))
  cy.getByInputName("pwd").type(Cypress.env("admin_password"))
  cy.get("form#loginform").submit()
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

// perform publish,pending,draft,trash when all courses are selected at once

Cypress.Commands.add("performBulkAction", (option) => {
  cy.get("#tutor-bulk-checkbox-all").click();
  cy.get(".tutor-mr-12 > .tutor-js-form-select").click();

  cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
    .invoke("text")
    .then((text) => {
      const expectedValue = text.trim();
      console.log(`${option} Option:`, expectedValue);

      cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
        .click();

      cy.get("#tutor-admin-bulk-action-btn")
        .contains("Apply")
        .click();
      cy.get("#tutor-confirm-bulk-action")
        .contains("Yes, I'am Sure")
        .click();

        if (option === 'trash') {
          cy.contains("No Data Available in this Section")
        } else {
          cy.get("select.tutor-table-row-status-update")
            .invoke("val")
            .then((selectedValue) => {
              console.log("Selected val", selectedValue);
              expect(selectedValue).to.equal(expectedValue.toLowerCase());
            });
        }
    });
});

Cypress.Commands.add("search", (searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton = false) => {
  cy.get(searchInputSelector).type(`${searchQuery}{enter}`);
  if (submitWithButton) {
    cy.get(searchInputSelector).clear()
    cy.get(submitButtonSelector).click();
  } 
  let count = 0;
  cy.get(courseLinkSelector)
    .eq(0)
    .each(($link) => {
      const courseName = $link.text().trim();

      if (courseName.includes(searchQuery)) {
        count++;

        expect(courseName.toLowerCase()).to.include(
          searchQuery.toLowerCase()
        );

        cy.get(courseLinkSelector)
          .eq(0)
          .its("length")
          .then((totalVisibleElements) => {
            expect(count).to.eq(totalVisibleElements);
          });
      }
    });
});
