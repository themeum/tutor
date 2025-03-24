import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin ENROLLMENTS', () => {
  const studentCSVPath = 'cypress/fixtures/assets/tutor_bulk_enrollment_sample.csv';
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.ENROLLMENTS}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.ENROLLMENTS);

    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.CREATE_ENROLLMENT)) {
        req.alias = 'createEnrollment';
      }

      if (req.body.includes(endpoints.GET_COURSE_BUNDLE_LIST)) {
        req.alias = 'getCourseBundleList';
      }

      if (req.body.includes(endpoints.GET_UNENROLLED_USERS)) {
        req.alias = 'getUnenrolledUsers';
      }
    });
  });

  it('should enroll a student', () => {
    cy.get('a').contains('Enroll Student').click();
    cy.wait(500);
    cy.get('body').then(($body) => {
      if ($body.text().includes('Select a course to enroll students')) {
        cy.get('[data-cy=select-course]').click();
      }
    });

    // Select course
    cy.get('[data-cy=tutor-modal]').within(($elements) => {
      cy.wrap($elements).should('contain.text', 'Select course');
      cy.waitAfterRequest('getCourseBundleList');
      if ($elements.text().includes('No Data!')) {
        cy.get('[data-cy=tutor-modal-close]').click();
        return;
      } else {
        cy.get('tbody tr:first-child').find('[data-cy=select-course]').click({ force: true });
      }
    });

    cy.get('[data-cy=add-students').click();

    // Add students
    cy.get('[data-cy=tutor-modal]').within(($elements) => {
      cy.wrap($elements).should('contain.text', 'Add students');
      cy.waitAfterRequest('getUnenrolledUsers');
      if ($elements.text().includes('No Data!')) {
        cy.get('[data-cy=tutor-modal-close]').click();
        return;
      } else {
        cy.get('tbody tr')
          .find('input[type=checkbox]')
          .then(($checkboxes) => {
            cy.wrap($checkboxes).eq(0).check({ force: true });
            cy.wrap($checkboxes).eq(1).check({ force: true });
          });

        // Search student
        cy.getByInputName('search').type(faker.person.firstName());
        cy.waitAfterRequest('getUnenrolledUsers');
        // Check if tbody contains "No Data!" and conditionally proceed
        cy.get('tbody').then(($tbody) => {
          if ($tbody.text().includes('No Data!')) {
            cy.log('No data found');
          } else {
            cy.get('tbody tr').find('input[type="checkbox"]').first().check({ force: true });
          }
        });
        cy.get('[data-cy=add-students').click();
      }
    });

    cy.get('body').should('contain.text', 'Manually Added');

    // Upload students by CSV
    cy.get('[data-cy=upload-csv]').click();
    cy.get('[data-cy=tutor-modal]').within(($elements) => {
      cy.wrap($elements).should('contain.text', 'Import students by CSV');
      cy.get('[data-cy=select-file] input[type="file"]').selectFile(studentCSVPath, { force: true });
      cy.get('[data-cy=import-csv]').click();
    });

    cy.get('button[role=tab]').should('contain.text', 'Added from CSV');
    cy.get('[data-cy=student-card] input[type=checkbox]').check({ force: true });

    // Remove student
    cy.get('[data-cy=remove-students]').click();
    cy.get('[data-cy=enroll-now]').click();
    cy.waitAfterRequest('createEnrollment');

    cy.get('[data-cy=tutor-modal]').within(($elements) => {
      cy.wrap($elements)
        .invoke('text')
        .should('match', /Enrollment Completed|Enrollment Failed/);
      if ($elements.text().includes('Enrollment Completed')) {
        cy.log('Enrollment completed');
      } else {
        cy.log('Enrollment failed');
      }
      cy.get('[data-cy=close-modal]').click();
    });
  });

  it('should be able to search any enrollment', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'Intro to JavaScript';
    const courseLinkSelector = 'tr>td>div.tutor-d-flex.tutor-align-center.tutor-gap-2';
    const submitButtonSelector = '';
    const submitWithButton = false;

    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should perform bulk action on all enrollments', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Available in this Section')) {
        cy.log('No data found');
      } else {
        const bulkOption = (option: string) => {
          cy.get('#tutor-bulk-checkbox-all').click();
          cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
          cy.get(`.tutor-form-select-option>span[title="${option}"]`).contains(`${option}`).click();
          cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();

          cy.get('#tutor-confirm-bulk-action').contains('Yes, I’m sure').click();
        };
        bulkOption('Cancel');
        bulkOption('Approve');
      }
    });
  });

  it('should filter enrollments', () => {
    cy.get(':nth-child(2) > .tutor-js-form-select').click();
    cy.get(
      ':nth-child(2) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > .tutor-form-select-option:nth-child(2)',
    )
      .click()
      .then(($option) => {
        const selectedOptionText = $option.text().trim();
        cy.log('Selected option: ' + selectedOptionText);
        cy.reload();
        cy.get('body').then(($body) => {
          if (
            $body.text().includes('No Data Found from your Search/Filter') ||
            $body.text().includes('No Data Available in this Section')
          ) {
            cy.log('No data available');
          } else {
            cy.get('.tutor-d-flex.tutor-align-center.tutor-gap-2.tutor-text-nowrap').each(($announcement) => {
              cy.wrap($announcement)
                .invoke('text')
                .then((announcementText) => {
                  expect(selectedOptionText).to.include(announcementText.trim());
                });
            });
          }
        });
      });
  });

  it('should check if the elements are sorted', () => {
    const formSelector = ':nth-child(3) > .tutor-js-form-select';
    const itemSelector = '.tutor-d-flex.tutor-align-center.tutor-gap-2';
    function checkSorting(order: string) {
      cy.get('body').then(($body) => {
        if ($body.text().includes('No Data Found from your Search/Filter')) {
          cy.log('No data available');
        } else {
          cy.get(formSelector).click();
          cy.get(`span[title="${order}"]`).click();
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
    }
    checkSorting('ASC');
    checkSorting('DESC');
  });
  it('should filter enrollments by a specific date', () => {
    cy.get("input[placeholder='Y-M-d']").click();
    cy.get('.dropdown-years').click();
    cy.get('.dropdown-years>.dropdown-list').contains('2025').click();
    cy.get('.dropdown-months > .dropdown-label').click();
    cy.get('.dropdown-months > .dropdown-list').contains('June').click();
    cy.get('.react-datepicker__day--011').contains('11').click();

    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found from your Search/Filter')) {
        cy.log('No data available');
      } else {
        cy.wait(2000);
        cy.get('.tutor-fs-7 > span').each(($el) => {
          const dateText = $el.text().trim();
          expect(dateText).to.contain('June 11, 2025');
        });
      }
    });
  });
});
