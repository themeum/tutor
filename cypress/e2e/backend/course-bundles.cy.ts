import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Course Bundles', () => {
  let bundleData: { post_title: string; post_content: string; course_benefits: string };

  before(() => {
    bundleData = {
      post_title: faker.lorem.sentences(1),
      post_content: faker.lorem.sentences(3),
      course_benefits: faker.lorem.sentences(3),
    };
  });

  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSE_BUNDLES}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.COURSE_BUNDLES);

    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_BUNDLE_DETAILS)) {
        req.alias = 'getBundleDetails';
      }

      if (req.body.includes(endpoints.UPDATE_BUNDLE)) {
        req.alias = 'updateBundle';
      }

      if (req.body.includes(endpoints.COURSE_LIST)) {
        req.alias = 'courseList';
      }

      if (req.body.includes(endpoints.ADD_COURSE_TO_BUNDLE)) {
        req.alias = 'addCourseToBundle';
      }
    });
  });

  it('should check if the elements are sorted', () => {
    const formSelector = ':nth-child(3) > .tutor-js-form-select';
    const itemSelector = '.tutor-d-flex.tutor-align-center.tutor-gap-2 > div > a.tutor-table-link';
    function checkSorting(order: string) {
      cy.get(formSelector).click();
      cy.get(`span[title=${order}]`).click();
      cy.get('body').then(($body) => {
        if ($body.text().includes('No Data Available in this Section')) {
          cy.log('No data available');
        } else {
          cy.get(itemSelector).then(($items) => {
            const itemTexts = $items
              .map((_, item) => item.innerText.trim())
              .get()
              .filter((text) => text);
            const sortedItems = order === 'ASC' ? itemTexts.sort() : itemTexts.sort().reverse();
            expect(itemTexts).to.deep.equal(sortedItems);
          });
        }
      });
    }
    checkSorting('ASC');
    checkSorting('DESC');
  });

  it('should be able to search any course bundle', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'test';
    const courseLinkSelector = '.tutor-table-link';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should filter by category', () => {
    cy.filterByCategory();
  });

  it('should perform bulk actions on selected bundle course', () => {
    const options = ['publish', 'pending', 'draft', 'trash'];
    options.forEach((option) => {
      cy.performBulkActionOnSelectedElement(option);
    });
  });

  it('should create a bundle with basic information', () => {
    cy.get('.tutor-add-new-course-bundle').contains('Add New').click();

    cy.waitAfterRequest('getBundleDetails');

    // Only focus on creating basic bundle information
    cy.getByInputName('post_title').type(bundleData.post_title);
    cy.setTinyMceContent('[data-cy=tutor-tinymce]', bundleData.post_content);
    cy.getByInputName('course_benefits').type(bundleData.course_benefits);

    cy.get('[data-cy=bundle-builder-submit-button]').click();
    cy.waitAfterRequest('updateBundle');
    cy.get('[data-cy=tutor-toast]').should('contain.text', 'Course Bundle updated successfully');
  });

  it('should add courses to a bundle', () => {
    // Find the first bundle to edit
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No bundles available to test adding courses');
        return;
      }

      // Navigate to edit the first bundle
      cy.get('.tutor-table tbody tr button[action-tutor-dropdown=toggle]').eq(0).click();
      cy.get('.tutor-dropdown-parent.is-open a').contains('Edit').click();
      cy.url().should('include', 'action=edit');

      cy.waitAfterRequest('getBundleDetails');

      // Add course to bundle
      cy.get('[data-cy=add-course]').click();
      cy.get('[data-cy=tutor-modal]').within(($elements) => {
        cy.wrap($elements).should('contain.text', 'Add Course');
        cy.waitAfterRequest('courseList');
        if ($elements.text().includes('No Data!')) {
          cy.get('[data-cy=tutor-modal-close]').click();
          return;
        } else {
          cy.get('tbody tr:first-child').find('[data-cy=select-course]').click({ force: true });
        }
        cy.waitAfterRequest('addCourseToBundle');
      });

      cy.get('[data-cy=bundle-builder-submit-button]').click();
      cy.get('[data-cy=tutor-toast]').should('contain.text', 'Course Bundle updated successfully');
    });
  });

  it('should remove courses from a bundle', () => {
    // Find the first bundle to edit
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No bundles available to test removing courses');
        return;
      }

      // Navigate to edit the first bundle
      cy.get('.tutor-table tbody tr button[action-tutor-dropdown=toggle]').eq(0).click();
      cy.get('.tutor-dropdown-parent.is-open a').contains('Edit').click();
      cy.url().should('include', 'action=edit');

      cy.waitAfterRequest('getBundleDetails');

      // Remove course from bundle if any exists
      cy.get('[data-cy=course-selection]').then(($elements) => {
        if ($elements.text().includes('No Courses Added Yet')) {
          cy.log('No courses to remove');
          return;
        }

        cy.get('[data-cy=remove-course]').first().click({ force: true });
        cy.waitAfterRequest('addCourseToBundle');

        cy.get('[data-cy=bundle-builder-submit-button]').click();
        cy.get('[data-cy=tutor-toast]').should('contain.text', 'Course Bundle updated successfully');
      });
    });
  });

  it('should visit a random bundle product', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-table tbody tr').then(($rows) => {
          const randomNumber = Math.floor(Math.random() * $rows.length);
          cy.wrap($rows[randomNumber])
            .find('a')
            .invoke('attr', 'href')
            .then((link) => {
              if (link) {
                cy.visit(link);
                cy.url().should('eq', link);
              } else {
                cy.log('Link not found');
              }
            });
        });
      }
    });
  });

  it('should change a bundle products status', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-table tbody tr')
          .eq(0)
          .then(($row) => {
            cy.wrap($row).find('button[action-tutor-dropdown=toggle]').click();

            for (let index = 0; index < 2; index++) {
              cy.wrap($row)
                .find('.tutor-table-row-status-update')
                .invoke('attr', 'data-status')
                .then((status) => {
                  if (status !== 'draft') {
                    cy.wrap($row).find('.tutor-table-row-status-update').select('draft');
                  } else {
                    cy.wrap($row).find('.tutor-table-row-status-update').select('publish');
                  }
                });

              cy.wait('@ajaxRequest').then((interception) => {
                expect(interception.response?.body.success).to.equal(true);
              });
            }
          });
      }
    });
  });

  it('should delete a bundle product successfully', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        cy.get('.tutor-table tbody tr button[action-tutor-dropdown=toggle]').eq(0).click();
        cy.get('.tutor-dropdown-parent.is-open a').contains('Edit').click();
        cy.waitAfterRequest('getBundleDetails');

        cy.doesElementExist('[data-cy=dropdown-trigger]').then((exists) => {
          if (!exists) {
            cy.log('No dropdown trigger found. Can not delete');
            return;
          }

          cy.get('[data-cy=dropdown-trigger]').click();
          cy.get('[data-cy=move-to-trash]').click();
          cy.waitAfterRequest('updateBundle');
        });
      }
    });
  });
});
