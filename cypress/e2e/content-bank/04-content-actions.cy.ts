import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Content Bank - Actions', () => {
  let collectionId: string;
  let duplicatedCollectionId: string;

  before(() => {
    cy.fixture('collection.json').then((data) => {
      collectionId = data.collectionId;
    });
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_CONTENT_BANK_COLLECTIONS)) {
        req.alias = 'getContentBankCollections';
      }

      if (req.body.includes(endpoints.GET_CONTENT_BANK_CONTENTS)) {
        req.alias = 'getContentBankContents';
      }

      if (req.body.includes(endpoints.SAVE_CONTENT_BANK_COLLECTION)) {
        req.alias = 'saveCollection';
      }

      if (req.body.includes(endpoints.DUPLICATE_CONTENT_BANK_CONTENT)) {
        req.alias = 'duplicateContent';
      }

      if (req.body.includes(endpoints.DELETE_CONTENT_BANK_CONTENTS)) {
        req.alias = 'deleteContent';
      }

      if (req.body.includes(endpoints.DUPLICATE_CONTENT_BANK_COLLECTION)) {
        req.alias = 'duplicateCollection';
      }

      if (req.body.includes(endpoints.DELETE_CONTENT_BANK_COLLECTION)) {
        req.alias = 'deleteCollection';
      }

      if (req.body.includes(endpoints.GET_EXPORTABLE_CONTENT)) {
        req.alias = 'getExportableContent';
      }

      if (req.body.includes(endpoints.IMPORT_FROM_COURSES)) {
        req.alias = 'importFromCourses';
      }

      if (req.body.includes(endpoints.MOVE_CONTENT_BANK_CONTENT)) {
        req.alias = 'moveContent';
      }
    });

    loginAsAdmin();

    cy.visit(`${backendUrls.CONTENT_BANK}&collection_id=${collectionId}`);
    cy.waitAfterRequest('getContentBankContents');
  });

  it('import content from courses', () => {
    cy.get('[data-cy=content-create-popover]').click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=import-content-modal]').click();
    });
    cy.waitAfterRequest('getExportableContent');

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('[data-cy=select-courses]').click();
    });

    cy.get('[data-cy=tutor-modal]')
      .last()
      .within(() => {
        cy.fixture('course.json').then((courses) => {
          cy.getByInputName('search').type(courses.post_title);
        });

        cy.get('table tbody tr')
          .first()
          .within(() => {
            cy.get('input[type="checkbox"]').check({ force: true });
          });

        cy.get('[data-cy=add-courses]').click();
        cy.waitAfterRequest('getExportableContent');
      });

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.getByInputName('courses__tutor_quiz').check();
      cy.getByInputName('courses__tutor_assignments').check();
      cy.getByInputName('courses__lesson').check();

      cy.get('[data-cy=import-from-courses]').click();
      cy.waitAfterRequest('importFromCourses');
    });
  });

  it('duplicate content in content bank', () => {
    cy.get('[data-cy=content-actions-popover]').first().click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=duplicate-content]').click();
    });
    cy.waitAfterRequest('duplicateContent');
  });

  it('delete content in content bank', () => {
    cy.get('[data-cy=content-actions-popover]').first().click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=delete-content]').click();
    });

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('button').contains('Delete').click();
    });

    cy.waitAfterRequest('deleteContent');
  });

  it('duplicate collection in content bank', () => {
    cy.get('[data-cy=selected-collection]').within(() => {
      cy.get('[data-cy=collection-actions-popover]').click({ force: true });
    });

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=duplicate-collection]').click();
    });
    cy.waitAfterRequest('duplicateCollection');
    cy.waitAfterRequest('getContentBankCollections');

    cy.url().then((url) => {
      const urlParams = new URLSearchParams(url);
      duplicatedCollectionId = urlParams.get('collection_id') || '';
      cy.log(`Duplicated Collection ID: ${duplicatedCollectionId}`);
    });
  });

  it('move content to another collection', () => {
    cy.get('[data-cy=content-actions-popover]').first().click();

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=move-content]').click();
    });

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.fixture('collection.json').then((data) => {
        cy.get('input')
          .first()
          .type(data.name + ' (copy)');
      });
      cy.waitAfterRequest('getContentBankCollections');
      cy.get('button[data-move-here-button=true]').first().click({ force: true });
    });

    cy.waitAfterRequest('moveContent');
  });

  it('rename collection in content bank', () => {
    cy.get('[data-cy=selected-collection]').within(() => {
      cy.get('[data-cy=collection-actions-popover]').click({ force: true });
    });

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=rename-collection]').click();
    });

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('input[name=name]').clear().type('Renamed Collection');
      cy.get('button[data-cy=save-collection-button]').click();
    });

    cy.waitAfterRequest('saveCollection');
  });

  it('delete collection in content bank', () => {
    cy.get('[data-cy=selected-collection]').within(() => {
      cy.get('[data-cy=collection-actions-popover]').click({ force: true });
    });

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=delete-collection]').click();
    });

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('button').contains('Delete').click();
    });

    cy.waitAfterRequest('deleteCollection');

    cy.visit(`${backendUrls.CONTENT_BANK}&collection_id=${duplicatedCollectionId}`);

    cy.waitAfterRequest('getContentBankCollections');
    cy.waitAfterRequest('getContentBankContents');

    cy.get('[data-cy=selected-collection]').within(() => {
      cy.get('[data-cy=collection-actions-popover]').click({ force: true });
    });

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=delete-collection]').click();
    });

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('button').contains('Delete').click();
    });

    cy.waitAfterRequest('deleteCollection');
  });
});

describe('Course Management', () => {
  it('delete created course', () => {
    cy.fixture('course.json').then((course) => {
      cy.deleteCourseById(course.courseId);
    });
  });
});
