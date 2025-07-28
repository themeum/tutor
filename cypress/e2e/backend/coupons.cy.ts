import { faker } from '@faker-js/faker';
import { backendUrls } from 'cypress/config/page-urls';

const couponData = {
  title: faker.lorem.words(2),
  discountAmount: faker.number.int({ min: 1, max: 100 }),
};

const NO_DATA_MESSAGES = [
  'No Data Found from your Search/Filter',
  'No request found',
  'No Data Available in this Section',
  'No records found',
  'No Records Found',
];

describe('Coupon Management', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.LOGIN}`);
    cy.loginAsAdmin();

    // Navigate to coupons page and set up ajax interceptor if monetized by tutor
    cy.monetizedBy().then((monetizedBy) => {
      if (monetizedBy === 'tutor') {
        cy.visit(`${Cypress.env('base_url')}${backendUrls.COUPONS}`);
        cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`).as('ajaxRequest');
      }
    });
  });

  // Custom command to check if monetization is enabled and skip if not
  const runIfMonetized = (testFn: () => void) => {
    cy.monetizedBy().then((monetizedBy) => {
      if (monetizedBy !== 'tutor') {
        cy.log('Monetization is not enabled - skipping test');
        return;
      }
      testFn();
    });
  };

  // Custom command to check if data is available
  const ifDataAvailable = (callback: () => void) => {
    cy.get('body').then(($body) => {
      const bodyText = $body.text();
      const hasNoData = NO_DATA_MESSAGES.some((message) => bodyText.includes(message));

      if (hasNoData) {
        cy.log('No data available - skipping this check');
        return;
      }

      callback();
    });
  };

  it('should be able to create a coupon', () => {
    runIfMonetized(() => {
      cy.get('a').contains('Add New').click();
      cy.url().should('include', '&action=add_new');

      cy.getByInputName('coupon_title').type(couponData.title);
      cy.get('[data-cy=generate-code]').click();
      cy.getByInputName('coupon_code').invoke('val').should('not.be.empty');
      cy.getByInputName('discount_amount').type(String(couponData.discountAmount));

      cy.selectDate('start_date');
      cy.getSelectInput('start_time', '12:00 AM');

      cy.get('[data-cy=save-coupon]').click();
      cy.waitAfterRequest('ajaxRequest');
      cy.url().should('include', backendUrls.COUPONS);
    });
  });

  it('should be able to edit a coupon', () => {
    runIfMonetized(() => {
      cy.get('a.tutor-btn.tutor-btn-outline-primary.tutor-btn-sm').contains('Edit').first().click();
      cy.url().should('include', '&action=edit');

      cy.get('input[name=coupon_type]').should('be.disabled');
      const newDiscountAmount = faker.number.int({ min: 1, max: 99 });
      cy.getByInputName('discount_amount').clear().type(String(newDiscountAmount));

      cy.get('[data-cy=save-coupon]').click();
      cy.waitAfterRequest('ajaxRequest');
      cy.url().should('include', backendUrls.COUPONS);
    });
  });

  it('should filter by category', () => {
    runIfMonetized(() => {
      cy.unifiedFilterElements({
        selectFieldName: 'applies_to',
        resultColumnIndex: 3,
      });
    });
  });

  it('should perform bulk actions on one randomly selected coupon', () => {
    runIfMonetized(() => {
      // Test each action option
      const options = ['inactive', 'active', 'trash'];

      options.forEach((option) => {
        ifDataAvailable(() => {
          // Select a random checkbox
          cy.getByInputName('tutor-bulk-checkbox-all').then(($checkboxes) => {
            if ($checkboxes.length === 0) {
              cy.log('No checkboxes available');
              return;
            }

            const checkboxesArray = Cypress._.toArray($checkboxes);
            const randomIndex = Cypress._.random(0, checkboxesArray.length - 1);
            const randomCheckbox = checkboxesArray[randomIndex];

            // Store reference and index for later verification
            cy.wrap(randomCheckbox).as('randomCheckbox');
            cy.wrap(randomIndex).as('randomIndex');

            // Perform the bulk action
            cy.get('@randomCheckbox').check();
            cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
            cy.get(`span[tutor-dropdown-item][data-key=${option}]`).first().click();
            cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
            cy.get('#tutor-confirm-bulk-action').click();

            // Verify the status change after reload
            cy.reload();
            cy.get('@randomIndex').then((idx) => {
              const index = idx as unknown as number;
              return cy
                .get('.tutor-badge-label')
                .eq(index)
                .invoke('text')
                .then((status) => {
                  expect(status.toLowerCase()).to.include(option);
                });
            });
          });
        });
      });
    });
  });

  it('should be able to perform bulk actions on all coupons', () => {
    runIfMonetized(() => {
      // Test each action option
      const options = ['inactive', 'active', 'trash'];

      options.forEach((option) => {
        ifDataAvailable(() => {
          // Select all checkboxes
          cy.get('#tutor-bulk-checkbox-all').click();
          cy.get('.tutor-mr-12 > .tutor-js-form-select').click();

          cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
            .first()
            .invoke('text')
            .then((text) => {
              const expectedValue = text.trim();

              cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`).first().click();
              cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
              cy.get('#tutor-confirm-bulk-action').contains('Yes, I’m sure').click();

              cy.reload();
              cy.get('.tutor-badge-label').each(($label) => {
                cy.wrap($label)
                  .invoke('text')
                  .then((status) => {
                    expect(status.toLowerCase()).to.include(expectedValue.toLowerCase());
                  });
              });
            });
        });
      });
    });
  });
});
