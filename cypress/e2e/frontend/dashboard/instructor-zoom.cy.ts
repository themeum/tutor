import { type ZoomMeetingFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import endpoints from '@TutorShared/utils/endpoints';
import { frontendUrls } from '../../../config/page-urls';

let courseId: string = '';
describe('Tutor Dashboard Zoom', () => {
  // @ts-ignore
  const zoomMeetingData: ZoomMeetingFormData = {
    meeting_name: faker.lorem.sentence(),
    meeting_summary: faker.lorem.sentences(3),
    meeting_duration: String(faker.number.int({ min: 1, max: 60 })),
    meeting_time: '08:30 PM',
    meeting_duration_unit: 'min',
    meeting_timezone: '(GMT+6:00) Astana, Dhaka',
    meeting_password: '1234',
  };
  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ZOOM}`);
    cy.loginAsInstructor();
    cy.url().should('include', frontendUrls.dashboard.ZOOM);

    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }

      if (req.body.includes(endpoints.SAVE_ZOOM_MEETING)) {
        req.alias = 'saveZoomMeeting';
      }
    }).as('ajaxRequest');
  });

  //   set api and save connection
  it('should set and save zoom api connection', () => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.ZOOM}/set-api`);
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get("input[name='tutor_zoom_api[account_id]']")
      .clear()
      .type(`${Cypress.env('instructor_zoom_account_id')}`);
    cy.get("input[name='tutor_zoom_api[api_key]']")
      .clear()
      .type(`${Cypress.env('instructor_zoom_client_id')}`);
    cy.get("input[name='tutor_zoom_api[api_secret]']")
      .clear()
      .type(`${Cypress.env('instructor_zoom_client_secret')}`);
    cy.get('button#save-changes').click();

    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.response?.body.success).to.equal(true);
    });
  });

  // create new course and zoom meeting
  it('should create new course and zoom meeting', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');
    cy.get('a.tutor-create-new-course.tutor-dashboard-create-course').click();
    cy.url().should('include', '/create-course');
    cy.waitAfterRequest('getCourseDetails');
    cy.getByInputName('post_title').clear().type('Zoom test course');
    cy.get('[data-cy=tutor-tracker]').contains('Additional').click();

    cy.wait(1000);

    cy.isAddonEnabled(Addons.TUTOR_ZOOM_INTEGRATION).then((isEnabled) => {
      if (isEnabled) {
        cy.get('[data-cy=create-zoom-meeting]').click().wait(500);
        cy.get('.tutor-portal-popover').within(() => {
          cy.getByInputName('meeting_name').type(zoomMeetingData.meeting_name);
          cy.getByInputName('meeting_duration').type(zoomMeetingData.meeting_duration);
        });

        // cy.wait(500);
        cy.getByInputName('meeting_summary').click({ force: true }).type(zoomMeetingData.meeting_summary);
        cy.selectDate('meeting_date');
        cy.getSelectInput('meeting_time', '11:30 PM');
        cy.getSelectInput('meeting_timezone', zoomMeetingData.meeting_timezone);
        cy.wait(500);
        cy.getByInputName('meeting_password').scrollIntoView().click().type(zoomMeetingData.meeting_password);

        cy.get('[data-cy=save-zoom-meeting]').click();

        cy.waitAfterRequest('saveZoomMeeting');

        cy.get('[data-cy=tutor-toast]').should('contain.text', 'Meeting Successfully Added');

        return;
      }

      cy.log('Zoom Addon is not enabled');
    });

    cy.get('[data-cy=course-builder-submit-button]').click();

    cy.url()
      .should('include', 'course_id=')
      .then((url) => {
        courseId = url.split('course_id=')[1].split('#/')[0];
        cy.log(`Course ID: ${courseId}`);
        cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');
      });
  });

  it('should start meeting', () => {
    cy.get('a.tutor-btn.tutor-btn-primary').contains('Start Meeting').invoke('removeAttr', 'target').click();
    cy.origin('https://us05web.zoom.us', () => {
      cy.url().should('include', 'zoom.us');
    });
  });

  it('should edit a zoom meeting', () => {
    cy.intercept('POST', '/wordpress-tutor/wp-admin/admin-ajax.php').as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found from your Search/Filter')) {
        cy.log('No data available');
      } else {
        cy.get(".tutor-zoom-meeting-item button[action-tutor-dropdown='toggle']").eq(1).click();
        cy.get('a.tutor-dropdown-item').contains('Edit').click();
        cy.get("input[data-name='meeting_title']").eq(0).clear().type('Edited test zoom meeting');

        cy.get("textarea[data-name='meeting_summary'")
          .eq(0)
          .clear()
          .type('Edited zoom meeting summary', { force: true });
        cy.get("input[data-name='meeting_duration']").eq(0).clear().type('1');
        cy.get('input[data-name="meeting_time"]').eq(0).clear().type('08:00 PM');
        cy.get('select[data-name="meeting_duration_unit"]').eq(0).select('Hours');
        cy.get("div[class='tutor-col-6'] div[class='tutor-form-control tutor-form-select tutor-js-form-select']")
          .eq(0)
          .click();

        cy.get(
          '.meeting-modal-form-wrap > :nth-child(4) > :nth-child(1) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(108) > .tutor-nowrap-ellipsis',
        )
          .eq(0)
          .click();
        cy.get("select[data-name='auto_recording']").eq(0).select('No Recordings');
        cy.get("input[data-name='meeting_password']").eq(0).type('1234');
        cy.get('button').contains('Update Meeting').click();
        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.statusCode).to.equal(200);
        });
      }
    });
  });

  it('should delete a zoom meeting', () => {
    cy.intercept('POST', '/wordpress-tutor/wp-admin/admin-ajax.php').as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found from your Search/Filter')) {
        cy.log('No data available');
      } else {
        cy.get(".tutor-zoom-meeting-item button[action-tutor-dropdown='toggle']").eq(0).click();
        cy.get('a').contains('Delete').click({ force: true });
        cy.get('button').contains('Yes, Delete This').click();

        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.statusCode).to.equal(200);
        });
      }
    });
  });

  // settings
  it('should allow users to check checkboxes', () => {
    cy.get(':nth-child(4) > .tutor-nav-link').contains('Settings').click();
    cy.get('input[name="tutor_zoom_settings[join_before_host]"]').check({ force: true }).should('be.checked');
    cy.get('input[name="tutor_zoom_settings[host_video]"]').check({ force: true }).should('be.checked');
    cy.get('input[name="tutor_zoom_settings[participants_video]"]').check({ force: true }).should('be.checked');
    cy.get('input[name="tutor_zoom_settings[mute_participants]"]').check({ force: true }).should('be.checked');
    cy.get('input[name="tutor_zoom_settings[enforce_login]"]').check({ force: true }).should('be.checked');
    // recording options
    cy.get('input#tutor_zoom_rec_none').check().should('be.checked');
    cy.get('input#tutor_zoom_rec_cloud').should('not.be.checked');
  });

  // help
  it('Should make corresponding elements visible when accordion is clicked', () => {
    cy.get(':nth-child(5) > .tutor-nav-link').contains('Help').click();
    cy.get('.tutor-accordion-panel-handler-label').each(($accordion, index) => {
      cy.wrap($accordion).click();
      cy.get(`.tutor-fs-7.tutor-color-secondary`).eq(index).should('be.visible');
    });
  });

  it('should be able to search any meeting', () => {
    const searchInputSelector =
      '.tutor-wp-dashboard-filter-items > :nth-child(1) > .tutor-form-wrap > .tutor-form-control';
    const searchQuery = 'New Zoom Meeting';
    const courseLinkSelector = '.tutor-table-link';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should filter meetings', () => {
    cy.get('.tutor-my-lg-0 > .tutor-js-form-select').click();

    cy.get('.tutor-form-select-options').then(() => {
      cy.get('.tutor-form-select-option')
        .then(() => {
          // cy.get('.tutor-dropdown-item').eq(1).click();
          cy.get(
            '.tutor-my-lg-0 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis',
          ).click();
        })
        .then(() => {
          cy.get('body').then(($body) => {
            if ($body.text().includes('No Data Found.')) {
              cy.log('No data available');
            } else {
              cy.get('span.tutor-form-select-label[tutor-dropdown-label]')
                .eq(1)
                .invoke('text')
                .then((retrievedText) => {
                  cy.get(
                    '.tutor-wp-dashboard-filter-item >.tutor-js-form-select >.tutor-form-select-dropdown >.tutor-form-select-options >.tutor-form-select-option >.tutor-nowrap-ellipsis',
                  ).each(($category) => {
                    cy.wrap($category)
                      .invoke('text')
                      .then((categoryText) => {
                        if (categoryText.trim() === retrievedText.trim()) {
                          cy.wrap($category).click();
                        }
                      });
                  });
                });
            }
          });
        });
    });
  });

  it('should filter courses by a specific date', () => {
    cy.get(
      ':nth-child(3) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control',
    ).click();

    cy.get('.dropdown-years').click();
    cy.get('.dropdown-years>.dropdown-list').contains('2025').click();
    cy.get('.dropdown-months > .dropdown-label').click();
    cy.get('.dropdown-months > .dropdown-list').contains('June').click();
    cy.get('.react-datepicker__day--011').contains('11').click();
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found.')) {
        cy.log('No data available');
      } else {
        cy.wait(2000);
        cy.get('.tutor-zoom-meeting-item>td>.tutor-fs-7').each(($el) => {
          const dateText = $el.text().trim();
          expect(dateText).to.contain('June 11, 2025');
        });
      }
    });
  });
});

describe('Course Management', () => {
  it('should delete the created course', () => {
    cy.deleteCourseById(courseId);
  });
});
