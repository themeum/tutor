import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Content Bank - Create Contents', () => {
  let collectionId: string;
  const questionTitle = faker.lorem.sentence();
  const lessonTitle = faker.lorem.sentence();
  const assignmentTitle = faker.lorem.sentence();

  before(() => {
    cy.fixture('collection.json').then((data) => {
      collectionId = data.collectionId;
    });
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_CONTENT_BANK_COLLECTIONS)) {
        req.alias = 'getCollections';
      }

      if (req.body.includes(endpoints.GET_CONTENT_BANK_CONTENTS)) {
        req.alias = 'getContents';
      }

      if (req.body.includes(endpoints.SAVE_QUESTION_CONTENT)) {
        req.alias = 'saveQuestion';
      }

      if (req.body.includes(endpoints.SAVE_CONTENT_BANK_LESSON_CONTENT)) {
        req.alias = 'saveLesson';
      }

      if (req.body.includes(endpoints.SAVE_CONTENT_BANK_ASSIGNMENT_CONTENT)) {
        req.alias = 'saveAssignment';
      }
    });

    loginAsAdmin();

    cy.visit(`${backendUrls.CONTENT_BANK}&collection_id=${collectionId}`);
  });

  it('should create a new question', () => {
    cy.waitAfterRequest('getCollections');
    cy.waitAfterRequest('getContents');

    cy.get('[data-cy=question-content-modal]').click();

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]')
      .should('be.visible')
      .within(() => {
        cy.get('textarea[name=question_title]').type(questionTitle);
        cy.get('button[data-cy=save-question]').click();
      });

    cy.waitAfterRequest('saveQuestion');
    cy.waitAfterRequest('getContents');

    cy.get('table').within(() => {
      cy.get('tbody tr').should('have.length.greaterThan', 0);
      cy.get('tbody tr')
        .first()
        .within(() => {
          cy.get('td').eq(0).should('contain.text', questionTitle);
        });
    });
  });

  it('should create a new lesson', () => {
    cy.waitAfterRequest('getCollections');
    cy.waitAfterRequest('getContents');

    cy.get('[data-cy=content-create-popover]').click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=lesson-content-modal]').click();
    });

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]')
      .should('be.visible')
      .within(() => {
        cy.getByInputName('title').type(lessonTitle);
        cy.get('[data-cy=save-lesson]').click();
      });

    cy.waitAfterRequest('saveLesson');
    cy.waitAfterRequest('getContents');

    cy.get('table').within(() => {
      cy.get('tbody tr').should('have.length.greaterThan', 0);
      cy.get('tbody tr')
        .first()
        .within(() => {
          cy.get('td').eq(0).should('contain.text', lessonTitle);
        });
    });
  });

  it('should create a new assignment', () => {
    cy.waitAfterRequest('getCollections');
    cy.waitAfterRequest('getContents');

    cy.get('[data-cy=content-create-popover]').click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=assignment-content-modal]').click();
    });

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]')
      .should('be.visible')
      .within(() => {
        cy.getByInputName('title').type(assignmentTitle);
        cy.get('[data-cy=save-assignment]').click();
      });

    cy.waitAfterRequest('saveAssignment');
    cy.waitAfterRequest('getContents');

    cy.get('table').within(() => {
      cy.get('tbody tr').should('have.length.greaterThan', 0);
      cy.get('tbody tr')
        .first()
        .within(() => {
          cy.get('td').eq(0).should('contain.text', assignmentTitle);
        });
    });
  });
});
