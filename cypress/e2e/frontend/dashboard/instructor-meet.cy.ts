import { type GoogleMeetMeetingFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import { Addons } from '@TutorShared/config/constants';
import endpoints from '@TutorShared/utils/endpoints';
import { frontendUrls } from '../../../config/page-urls';

let courseId: string = '';
describe('Tutor Dashboard My Courses', () => {
  // @ts-ignore
  const googleMeetData: GoogleMeetMeetingFormData = {
    meeting_name: faker.lorem.sentence(),
    meeting_summary: faker.lorem.sentences(3),
    meeting_start_time: '08:30 PM',
    meeting_end_time: '09:00 PM',
    meeting_timezone: '(GMT+6:00) Astana, Dhaka',
  };

  beforeEach(() => {
    cy.visit(`${Cypress.env('base_url')}${frontendUrls.dashboard.GOOGLE_MEET}`);
    cy.loginAsInstructor();
    cy.url().should('include', frontendUrls.dashboard.GOOGLE_MEET);

    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }

      if (req.body.includes(endpoints.SAVE_GOOGLE_MEET)) {
        req.alias = 'saveGoogleMeeting';
      }
    }).as('ajaxRequest');
  });

  //   set api and save connection
  it('should upload meet integration json and save connection', () => {
    const filePath = 'cypress/fixtures/assets/google-api.json';
    cy.get('body').then(($body) => {
      if ($body.text().includes('Drag & Drop your JSON File here')) {
        cy.get('#tutor-google-meet-credential-upload').selectFile(filePath, {
          force: true,
        });
      } else if ($body.text().includes('The app is not permitted yet!')) {
        cy.get('a').contains("Go To Google's Consent Screen").click();
      } else if ($body.text().includes('Reset Credential')) {
        cy.get(':nth-child(3) > .tutor-nav-link').contains('Set API').click();
        //   reset credentials
        cy.get('a').contains('Reset Credential').click();
        cy.get('button').contains('Yes, I’m sure').click();
      }
    });
  });

  it('should create new course and google meeting', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

    cy.get('a.tutor-create-new-course.tutor-dashboard-create-course').click();
    cy.url().should('include', '/create-course');
    cy.getByInputName('post_title').clear().type('Google meet test course');
    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Additional').click();
    });

    cy.wait(500);

    cy.isAddonEnabled(Addons.TUTOR_GOOGLE_MEET_INTEGRATION).then((isEnabled) => {
      if (isEnabled) {
        cy.get('[data-cy=create-google-meet-link]').click().wait(500);
        cy.get('.tutor-portal-popover').within(() => {
          cy.getByInputName('meeting_name').type(googleMeetData.meeting_name);
        });

        // cy.wait(500);
        cy.getByInputName('meeting_summary').click({ force: true }).type(googleMeetData.meeting_summary);
        cy.selectDate('meeting_start_date');
        cy.getSelectInput('meeting_start_time', '11:00 PM');

        cy.selectDate('meeting_end_date');
        cy.getSelectInput('meeting_end_time', '11:30 PM');

        cy.getSelectInput('meeting_timezone', googleMeetData.meeting_timezone);
        cy.get('[data-cy=save-google-meeting]').click();

        cy.waitAfterRequest('saveGoogleMeeting');

        cy.get('[data-cy=tutor-toast]').should('contain.text', 'Meeting Successfully Added');

        return;
      }
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

    cy.origin('https://workspace.google.com', () => {
      cy.url().should('include', '/calendar');
    });
  });

  it('should edit a google meeting', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_google_meet_new_meeting')) {
        req.alias = 'ajaxRequest';
      }
    }).as('ajaxRequest');

    cy.get('body').then(($body) => {
      if ($body.text().includes('No Records Found')) {
        cy.log('No data available');
      } else {
        cy.get(".tutor-google-meet-meeting-item button[action-tutor-dropdown='toggle']").eq(1).click();
        cy.get('a.tutor-dropdown-item').contains('Edit').click();

        cy.get("input[name='meeting_title']").eq(0).clear().type('Edited test google meeting');

        cy.get("textarea[name='meeting_summary'").eq(0).clear().type('Edited google meeting summary', { force: true });

        cy.get(
          '.tutor-gmi-meeting-time > :nth-child(1) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control',
        )
          .eq(0)
          .click();
        cy.get('.dropdown-years > .dropdown-label').click();
        cy.get('.dropdown-container.dropdown-years .dropdown-list li').contains('2025').click();
        cy.get('.dropdown-container.dropdown-months .dropdown-label').click();
        cy.get('.dropdown-container.dropdown-months .dropdown-list li').contains('June').click();
        cy.get('.react-datepicker__day').contains('11').click();

        cy.get("input[name='meeting_start_time']").eq(0).clear({ force: true }).type('09:30 PM');

        cy.get('input[name="meeting_end_time"]').eq(0).clear().type('10:00 PM');
        cy.get("input[name='meeting_end_date']").eq(0).click();
        cy.get('.dropdown-years > .dropdown-label').click();
        cy.get('.dropdown-container.dropdown-years .dropdown-list li').contains('2025').click();
        cy.get('.dropdown-container.dropdown-months .dropdown-label').click();
        cy.get('.dropdown-container.dropdown-months .dropdown-list li').contains('June').click();
        cy.get('.react-datepicker__day').contains('11').click();

        cy.get('input[name="meeting_end_time"]').eq(0).clear().type('10:00 PM');

        cy.get('.tutor-col-md-8 > .tutor-js-form-select').eq(0).click();
        cy.get(
          '.tutor-col-md-8 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(108) > .tutor-nowrap-ellipsis',
        )
          .eq(0)
          .click();

        cy.get("input[name='meeting_attendees_enroll_students']").check({
          force: true,
        });
        cy.get('button').contains('Update Meeting').click();

        cy.wait('@ajaxRequest').then((interception) => {
          expect(interception.response?.statusCode).to.equal(200);
        });
      }
    });
  });

  it('should delete a google meeting', () => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_google_meet_delete')) {
        req.alias = 'ajaxRequest';
      }
    }).as('ajaxRequest');
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Records Found') || $body.text().includes('No records found')) {
        cy.log('No data available');
      } else {
        cy.get(".tutor-google-meet-meeting-item button[action-tutor-dropdown='toggle']").eq(3).click();
        cy.get('a.tutor-dropdown-item').contains('Delete').click({ force: true });
        cy.get('#tutor-common-confirmation-form > .tutor-d-flex > .tutor-btn-primary')
          .contains('Yes, I’m sure')
          .click();
      }
    });
    cy.wait('@ajaxRequest').then((interception) => {
      expect(interception.response?.statusCode).to.equal(200);
    });
  });

  // settings
  it('should save settings', () => {
    cy.get(':nth-child(4) > .tutor-nav-link').contains('Settings').click();
    cy.get('.tutor-form-control.tutor-form-select.tutor-js-form-select').click();
    cy.get('.tutor-form-select-option').contains('(GMT+6:00) Astana, Dhaka').click();
    cy.get("input[value='30']").check().should('be.checked');
    cy.get("input[value='confirmed']").check().should('be.checked');
    cy.get("input[value='all']").check().should('be.checked');
    cy.get("input[value='opaque']").check().should('be.checked');
    cy.get("input[value='default']").check().should('be.checked');
  });

  // // help
  it('Should make corresponding elements visible when accordion is clicked', () => {
    cy.get(':nth-child(5) > .tutor-nav-link').contains('Help').click();
    cy.get('.tutor-accordion-item-header.tutor-card.tutor-mb-16').each(($accordion, index) => {
      cy.wrap($accordion).click();
      cy.get(`.tutor-fs-7.tutor-color-secondary`).eq(index).should('be.visible');
    });
  });

  it('should be able to search any meeting', () => {
    const searchInputSelector = '#tutor-backend-filter-search';
    const searchQuery = 'Google meet test';
    const courseLinkSelector = '.tutor-google-meet-meeting-item>td:nth-child(2)>div:first-child';
    const submitButtonSelector = '';
    const submitWithButton = false;
    cy.search(searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton);
  });

  it('should filter meetings', () => {
    cy.get(':nth-child(2) > .tutor-js-form-select').click();
    cy.get('body').then(($body) => {
      if ($body.text().includes('No Records Found') || $body.text().includes('No records found')) {
        cy.log('No data available');
      } else {
        cy.get('.tutor-form-select-options')
          .eq(1)
          .then(() => {
            cy.get('.tutor-form-select-option').then(() => {
              cy.get('.tutor-form-select-options>div:nth-child(2)').eq(0).click();
            });
          });
      }
    });
  });

  it('Should filter courses by a specific date', () => {
    cy.get(
      '.tutor-wp-dashboard-filter-items > :nth-child(3) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control',
    ).click();

    cy.get('.dropdown-years').click();
    cy.get('.dropdown-years>.dropdown-list').contains('2025').click();
    cy.get('.dropdown-months > .dropdown-label').click();
    cy.get('.dropdown-months > .dropdown-list').contains('June').click();
    cy.get('.react-datepicker__day--011').contains('11').click();

    cy.get('body').then(($body) => {
      if ($body.text().includes('No Data Found from your Search/Filter') || $body.text().includes('No records found')) {
        cy.log('No data available');
      } else {
        cy.wait(2000);
        cy.get('.tutor-google-meet-meeting-item .tutor-fs-7 > span').each(($el) => {
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
