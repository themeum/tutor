import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Additional Info', () => {
  let courseId: string;
  let courseData: CourseFormData;

  before(() => {
    // @ts-ignore
    courseData = {
      course_benefits: faker.lorem.lines(2),
      course_target_audience: faker.lorem.lines(2),
      course_duration_hours: faker.number.int({ min: 1, max: 10 }),
      course_duration_minutes: faker.number.int({ min: 1, max: 59 }),
      course_material_includes: faker.lorem.lines(2),
      course_requirements: faker.lorem.lines(2),
    };

    cy.readFile('cypress/fixtures/course.json').then((fixture) => {
      courseId = fixture.courseId;
    });
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.UPDATE_COURSE)) {
        req.alias = 'updateCourse';
      }
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }
      if (req.body.includes('query-attachments')) {
        req.alias = 'queryAttachments';
      }
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    cy.wait(500);
    cy.waitAfterRequest('getCourseDetails');
    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Additional').click();
    });
  });

  it('adds course benefits and target audience', () => {
    cy.getByInputName('course_benefits').clear().type(courseData.course_benefits);
    cy.getByInputName('course_target_audience').clear().type(courseData.course_target_audience);
    cy.updateCourse();
  });

  it('sets course duration', () => {
    cy.getByInputName('course_duration_hours').clear().type(String(courseData.course_duration_hours));
    cy.getByInputName('course_duration_minutes').clear().type(String(courseData.course_duration_minutes));
    cy.updateCourse();
  });

  it('adds materials and requirements', () => {
    cy.getByInputName('course_material_includes').clear().type(courseData.course_material_includes);
    cy.getByInputName('course_requirements').clear().type(courseData.course_requirements);
    cy.updateCourse();
  });

  it('adds course attachments', () => {
    cy.getWPMedia('Attachments', 'Upload Attachment', '');
    cy.updateCourse();
  });
});
