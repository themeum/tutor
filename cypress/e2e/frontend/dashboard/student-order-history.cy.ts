import { frontendUrls } from '../../../config/page-urls';

describe('Tutor Dashboard Student Order History', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ORDER_HISTORY}`);
    cy.loginAsStudent();
    cy.url().should('include', frontendUrls.dashboard.ORDER_HISTORY);
  });

  it.only('should filter from date range', () => {
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found.')) {
        cy.log('No data found');
      } else {
        cy.get("input[placeholder=' Y-M-d -- Y-M-d ']").click();
        cy.get('.react-datepicker__day.react-datepicker__day--001').eq(0).click();
        cy.get('.react-datepicker__day.react-datepicker__day--028:not(.react-datepicker__day--outside-month)')
          .eq(0)
          .click();
        cy.get('button').contains('Apply').click();

        cy.get('body').then(($body) => {
          if ($body.text().includes('No Data Found.')) {
            cy.log('No data found');
          }
        });
      }
    });
  });

  it('should filter based on today, monthly and yearly', () => {
    cy.get('.tutor-btn')
      .contains('Today')
      .click()
      .then(() => {
        cy.url().should('include', 'period=today');
      });
    cy.get('.tutor-btn')
      .contains('Monthly')
      .click()
      .then(() => {
        cy.url().should('include', 'period=monthly');
      });
    cy.get('.tutor-btn')
      .contains('Yearly')
      .click()
      .then(() => {
        cy.url().should('include', 'period=yearly');
      });
  });
});
