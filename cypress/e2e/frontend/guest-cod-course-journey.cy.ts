import { frontendUrls } from 'cypress/config/page-urls';

describe('Tutor Student Paid Course Journey', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.DASHBOARD}`);
    cy.loginAsStudent();
    cy.visit(`${Cypress.env('base_url')}/courses/${Cypress.env('paid_course_slug')}/`);
  });

  it('should be able to enroll in a paid course, view cart, and manage items as a guest', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

    cy.isEnrolled().then((isEnrolled) => {
      if (!isEnrolled) {
        cy.get('body').then(($body) => {
          if ($body.text().includes('Add to cart') || $body.text().includes('Add to Cart')) {
            cy.get('button').then(($btn) => {
              if ($btn.text().includes('Add to cart')) {
                cy.get('button').contains('Add to cart').click();
              } else if ($btn.text().includes('Add to Cart')) {
                cy.get('button').contains('Add to Cart').click();
              }
            });

            cy.get('a').contains('View Cart').click();
          } else if ($body.text().includes('View Cart')) {
            cy.get('a').contains('View Cart').click();
          }
        });

        cy.url().then((url) => {
          cy.monetizedBy().then((monetizedBy) => {
            if (monetizedBy === 'wc') {
              if (url.includes(frontendUrls.WOOCOMMERCE_CART)) {
                cy.get('a').contains('Proceed to Checkout').click();
                cy.url().should('include', frontendUrls.WOOCOMMERCE_CHECKOUT);
                const randomEmail = `guest${Math.random().toString().slice(2)}@gmail.com`;

                cy.get('#email').clear().type(randomEmail);

                cy.get('#billing-first_name').clear().type('Guest1');
                cy.get('#billing-last_name').clear().type('Test');
                cy.get('#billing-address_1').clear().type('123 Main Street');

                cy.get('#billing-city').clear().type('New York');

                cy.get('#billing-postcode').clear().type('96799');
                cy.get('#billing-phone').clear().type('+8801555123456');

                cy.get('.wc-block-components-radio-control-accordion-option')
                  .eq(2)
                  .then(() => {
                    cy.get(':nth-child(3) > .wc-block-components-radio-control__option').click();
                  });

                cy.get('body').then(($body) => {
                  if ($body.find('.tutor-icon-times').length > 0) {
                    cy.get('.tutor-icon-times').click();
                  }
                });

                // cy.get('button')
                //   .contains("Place Order")
                //   .click({force:true});

                cy.get('.wc-block-components-button').click({ force: true });
                cy.url().should('include', '/order-received');
                // redirect to admin dashboard and login
                cy.visit(`${Cypress.env('base_url')}/wp-login.php`);
                cy.loginAsAdmin();
                cy.visit(`${Cypress.env('base_url')}/wp-admin/admin.php?page=wc-orders`);

                cy.get("input[name='id[]']")
                  .eq(0)
                  .invoke('attr', 'value')
                  .then((value) => {
                    const selector = `#cb-select-${value}`;

                    cy.get(selector).should('be.visible').check();
                  });

                cy.get('#bulk-action-selector-top')
                  .select('Change status to completed')
                  .should('have.value', 'mark_completed');

                cy.get('#doaction').contains('Apply').click();
              }
            }

            if (monetizedBy === 'tutor') {
              if (url.includes(frontendUrls.TUTOR_CART)) {
                cy.get('a').contains('Proceed to checkout').click();
                cy.url().should('include', frontendUrls.TUTOR_CHECKOUT);
              }
            }
          });

          // redirect to course
          cy.visit(`${Cypress.env('base_url')}/courses/${Cypress.env('cod_course_slug')}/}`);
        });
      }
    });

    cy.visit(`${Cypress.env('base_url')}/dashboard/enrolled-courses/`);
    cy.loginAsStudent();
    // cy.get('.tutor-course-name').eq(0).click();
    cy.get('body').then(($body) => {
      if ($body.text().includes('Continue Learning')) {
        cy.get('a').contains('Continue Learning').click();
        cy.handleCourseStart();

        cy.isEnrolled().then((isEnrolled) => {
          if (isEnrolled) {
            cy.get('.tutor-course-topic-item').each(($topic, index, $list) => {
              const isLastItem = index === $list.length - 1;

              cy.url().then(($url) => {
                if ($url.includes('/lesson')) {
                  cy.completeLesson();
                  cy.handleNextButton();
                }

                if ($url.includes('/assignments')) {
                  cy.handleAssignment(isLastItem);
                }

                if ($url.includes('/quizzes')) {
                  cy.handleQuiz();
                }

                if ($url.includes('/meet-lessons')) {
                  cy.handleMeetingLesson(isLastItem);
                }

                if ($url.includes('/zoom-lessons')) {
                  cy.handleZoomLesson(isLastItem);
                }
              });
            });
          }
        });

        cy.completeCourse();
        cy.submitCourseReview();
        cy.viewCertificate();
      } else if ($body.text().includes('Start Learning')) {
        cy.get('a').contains('Start Learning').click();

        cy.handleCourseStart();

        cy.isEnrolled().then((isEnrolled) => {
          if (isEnrolled) {
            cy.get('.tutor-course-topic-item').each(($topic, index, $list) => {
              const isLastItem = index === $list.length - 1;

              cy.url().then(($url) => {
                if ($url.includes('/lesson')) {
                  cy.completeLesson();
                  cy.handleNextButton();
                }

                if ($url.includes('/assignments')) {
                  cy.handleAssignment(isLastItem);
                }

                if ($url.includes('/quizzes')) {
                  cy.handleQuiz();
                }

                if ($url.includes('/meet-lessons')) {
                  cy.handleMeetingLesson(isLastItem);
                }

                if ($url.includes('/zoom-lessons')) {
                  cy.handleZoomLesson(isLastItem);
                }
              });
            });
          }
        });

        cy.completeCourse();
        cy.submitCourseReview();
        cy.viewCertificate();
      } else {
        cy.log('No data found');
      }
    });
  });
});
