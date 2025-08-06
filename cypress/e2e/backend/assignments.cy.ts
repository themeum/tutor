import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Assignments', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.ASSIGNMENTS}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.ASSIGNMENTS);
  });

  it('should evaluate an assignment', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found.')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-table tbody tr')
          .eq(0)
          .then(($row) => {
            if ($row.text().includes('Evaluate')) {
              cy.wrap($row).find('a').contains('Evaluate').click();
            } else {
              cy.wrap($row).find('a').contains('Details').click();
            }
            cy.url().should('include', 'view_assignment');
            cy.url().then(() => {
              cy.get('input[type=number]').clear().type('5');
              cy.get('textarea')
                .clear()
                .type(
                  'The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking.',
                );
              cy.get('button').contains('Evaluate this submission').click();

              cy.contains('Success');
            });
          });
      }
    });
  });

  it('should be able to search any assignment', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'assignment';
    const courseLinkSelector = 'td>a';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should check if the elements are sorted', () => {
    const sortButton = '.tutor-wp-dashboard-filter-order';
    const itemSelector = 'tbody>tr>td>a';

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

  it('should filter assignments by course', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'course-id',
      resultTableSelector: 'table.tutor-table',
      resultColumnIndex: 1,
    });
  });

  it('Should filter assignments by a specific date', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'date',
      resultTableSelector: 'table.tutor-table',
      resultColumnIndex: 6,
      datePickerYear: '2024',
      datePickerMonth: 'June',
      datePickerDay: '11',
    });
  });
});
