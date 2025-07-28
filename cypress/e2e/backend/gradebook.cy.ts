import { backendUrls } from '../../config/page-urls';

describe('Tutor Dashboard My Courses', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.GRADEBOOK}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.GRADEBOOK);
  });

  it('should be able to search any grade', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'meet';
    const courseLinkSelector = ':nth-child(2)>.tutor-d-flex.tutor-align-center.tutor-gap-2';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should filter by course', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'course-id',
      resultColumnIndex: 2,
    });
  });

  it('should check if the elements are sorted', () => {
    const sortButton = '.tutor-wp-dashboard-filter-order';
    const itemSelector = 'tbody>tr>td>div';
    const checkSorting = (order: string) => {
      cy.get(sortButton).click();
      cy.get('body').then(($body) => {
        if ($body.text().includes('No Data Found.')) {
          cy.log('No data available');
        } else {
          cy.get(itemSelector).then(($items) => {
            const itemTexts = $items
              .map((index, item) => item.innerText.trim())
              .get()
              .filter((text) => text);
            const sortedItems = order === 'ASC' ? itemTexts.sort() : itemTexts.sort().reverse();
            expect(itemTexts).to.deep.equal(sortedItems);
          });
        }
      });
    };
    checkSorting('ASC');
    checkSorting('DESC');
  });

  it('should filter by a specific date', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'date',
      resultColumnIndex: 1,
    });
  });
  //   grade settings
  it('should add new grade', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('add_new_gradebook')) {
        req.alias = 'ajaxRequest';
      }
    }).as('ajaxRequest');
    cy.visit(`${Cypress.env('base_url')}${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`);
    cy.get('button').contains('Add New').click();
    cy.get('input[name=grade_name]').eq(0).type('T');
    cy.get('input[name=grade_point]').eq(0).type('5.00');
    cy.get('input[name=percent_to]').eq(0).type('100');
    cy.get('input[name=percent_from]').eq(0).type('95');
    cy.get('.button.wp-color-result').eq(0).click();
    cy.get('.wp-picker-input-wrap').eq(0).type('#1f86c1');
    cy.get('button').contains('Add new Grade').click();
    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.request.body).to.include('add_new_gradebook');
      expect(interception.response?.body.success).to.equal(true);
    });
  });
  it('should import grade settings', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('import_gradebook_sample_data')) {
        req.alias = 'ajaxRequest';
      }
    });
    cy.get('body').then(($body) => {
      if ($body.text().includes('No grading system has been defined to manage student grades')) {
        cy.get('button').contains('Import Sample Grade Data').click();
        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.request.body).to.include('import_gradebook_sample_data');
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });
  it('should edit a grade', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('update_gradebook')) {
        req.alias = 'ajaxRequest';
      }
    }).as('ajaxRequest');
    cy.visit(`${Cypress.env('base_url')}${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`);
    cy.get('.gradebook-edit-btn').contains('Edit').click();
    cy.get('input[name=grade_name]').eq(1).clear().type('E');
    cy.get('input[name=grade_point]').eq(1).clear().type('4.50');
    cy.get('input[name=percent_to]').eq(1).clear().type('99');
    cy.get('input[name=percent_from]').eq(1).clear().type('97');
    cy.get('.button.wp-color-result').eq(1).click();
    cy.get('#tutor-update-grade-color').clear().type('#1f8632');
    cy.get('button').contains('Update Grade').click();

    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.request.body).to.include('update_gradebook');
      expect(interception.response?.body.success).to.equal(true);
    });
  });
  it('should delete a grade', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.GRADEBOOK}&sub_page=gradebooks&data=grade-settings`);
    cy.get('.gradebook-delete-btn').eq(0).contains('Delete').click();
    cy.get('button').contains('Yes, Delete This').click();
    cy.contains('The grade has been deleted successfully');
  });
});
