import { type CourseFormData } from '@CourseBuilderServices/course';
import { faker } from '@faker-js/faker';
import endpoints from '@TutorShared/utils/endpoints';
import { backendUrls } from 'cypress/config/page-urls';
import { loginAsAdmin } from 'cypress/support/auth';

describe('Content Bank - Usage', () => {
  let collectionId: string;
  let collectionName: string;
  let courseId: string;
  let courseData: CourseFormData;
  let topicData: { title: string; summary: string };
  const addedContentNames: string[] = [];
  const quizData: { quiz_title: string; quiz_description: string } = {
    quiz_title: faker.lorem.sentence(),
    quiz_description: faker.lorem.paragraph(),
  };

  before(() => {
    cy.fixture('collection.json').then((data) => {
      collectionId = data.id;
      collectionName = data.name;
    });
    cy.fixture('course.json').then((data) => {
      courseId = data.courseId;
    });

    // @ts-ignore
    courseData = {
      post_title: 'Course for content bank testing ' + faker.string.alphanumeric(5),
      post_content: 'This is a course created for testing the content bank functionality.',
    };

    topicData = {
      title: faker.lorem.sentence(),
      summary: faker.lorem.paragraph(),
    };
  });

  beforeEach(() => {
    cy.intercept('POST', `${Cypress.env('base_url')}${backendUrls.AJAX_URL}`, (req) => {
      if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
        req.alias = 'getCourseDetails';
      }

      if (req.body.includes(endpoints.GET_COURSE_CONTENTS)) {
        req.alias = 'getCourseContents';
      }

      if (req.body.includes(endpoints.UPDATE_COURSE)) {
        req.alias = 'updateCourse';
      }

      if (req.body.includes(endpoints.SAVE_TOPIC)) {
        req.alias = 'saveTopic';
      }

      if (req.body.includes(endpoints.GET_CONTENT_BANK_COLLECTIONS)) {
        req.alias = 'getContentBankCollections';
      }

      if (req.body.includes(endpoints.GET_CONTENT_BANK_CONTENTS)) {
        req.alias = 'getContentBankContents';
      }

      if (req.body.includes(endpoints.ADD_CONTENT_BANK_CONTENT_TO_COURSE)) {
        req.alias = 'addContentBankContentToCourse';
      }

      if (req.body.includes(endpoints.SAVE_QUIZ)) {
        req.alias = 'saveQuiz';
      }

      if (req.body.includes(endpoints.DELETE_TOPIC)) {
        req.alias = 'deleteTopic';
      }
    });

    loginAsAdmin();
  });

  it('creates a new course', () => {
    cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSES}`);
    cy.get('.wp-menu-name').contains('Tutor LMS').click();
    cy.get('a.tutor-create-new-course').click();

    // Extract courseId from URL
    cy.url()
      .should('include', 'course_id=')
      .then((url) => {
        courseId = url.split('course_id=')[1].split('&')[0];
        cy.log(`Course ID: ${courseId}`);
        cy.wrap(courseId).should('be.a', 'string').and('not.be.empty');

        // Save course ID to file for other tests to use
        cy.writeFile('cypress/fixtures/course.json', { courseId, post_title: courseData.post_title });
      });

    cy.get('h6').should('have.text', 'Course Builder');
    cy.waitAfterRequest('getCourseDetails');

    cy.getByInputName('post_title').clear().type(courseData.post_title);
    cy.setTinyMceContent('[data-cy=tutor-tinymce]', courseData.post_content);

    cy.updateCourse();
    cy.get('[data-cy=tutor-toast]').should('contain', 'Course updated successfully');
  });

  it('create new topic', () => {
    cy.visit(`${backendUrls.COURSE_BUILDER}${courseId}`);
    cy.waitAfterRequest('getCourseDetails');

    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });

    cy.get('[data-cy=add-topic]').click();
    cy.saveTopic(topicData.title, topicData.summary);
  });

  it('adds content from content bank to topic', () => {
    cy.visit(`${backendUrls.COURSE_BUILDER}${courseId}`);
    cy.waitAfterRequest('getCourseDetails');

    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });

    cy.get('[data-cy=add-from-content-bank]').click();
    cy.waitAfterRequest('getContentBankCollections');

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.getByInputName('search').type(collectionName);
      cy.waitAfterRequest('getContentBankCollections');

      cy.get('table').within(() => {
        cy.get('tr').first().click();
      });
      cy.waitAfterRequest('getContentBankContents');

      cy.get('table th')
        .first()
        .within(() => {
          cy.get('input[type="checkbox"]').check({ force: true });
        });

      cy.get('table tr td label + div').each(($el) => {
        const contentName = $el.text().trim();
        addedContentNames.push(contentName);
      });

      cy.get('[data-cy=add-content-button]').click();

      cy.waitAfterRequest('addContentBankContentToCourse');
    });
    cy.waitAfterRequest('getCourseContents');

    cy.get('[data-cy=add-quiz]').click();
    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.getByInputName('quiz_title').clear().type(quizData.quiz_title);

      cy.getByInputName('quiz_description').clear().type(quizData.quiz_description);

      cy.get('[data-cy=save-quiz-title]').click();

      cy.get('[data-cy=add-question]').click();
    });

    cy.get('.tutor-portal-popover').within(() => {
      cy.get('[data-cy=add-from-content-bank]').click();
    });

    cy.get('[data-cy=tutor-modal]')
      .last()
      .within(() => {
        cy.getByInputName('search').type(collectionName);
        cy.waitAfterRequest('getContentBankCollections');

        cy.get('table').within(() => {
          cy.get('tr').first().click();
        });
        cy.waitAfterRequest('getContentBankContents');

        cy.get('table th')
          .first()
          .within(() => {
            cy.get('input[type="checkbox"]').check({ force: true });
          });

        cy.get('[data-cy=add-content-button]')
          .click()
          .then(() => {
            addedContentNames.push(quizData.quiz_title);
          });
      });

    cy.wait(250);

    cy.get('[data-cy=tutor-modal]').within(() => {
      cy.get('[data-cy=quiz-next]').click();
      cy.get('[data-cy=save-quiz]').click();
      cy.waitAfterRequest('saveQuiz');
    });
    cy.waitAfterRequest('getCourseContents');

    cy.get('#tutor-course-builder').then((body) => {
      addedContentNames.forEach((contentName) => {
        assert.isTrue(
          body.find(`:contains(${contentName})`).length > 0,
          `Expected to find ${contentName} in the course builder`,
        );
      });
    });
  });

  it('verifies content usage in content bank', () => {
    cy.visit(`${backendUrls.CONTENT_BANK}&collection_id=${collectionId}`);
    cy.waitAfterRequest('getContentBankContents');

    cy.get('[data-cy=linked-courses-popover]')
      .should('not.have.text', '0')
      .each(($el) => {
        cy.wrap($el).click();
        cy.get('.tutor-portal-popover').should('contain', courseData.post_title);
        cy.get('body').type('{esc}');
      });
  });

  it('delete the topic and then verify content bank', () => {
    cy.visit(`${backendUrls.COURSE_BUILDER}${courseId}`);
    cy.waitAfterRequest('getCourseDetails');

    cy.get('[data-cy=tutor-tracker]').within(() => {
      cy.get('button').contains('Curriculum').click();
    });

    // Duplicating for future reference
    cy.get('[data-cy=duplicate-topic]').first().click({ force: true });

    cy.get('[data-cy=delete-topic]').first().click({ force: true });
    cy.get('.tutor-portal-popover [data-cy=confirm-button]').click();
    cy.waitAfterRequest('deleteTopic');

    cy.visit(`${backendUrls.CONTENT_BANK}&collection_id=${collectionId}`);
    cy.waitAfterRequest('getContentBankContents');
    cy.get('[data-cy=linked-courses-popover]').each(($el) => {
      expect($el).to.contain('0');
    });
  });
});
