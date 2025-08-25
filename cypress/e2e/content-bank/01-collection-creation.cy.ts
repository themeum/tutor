import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Content Bank - Create Collection', () => {
  let collectionId: string;
  const collectionName = faker.lorem.sentence(3);

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.SAVE_CONTENT_BANK_COLLECTION)) {
        req.alias = 'createCollection';
      }
      if (req.body.includes(endpoints.GET_CONTENT_BANK_COLLECTIONS)) {
        req.alias = 'getCollections';
      }
    });

    loginAsAdmin();

    cy.visit(backendUrls.CONTENT_BANK);
  });

  it('should create a new collection', () => {
    cy.get('[data-cy=open-new-collection-modal]').click();

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]')
      .should('be.visible')
      .within(() => {
        cy.get('input[name=name]').type(collectionName);
        cy.get('button[data-cy=save-collection-button]').click();
      });

    cy.waitAfterRequest('createCollection');
    cy.waitAfterRequest('getCollections');

    cy.url().then((url) => {
      const urlParams = new URLSearchParams(url);
      collectionId = urlParams.get('collection_id') || '';
      cy.log(`Collection ID: ${collectionId}`);
      cy.wrap(collectionId).should('be.a', 'string').and('not.be.empty');

      cy.writeFile('cypress/fixtures/collection.json', { collectionId, name: collectionName });
    });
  });
});
