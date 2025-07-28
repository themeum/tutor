import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Courses', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSES}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.COURSES);
  });

  it('should filter by category', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'category',
      resultTableSelector: 'table.table-dashboard-course-list',
      resultColumnIndex: 3,
    });
  });

  it('should check if the elements are sorted', () => {
    const formSelector = '.tutor-wp-dashboard-filter-order';
    const itemSelector = 'select.tutor-table-row-status-update';
    function checkSorting(order: string) {
      if (order === 'ASC') {
        cy.get(formSelector).click();
      }
      cy.get('body').then(($body) => {
        if ($body.text().includes('No Courses Found.')) {
          cy.log('No data available');
        } else {
          cy.get(itemSelector).then(($items) => {
            const itemsIds = $items.map((_, item) => item.getAttribute('data-id')).get();
            if (order === 'ASC') {
              const isASC = itemsIds.every((id, index) => {
                return index === 0 || Number(id) >= Number(itemsIds[index - 1]);
              });
              cy.wrap(isASC).should('be.true');
            }
            if (order === 'DESC') {
              const isDESC = itemsIds.every((id, index) => {
                return index === 0 || Number(id) <= Number(itemsIds[index - 1]);
              });
              cy.wrap(isDESC).should('be.true');
            }
            cy.log(`Items are sorted in ${order} order`);
          });
        }
      });
    }
    checkSorting('DESC');
    checkSorting('ASC');
  });

  it('should show warning when no course is selected', () => {
    cy.get('.tutor-form-select-label').then(() => {
      cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
    });
    cy.contains('Nothing was selected for bulk action.');
  });

  it('should be able to search any course', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'JavaScript';
    const courseLinkSelector = '.tutor-table-link';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should be able to duplicate a course successfully', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Courses Found.')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-iconic-btn[action-tutor-dropdown]').eq(0).click();
        cy.get('.tutor-dropdown-item').contains('Duplicate').click();
        cy.get('.tutor-table-link')
          .eq(0)
          .invoke('text')
          .then((courseName) => {
            const duplicatedCourseName = `${courseName.trim()}`;
            void expect(duplicatedCourseName.includes('(Copy')).to.be.true;
            cy.contains(duplicatedCourseName).should('exist');
          });
      }
    });
  });

  it('should be able to delete a course successfully', () => {
    // admin should delete course
    cy.get('body').then(($body) => {
      cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
        if (req.body.includes('tutor_course_delete')) {
          req.alias = 'ajaxRequest';
        }
      }).as('ajaxRequest');
      if ($body.text().includes('No Courses Found.')) {
        cy.log('No data found');
      } else {
        // first trash course
        cy.get(':nth-child(1) > :nth-child(1) > .td-checkbox > .tutor-form-check-input').check();
        cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
        cy.get('[data-key=trash]').eq(0).should('be.visible').click();

        cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
        cy.get('#tutor-confirm-bulk-action').click();

        // go to trash tab
        cy.get('.tutor-wp-dashboard-filters-button').click();
        cy.get('.tutor-admin-dashboard-filter-form')
          .should('be.visible')
          .within(() => {
            cy.get('.tutor-wp-dashboard-filters-item').each(($item) => {
              if ($item.find('select[name="data"]').length) {
                cy.wrap($item).click();
                cy.wrap($item).within(() => {
                  cy.get('.tutor-form-select-options')
                    .should('be.visible')
                    .within(() => {
                      cy.get('.tutor-form-select-option').then(($options) => {
                        const trashOption = Array.from($options).find((option) =>
                          option.textContent?.includes('Trash'),
                        );
                        if (trashOption) {
                          cy.wrap(trashOption).click();
                        }
                      });
                    });
                });
              }
            });

            cy.get('button.tutor-btn.tutor-btn-outline-primary').contains('Apply Filters').click();
          });
        cy.get('.tutor-iconic-btn[action-tutor-dropdown]').eq(0).click();
        cy.get('.tutor-dropdown-item').contains('Delete Permanently').click();
        cy.get('#tutor-common-confirmation-form').find('button[data-tutor-modal-submit]').click();
        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });

  it('should perform bulk actions on one randomly selected course', () => {
    const options = ['publish', 'pending', 'draft', 'trash'];
    options.forEach((option) => {
      cy.performBulkActionOnSelectedElement(option);
    });
  });

  it('should be able to perform bulk actions on all courses', () => {
    const options = ['publish', 'pending', 'draft', 'trash'];
    options.forEach((option) => {
      cy.performBulkAction(option);
    });
  });
});
