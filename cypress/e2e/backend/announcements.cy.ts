import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Announcements', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.ANNOUNCEMENTS}`);
    cy.loginAsAdmin();
    cy.url().should('include', backendUrls.ANNOUNCEMENTS);
  });

  it('should create a new announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_announcement_create')) {
        req.alias = 'ajaxRequest';
      }
    });
    cy.get('button[data-tutor-modal-target=tutor_announcement_new]').click();
    cy.get('#tutor_announcement_new input[name=tutor_announcement_title]').type(
      'Important Announcement - Upcoming Student Assembly',
    );
    cy.get('#tutor_announcement_new textarea[name=tutor_announcement_summary]').type(
      'I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly.',
    );
    cy.get('#tutor_announcement_new button').contains('Publish').click();

    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.response?.body.success).to.equal(true);
    });
  });

  it('should update an announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_announcement_create')) {
        req.alias = 'ajaxRequest';
      }
    });

    cy.get('button[data-tutor-modal-target=tutor_announcement_new]').click();
    cy.get('#tutor_announcement_new input[name=tutor_announcement_title]')
      .clear()
      .type('Important Announcement - Updated Announcement Title');
    cy.get('#tutor_announcement_new textarea[name=tutor_announcement_summary]').type(
      'I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly.',
    );
    cy.get('#tutor_announcement_new button').contains('Publish').click();

    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.response?.body.success).to.equal(true);
    });
  });

  it('should view and delete an announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_announcement_delete')) {
        req.alias = 'ajaxRequest';
      }
    });

    cy.get('body').then(($body) => {
      if (
        $body.text().includes('No Data Found from your Search/Filter') ||
        $body.text().includes('No request found') ||
        $body.text().includes('No Data Available in this Section') ||
        $body.text().includes('No records found') ||
        $body.text().includes('No Records Found')
      ) {
        cy.log('No data found');
      } else {
        cy.get('button.tutor-announcement-details').eq(0).click();
        cy.get('.tutor-modal.tutor-is-active button.tutor-modal-btn-delete').click();
        cy.get('.tutor-modal.tutor-is-active button').contains('Yes, Delete This').click();

        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.body.success).to.equal(true);
        });
      }
    });
  });

  it('should be able to search any announcement', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'Important Announcement';
    const courseLinkSelector = '.td-course.tutor-color-black.tutor-fs-6.tutor-fw-medium';
    const submitButtonSelector = '';
    const submitWithButton = false;

    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should filter announcements by courses', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'course-id',
      resultTableSelector: 'table.tutor-table',
      resultColumnIndex: 3,
    });
  });

  it('Should filter announcements by a specific date', () => {
    cy.unifiedFilterElements({
      selectFieldName: 'date',
      resultTableSelector: 'table.tutor-table',
      resultColumnIndex: 2,
      datePickerYear: '2024',
      datePickerMonth: 'June',
      datePickerDay: '11',
    });
  });

  it('should perform bulk action on an announcement', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('delete')) {
        req.alias = 'ajaxRequest';
      }
    });
    cy.get('body').then(($body) => {
      if (
        $body.text().includes('No Data Found from your Search/Filter') ||
        $body.text().includes('No request found') ||
        $body.text().includes('No Data Available in this Section') ||
        $body.text().includes('No records found') ||
        $body.text().includes('No Records Found')
      ) {
        cy.log('No data found');
      } else {
        cy.getByInputName('tutor-bulk-checkbox-all').eq(1).check();
        cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
        cy.get(
          '.tutor-mr-12 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis',
        )
          .contains('Delete Permanently')
          .click();
        cy.get('#tutor-admin-bulk-action-btn').click();
        cy.get('#tutor-confirm-bulk-action').contains('Yes, I’m sure').click();
        cy.url().should('include', backendUrls.ANNOUNCEMENTS);
      }
    });
  });
});
