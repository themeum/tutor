import { frontendUrls } from '../../../config/page-urls';

describe('Tutor Dashboard Student Calendar', () => {
  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('get_calendar_materials')) {
        req.alias = 'calendarAjaxRequest';
      }
    });
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.CALENDAR}`);
    cy.loginAsStudent();
    cy.url().should('include', frontendUrls.dashboard.CALENDAR);
  });

  it('should visit all the upcoming events', () => {
    cy.wait('@calendarAjaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No data found in this section')) {
        cy.log('No data found');
      } else {
        cy.get('.meta-info>a').each(($item) => {
          cy.wrap($item)
            .invoke('attr', 'href')
            .then((link) => {
              if (link) {
                cy.visit(link);
                cy.wait(1000);
                cy.url().should('eq', link);
                cy.go('back');
                cy.wait('@calendarAjaxRequest');
              } else {
                cy.log('Link not found');
              }
            });
        });
      }
    });
  });
});
