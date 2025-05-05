import { type AssignmentForm } from '@CourseBuilderComponents/modals/AssignmentModal';
import { type LessonForm } from '@CourseBuilderComponents/modals/LessonModal';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Curriculum', () => {
  let courseId: string;
  let topicData: { title: string; summary: string };
  let lessonData: LessonForm;
  let assignmentData: AssignmentForm;

  before(() => {
    // @ts-ignore
    topicData = {
      title: faker.lorem.lines(1),
      summary: faker.lorem.lines(2),
    };

    // @ts-ignore
    lessonData = {
      title: faker.lorem.lines(1),
      description: faker.lorem.lines(3),
      duration: {
        hour: faker.number.int({ min: 1, max: 10 }),
        minute: faker.number.int({ min: 1, max: 59 }),
        second: faker.number.int({ min: 1, max: 59 }),
      },
    };

    // @ts-ignore
    assignmentData = {
      title: faker.lorem.lines(1),
      summary: faker.lorem.lines(3),
      time_duration: {
        value: String(faker.number.int({ min: 1, max: 10 })),
        time: 'days',
      },
      total_mark: faker.number.int({ min: 50, max: 100 }),
      pass_mark: faker.number.int({ min: 1, max: 49 }),
      upload_files_limit: faker.number.int({ min: 1, max: 10 }),
      upload_file_size_limit: faker.number.int({ min: 1, max: 10 }),
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
      if (req.body.includes(endpoints.GET_COURSE_CONTENTS)) {
        req.alias = 'getCourseContents';
      }
      if (req.body.includes(endpoints.SAVE_TOPIC)) {
        req.alias = 'saveTopic';
      }
      if (req.body.includes(endpoints.DELETE_TOPIC)) {
        req.alias = 'deleteTopic';
      }
      if (req.body.includes(endpoints.SAVE_LESSON)) {
        req.alias = 'saveLesson';
      }
      if (req.body.includes(endpoints.DELETE_TOPIC_CONTENT)) {
        req.alias = 'deleteContent';
      }
      if (req.body.includes(endpoints.SAVE_ASSIGNMENT)) {
        req.alias = 'saveAssignment';
      }
      if (req.body.includes(endpoints.DUPLICATE_CONTENT)) {
        req.alias = 'duplicateContent';
      }
      if (req.body.includes('query-attachments')) {
        req.alias = 'queryAttachments';
      }
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    cy.wait(1000);
    cy.waitAfterRequest('getCourseDetails');
    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });
    cy.wait(1000);
    cy.waitAfterRequest('getCourseContents');
  });

  it('adds a topic', () => {
    cy.get('[data-cy=add-topic]').click();
    cy.saveTopic(topicData.title, topicData.summary);
  });

  it('adds a lesson to a topic', () => {
    cy.get('[data-cy=add-lesson]').first().click();
    cy.saveLesson(lessonData);
    cy.get('body').should('contain', lessonData.title);
  });

  it('adds an assignment to a topic', () => {
    cy.get('[data-cy=add-assignment]').first().click();
    cy.saveAssignment(assignmentData);
    cy.get('body').should('contain', assignmentData.title);
  });

  it('duplicates and deletes curriculum content', () => {
    // Duplicate lesson
    cy.get('[data-cy=duplicate-lesson]').eq(0).click();
    cy.waitAfterRequest('duplicateContent');
    cy.waitAfterRequest('getCourseContents');
    cy.get('body').should('contain', `${lessonData.title} (copy)`);

    // Duplicate assignment
    cy.get('[data-cy=duplicate-tutor_assignments]').eq(0).click();
    cy.waitAfterRequest('duplicateContent');
    cy.waitAfterRequest('getCourseContents');
    cy.get('body').should('contain', `${assignmentData.title} (copy)`);

    // Delete lesson copy
    cy.get('[data-cy=delete-lesson]').eq(1).click();
    cy.get('.tutor-portal-popover [data-cy=confirm-button]').click();
    cy.waitAfterRequest('deleteContent');
    cy.waitAfterRequest('getCourseContents');

    // Delete assignment copy
    cy.get('[data-cy=delete-tutor_assignments]').eq(1).click();
    cy.get('.tutor-portal-popover [data-cy=confirm-button]').click();
    cy.waitAfterRequest('deleteContent');
    cy.waitAfterRequest('getCourseContents');
  });

  it('duplicates and deletes topics', () => {
    // Duplicate topic
    cy.get('[data-cy=duplicate-topic]').eq(0).click();
    cy.waitAfterRequest('duplicateContent');
    cy.waitAfterRequest('getCourseContents');
    cy.get('body').should('contain', `${topicData.title} (copy)`);

    // Delete topic copy
    cy.get('[data-cy=delete-topic]').eq(1).click();
    cy.get('.tutor-portal-popover [data-cy=confirm-button]').click();
    cy.waitAfterRequest('deleteTopic');
    cy.get('body').should('not.contain', `${topicData.title} (copy)`);
  });
});
