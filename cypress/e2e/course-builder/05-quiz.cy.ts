import { type QuizForm } from '@CourseBuilderServices/quiz';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from '../../support/auth';

describe('Course Builder - Quiz', () => {
  let courseId: string;
  let topicData: { title: string; summary: string };
  let quizData: QuizForm;

  before(() => {
    // @ts-ignore
    topicData = {
      title: faker.lorem.lines(1),
      summary: faker.lorem.lines(2),
    };

    // @ts-ignore
    quizData = {
      quiz_title: faker.lorem.lines(1),
      quiz_description: faker.lorem.lines(3),
      // @ts-ignore
      quiz_option: {
        time_limit: {
          time_value: faker.number.int({ min: 1, max: 10 }),
          time_type: 'hours',
        },
        attempts_allowed: faker.number.int({ min: 1, max: 10 }),
        passing_grade: faker.number.int({ min: 50, max: 100 }),
        max_questions_for_answer: faker.number.int({ min: 1, max: 10 }),
      },
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
      if (req.body.includes(endpoints.SAVE_QUIZ)) {
        req.alias = 'saveQuiz';
      }
      if (req.body.includes(endpoints.DELETE_QUIZ)) {
        req.alias = 'deleteQuiz';
      }
      if (req.body.includes(endpoints.DUPLICATE_CONTENT)) {
        req.alias = 'duplicateContent';
      }
    });

    // Use the centralized login function
    loginAsAdmin();

    cy.visit(`/wp-admin/admin.php?page=create-course&course_id=${courseId}`);
    cy.wait(500);
    cy.waitAfterRequest('getCourseDetails');
    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });
    cy.wait(500);
    cy.waitAfterRequest('getCourseContents');
  });

  it('creates a topic for quiz', () => {
    cy.doesElementExist('[data-cy=edit-topic]').then((exists) => {
      if (!exists) {
        cy.get('[data-cy=add-topic]').click();
        cy.saveTopic(topicData.title, topicData.summary);
      }
    });
  });

  it('creates a quiz with settings', () => {
    cy.get('[data-cy=add-quiz]').first().click();
    cy.saveQuiz(quizData);
    cy.get('body').should('contain', quizData.quiz_title);
  });

  it('duplicates and deletes a quiz', () => {
    // Duplicate quiz
    cy.get('[data-cy=duplicate-tutor_quiz]').first().click();
    cy.waitAfterRequest('duplicateContent');
    cy.waitAfterRequest('getCourseContents');
    cy.get('body').should('contain', `${quizData.quiz_title} (copy)`);

    // Delete quiz copy
    cy.get('[data-cy=delete-tutor_quiz]').eq(1).click();
    cy.get('.tutor-portal-popover [data-cy=confirm-button]').click();
    cy.waitAfterRequest('deleteQuiz');
    cy.waitAfterRequest('getCourseContents');
    cy.get('body').should('not.contain', `${quizData.quiz_title} (copy)`);
  });
});
