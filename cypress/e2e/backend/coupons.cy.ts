import { faker } from '@faker-js/faker';
import { backendUrls } from 'cypress/config/page-urls';

const couponData = {
  title: faker.lorem.words(2),
  discountAmount: faker.number.int({ min: 1, max: 100 }),
};

describe('Coupon Management', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.LOGIN}`);
    cy.loginAsAdmin();
    cy.visit(`${Cypress.env('base_url')}${backendUrls.COUPONS}`);
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`).as('ajaxRequest');
  });

  it('should be able to create a coupon', () => {
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

  it('should be able to edit a coupon', () => {
    cy.get('a.tutor-btn.tutor-btn-outline-primary.tutor-btn-sm:contains("Edit")').eq(0).click();
    cy.url().should('include', '&action=edit');

    cy.get('input[name=coupon_type]').should('be.disabled');

    const newDiscountAmount = faker.number.int({ min: 1, max: 99 });
    cy.getByInputName('discount_amount').clear().type(String(newDiscountAmount));

    cy.get('[data-cy=save-coupon]').click();
    cy.waitAfterRequest('ajaxRequest');
    cy.url().should('include', backendUrls.COUPONS);
  });

  it.only('should filter by category', () => {
    cy.get('.tutor-js-form-select').eq(1).click();
    cy.get('.tutor-form-select-options')
      .eq(1)
      .then(() => {
        cy.get('.tutor-form-select-option')
          .then(($options) => {
            const randomIndex = Cypress._.random(6, $options.length - 3);
            const $randomOption = Cypress.$($options[randomIndex]);
            cy.wrap($randomOption).find('span[tutor-dropdown-item]').click();
          })
          .then(() => {
            cy.get('body').then(($body) => {
              if (
                $body.text().includes('No Data Found from your Search/Filter') ||
                $body.text().includes('No request found') ||
                $body.text().includes('No Data Available in this Section') ||
                $body.text().includes('No records found') ||
                $body.text().includes('No Records Found')
              ) {
                cy.log('No data available');
              } else {
                cy.get('span.tutor-form-select-label[tutor-dropdown-label]')
                  .eq(1)
                  .invoke('text')
                  .then((retrievedText) => {
                    cy.get('.tutor-badge-label').each(($category) => {
                      cy.wrap($category)
                        .invoke('text')
                        .then((categoryText) => {
                          expect(categoryText.trim().toLowerCase()).to.include(retrievedText.trim().toLowerCase());
                        });
                    });
                  });
              }
            });
          });
      });
  });

  it('should perform bulk actions on one randomly selected coupon', () => {
    const options = ['inactive', 'active', 'trash'];
    options.forEach((option) => {
      cy.get('body').then(($body) => {
        if (
          $body.text().includes('No Data Available in this Section') ||
          $body.text().includes('No Data Found from your Search/Filter') ||
          $body.text().includes('No request found') ||
          $body.text().includes('No records found') ||
          $body.text().includes('No Records Found')
        ) {
          cy.log('No data available');
        } else {
          cy.getByInputName('tutor-bulk-checkbox-all').then(($checkboxes) => {
            const checkboxesArray = Cypress._.toArray($checkboxes);
            const randomIndex = Cypress._.random(0, checkboxesArray.length - 1);
            cy.wrap(checkboxesArray[randomIndex]).as('randomCheckbox');
            cy.get('@randomCheckbox').check();
            cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
            cy.get(`span[tutor-dropdown-item][data-key=${option}]`).first().click();

            cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
            cy.get('#tutor-confirm-bulk-action').click();
            cy.reload();
            cy.get('@randomCheckbox')
              .invoke('attr', 'value')
              .then(() => {
                cy.get('.tutor-badge-label')
                  .eq(randomIndex)
                  .invoke('text')
                  .then((status) => {
                    expect(status.toLowerCase()).to.include(option);
                  });
              });
          });
        }
      });
    });
  });

  it('should be able to perform bulk actions on all coupons', () => {
    const options = ['inactive', 'active', 'trash'];
    options.forEach((option) => {
      cy.get('body').then(($body) => {
        if (
          $body.text().includes('No Data Found from your Search/Filter') ||
          $body.text().includes('No request found') ||
          $body.text().includes('No Data Available in this Section') ||
          $body.text().includes('No records found') ||
          $body.text().includes('No Records Found')
        ) {
          cy.log('No data available');
        } else {
          cy.get('#tutor-bulk-checkbox-all').click();
          cy.get('.tutor-mr-12 > .tutor-js-form-select').click();

          cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
            .invoke('text')
            .then((text) => {
              const expectedValue = text.trim();
              cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`).first().click();
              cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
              cy.get('#tutor-confirm-bulk-action').contains('Yes, I’m sure').click();
              cy.reload();

              cy.get('.tutor-badge-label')
                .invoke('text')
                .then((selectedValue) => {
                  expect(selectedValue.toLowerCase()).to.include(expectedValue.toLowerCase());
                });
            });
        }
      });
    });
  });
});
