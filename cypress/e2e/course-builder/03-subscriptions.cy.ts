import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { loginAsAdmin } from 'cypress/support/auth';
import { backendUrls } from '../../config/page-urls';

describe('Tutor Admin Subscriptions', () => {
  let courseId: string;
  let subscriptionData: {
    plan_name: string;
    regular_price: string;
    sale_price: string;
    recurring_value: string;
    enrollment_fee: string;
  };

  before(() => {
    subscriptionData = {
      plan_name: faker.commerce.productName(),
      regular_price: faker.commerce.price({ min: 50, max: 1000 }),
      sale_price: faker.commerce.price({ min: 20, max: 49 }),
      recurring_value: '1',
      enrollment_fee: faker.commerce.price({ min: 10, max: 25 }),
    };

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      courseId = fixture.courseId;
    });
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }
      if (req.body.includes(endpoints.GET_SUBSCRIPTIONS_LIST)) {
        req.alias = 'getSubscriptions';
      }
      if (req.body.includes(endpoints.SAVE_SUBSCRIPTION)) {
        req.alias = 'saveSubscription';
      }
      if (req.body.includes(endpoints.DELETE_SUBSCRIPTION)) {
        req.alias = 'deleteSubscription';
      }
      if (req.body.includes(endpoints.DUPLICATE_SUBSCRIPTION)) {
        req.alias = 'duplicateSubscription';
      }
    });
    loginAsAdmin();
    if (courseId) {
      cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    } else {
      cy.visit(`${backendUrls.COURSES}`);
      cy.get('a.tutor-table-link').first().click();
    }
    cy.waitAfterRequest('getCourseDetails');
    cy.waitAfterRequest('getSubscriptions');
  });

  it('should create a new subscription plan', () => {
    cy.get('[data-cy=add-subscription]').click();

    cy.get('[data-cy=tutor-modal]').should('be.visible');

    cy.get('input[name*=plan_name]').type(subscriptionData.plan_name);
    cy.get('input[name*=regular_price]').clear().type(subscriptionData.regular_price);
    cy.get('input[name*=recurring_value]').clear().type(subscriptionData.recurring_value);

    cy.getSelectInput('recurring_interval', 'Month(s)');

    cy.getSelectInput('recurring_limit', 'Until cancelled');

    cy.get('input[name*=charge_enrollment_fee]').check({ force: true });
    cy.get('input[name*=enrollment_fee]:not([type=checkbox])').type(subscriptionData.enrollment_fee, { force: true });

    cy.get('input[name*=is_featured]').check({ force: true });

    cy.get('[data-cy="save-subscription"]').click();
    cy.waitAfterRequest('saveSubscription');

    cy.get('[data-cy="tutor-toast"]').should('contain.text', 'successfully');
  });

  it('should edit an existing subscription plan', () => {
    cy.get('[data-cy=edit-subscription]').first().click({ force: true });

    cy.get('[data-cy=tutor-modal]').should('be.visible');

    cy.get('input[name*="plan_name"]').clear().type(`${subscriptionData.plan_name} (Edited)`);

    const newPrice = (parseFloat(subscriptionData.regular_price) + 10).toString();
    cy.get('input[name*="regular_price"]').clear().type(newPrice);

    cy.get('[data-cy=save-subscription]').click();
    cy.waitAfterRequest('saveSubscription');

    cy.get('[data-cy=tutor-toast]').should('contain.text', 'successfully');
  });

  it('should duplicate a subscription plan', () => {
    cy.get('[data-cy=edit-subscription]').first().click({ force: true });

    cy.get('[data-cy=tutor-modal]').should('be.visible');

    cy.get('[data-cy=duplicate-subscription]').click();

    cy.waitAfterRequest('duplicateSubscription');
    cy.get('[data-cy="tutor-toast"]').should('contain.text', 'successfully');
  });

  it('should delete a subscription plan', () => {
    cy.get('[data-cy=edit-subscription]').first().click({ force: true });
    cy.get('[data-cy=tutor-modal]').should('be.visible');

    cy.get('[data-cy=delete-subscription]').first().click();
    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=confirm-button]').click();
    });

    cy.waitAfterRequest('deleteSubscription');
    cy.get('[data-cy="tutor-toast"]').should('contain.text', 'successfully');
  });
});
