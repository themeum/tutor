import { type Addon } from '@TutorShared/utils/util';
import { type Interception } from 'cypress/types/net-stubbing';

/* eslint-disable @typescript-eslint/no-namespace */
export {};

declare global {
  namespace Cypress {
    interface Chainable {
      getByInputName(dataTestAttribute: string): Chainable<JQuery<HTMLElement>>;
      setTinyMceContent(selector: string, content: string): Chainable<JQuery<HTMLElement>>;
      loginAsAdmin(): Chainable<JQuery<HTMLElement>>;
      loginAsInstructor(): Chainable<JQuery<HTMLElement>>;
      loginAsStudent(): Chainable<JQuery<HTMLElement>>;
      performBulkAction(option: string): Chainable<JQuery<HTMLElement>>;
      performBulkActionOnSelectedElement(option: string): Chainable<JQuery<HTMLElement>>;
      filterByCategory(): Chainable<JQuery<HTMLElement>>;
      checkSorting(order: string, formSelector: string, itemSelector: string): Chainable<JQuery<HTMLElement>>;
      filterElements(
        filterFormSelector: string,
        dropdownSelector: string,
        dropdownOptionSelector: string,
        dropdownTextSelector: string,
        elementTitleSelector: string,
      ): Chainable<JQuery<HTMLElement>>;
      filterElementsByDate(filterFormSelector: string, elementDateSelector: string): Chainable<JQuery<HTMLElement>>;
      search(
        searchInputSelector: string,
        searchQuery: string,
        courseLinkSelector: string,
        submitButtonSelector: string,
        submitWithButton: boolean,
      ): Chainable<JQuery<HTMLElement>>;
      selectPageFromDropdownAndSaveChanges(
        commonSelector: string,
        saveButtonSelector: string,
        apiFieldOption: string,
        dataValueAttribute: string,
      ): Chainable<JQuery<HTMLElement>>;
      toggle(inputName: string, fieldId: string): Chainable<JQuery<HTMLElement>>;
      isEnrolled(): Chainable<JQuery<HTMLElement>>;
      handleCourseStart(): Chainable<JQuery<HTMLElement>>;
      completeLesson(): Chainable<JQuery<HTMLElement>>;
      handleNextButton(): Chainable<JQuery<HTMLElement>>;
      handleAssignment(isLastItem: boolean): Chainable<JQuery<HTMLElement>>;
      handleQuiz(): Chainable<JQuery<HTMLElement>>;
      handleMeetingLesson(isLastItem: boolean): Chainable<JQuery<HTMLElement>>;
      handleZoomLesson(isLastItem: boolean): Chainable<JQuery<HTMLElement>>;
      completeCourse(): Chainable<JQuery<HTMLElement>>;
      submitCourseReview(): Chainable<JQuery<HTMLElement>>;
      viewCertificate(): Chainable<JQuery<HTMLElement>>;
      login: () => Chainable<void>;
      setWPeditorContent: (content: string) => Chainable<void>;
      getSelectInput: (name: string, value: string) => Chainable<void>;
      getWPMedia: (label: string, buttonText: string, replaceButtonText: string) => Chainable<void>;
      selectWPMedia: () => Chainable<void>;
      isAddonEnabled: (addon: Addon) => Chainable<boolean>;
      doesElementExist: (selector: string) => Chainable<boolean>;
      updateCourse: () => Chainable<void>;
      selectDate: (selector: string) => Chainable<void>;

      // Course builder commands
      saveTopic(title: string, summary?: string): Chainable<void>;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      saveLesson(lessonData: any): Chainable<void>;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      saveAssignment(assignmentData: any): Chainable<void>;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      saveQuiz(quizData: any): Chainable<void>;
      deleteTopic(index?: number): Chainable<void>;
      deleteContent(type: string, index?: number): Chainable<void>;
      duplicateContent(type: string, index?: number): Chainable<void>;
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      waitAfterRequest(alias: string, additionalWaitMs?: number): Chainable<Interception<any, any>>;
      monetizedBy(): Chainable<string>;
    }
  }
}

/**
 * Wait for an API request to complete and then optionally wait additional time for UI to update
 */
Cypress.Commands.add('waitAfterRequest', (alias: string, additionalWaitMs = 500) => {
  return cy.wait(`@${alias}`).then((interception) => {
    // Check for successful status code
    if (interception.response) {
      expect(interception.response.statusCode).to.be.oneOf([200, 201]);
    }

    // Add small wait for UI to update if needed
    if (additionalWaitMs > 0) {
      cy.wait(additionalWaitMs);
    }

    return cy.wrap(interception);
  });
});

Cypress.Commands.add('getByInputName', (selector) => {
  cy.get(`input[name="${selector}"], textarea[name="${selector}"]`).then(($input) => {
    if ($input.length === 1) {
      cy.wrap($input);
    }
    if ($input.attr('type') === 'radio') {
      cy.wrap($input).parent();
    } else {
      cy.wrap($input);
    }
  });
});

Cypress.Commands.add('setTinyMceContent', (selector, content) => {
  // wait for tinymce to be loaded
  cy.window().should('have.property', 'tinymce');

  // wait for the editor to be rendered
  cy.get(selector).as('editorTextarea').should('exist');

  // set the content for the editor by its dynamic id
  cy.window().then((win) =>
    cy.get('@editorTextarea').then((element) => {
      const editorId = element.attr('id');
      const editorInstance = win.tinymce.EditorManager.get().filter(
        (editor: { id: string }) => editor.id === editorId,
      )[0];
      editorInstance.setContent('');
      cy.get(`#${editorId}_ifr`).then(($iframe) => {
        const doc = $iframe.contents();
        const body = doc.find('body > p');
        cy.wrap(body).scrollIntoView().type(content);
      });
    }),
  );
});

Cypress.Commands.add('loginAsAdmin', () => {
  cy.wait(500);
  cy.getByInputName('log').clear().type(Cypress.env('admin_username'));
  cy.getByInputName('pwd').clear().type(Cypress.env('admin_password'));
  cy.get('form#loginform').submit();
});

Cypress.Commands.add('loginAsInstructor', () => {
  cy.getByInputName('log').clear().type(Cypress.env('instructor_username'));
  cy.getByInputName('pwd').clear().type(Cypress.env('instructor_password'));
  cy.get('#tutor-login-form button').contains('Sign In').click();
});

Cypress.Commands.add('loginAsStudent', () => {
  cy.getByInputName('log').clear().type(Cypress.env('student_username'));
  cy.getByInputName('pwd').clear().type(Cypress.env('student_password'));
  cy.get('#tutor-login-form button').contains('Sign In').click();
});

Cypress.Commands.add('performBulkActionOnSelectedElement', (option) => {
  cy.get('body').then(($body) => {
    if (
      $body.text().includes('No Data Available in this Section') ||
      $body.text().includes('No Data Found from your Search/Filter') ||
      $body.text().includes('No request found') ||
      $body.text().includes('No records found') ||
      $body.text().includes('No Records Found')
    ) {
      cy.log('No data available');
    } else {
      cy.getByInputName('tutor-bulk-checkbox-all').then(($checkboxes) => {
        const checkboxesArray = Cypress._.toArray($checkboxes);
        const randomIndex = Cypress._.random(0, checkboxesArray.length - 1);
        console.log(randomIndex);
        cy.wrap(checkboxesArray[randomIndex]).as('randomCheckbox');
        cy.get('@randomCheckbox').check();
        // cy.get(':nth-child(1) > :nth-child(1) > .td-checkbox > .tutor-form-check-input').check();
        cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
        cy.get(`span[tutor-dropdown-item][data-key=${option}]`).click();

        cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
        cy.get('#tutor-confirm-bulk-action').click();
        cy.reload();
        cy.get('@randomCheckbox')
          .invoke('attr', 'value')
          .then((id) => {
            if (option === 'trash') {
              cy.get(`select.tutor-table-row-status-update[data-id="${id}"]`).should('not.exist');
            } else {
              cy.get(`select.tutor-table-row-status-update[data-id="${id}"]`)
                .invoke('attr', 'data-status')
                .then((status) => {
                  console.log(status);
                  expect(status).to.include(option);
                });
            }
          });
      });
    }
  });
});
// perform publish,pending,draft,trash on all courses
Cypress.Commands.add('performBulkAction', (option) => {
  cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`).as('ajaxRequest');

  cy.get('body').then(($body) => {
    if (
      $body.text().includes('No Data Found from your Search/Filter') ||
      $body.text().includes('No request found') ||
      $body.text().includes('No Data Available in this Section') ||
      $body.text().includes('No records found') ||
      $body.text().includes('No Records Found')
    ) {
      cy.log('No data available');
    } else {
      cy.get('#tutor-bulk-checkbox-all').click();
      cy.get('.tutor-mr-12 > .tutor-js-form-select').click();

      cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
        .invoke('text')
        .then((text) => {
          const expectedValue = text.trim();
          cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`).click();
          cy.get('#tutor-admin-bulk-action-btn').contains('Apply').click();
          cy.get('#tutor-confirm-bulk-action').contains('Yes, I’m sure').click();
          cy.reload();

          if (option === 'trash') {
            cy.get('select.tutor-table-row-status-update')
              .invoke('val')
              .then((selectedValue) => {
                expect(selectedValue).not.to.include(expectedValue.toLowerCase());
              });
          } else {
            cy.get('select.tutor-table-row-status-update')
              .invoke('val')
              .then((selectedValue) => {
                expect(selectedValue).to.include(expectedValue.toLowerCase());
              });
          }
        });
    }
  });
});

Cypress.Commands.add('checkSorting', (order, formSelector, itemSelector) => {
  function checkSorting() {
    cy.get('body').then(($body) => {
      if (
        $body.text().includes('No Data Found from your Search/Filter') ||
        $body.text().includes('No request found') ||
        $body.text().includes('No Data Available in this Section') ||
        $body.text().includes('No records found') ||
        $body.text().includes('No Records Found')
      ) {
        cy.log('No data available');
      } else {
        cy.get(itemSelector).then(($items) => {
          cy.get(formSelector).select(order);
          const itemTexts = $items
            .map((index, item) => item.innerText.trim())
            .get()
            .filter((text) => text);
          const sortedItems = order === 'ASC' ? itemTexts.sort() : itemTexts.sort().reverse();
          expect(itemTexts).to.deep.equal(sortedItems);
        });
      }
    });
  }
  checkSorting();
});

Cypress.Commands.add(
  'filterElements',
  (filterFormSelector, dropdownSelector, dropdownOptionSelector, dropdownTextSelector, elementTitleSelector) => {
    cy.get(filterFormSelector).click();
    cy.get('body').then(($body) => {
      if (
        $body.text().includes('No Data Found from your Search/Filter') ||
        $body.text().includes('No request found') ||
        $body.text().includes('No Data Available in this Section') ||
        $body.text().includes('No records found') ||
        $body.text().includes('No Records Found')
      ) {
        cy.log('No data available');
      } else {
        cy.get(dropdownSelector)
          .eq(1)
          .then(() => {
            cy.get(dropdownOptionSelector).then(($options) => {
              const randomIndex = Cypress._.random(1, $options.length - 3);
              const $randomOption = Cypress.$($options[randomIndex]);
              const selectedOptionText = $randomOption.text().trim();
              cy.wrap($randomOption).find(dropdownTextSelector).click();
              console.log(`drop `, selectedOptionText);
              cy.get('body').then(($body) => {
                if (
                  $body.text().includes('No Data Found from your Search/Filter') ||
                  $body.text().includes('No request found') ||
                  $body.text().includes('No Data Available in this Section') ||
                  $body.text().includes('No records found') ||
                  $body.text().includes('No Records Found')
                ) {
                  cy.log('No data available');
                } else {
                  cy.wait(500);
                  cy.get(elementTitleSelector).each(($element) => {
                    const elementText = $element.text().trim();
                    console.log('el ', elementText);
                    expect(elementText).to.contain(selectedOptionText);
                  });
                }
              });
            });
          });
      }
    });
  },
);

Cypress.Commands.add('filterElementsByDate', (filterFormSelector, elementDateSelector) => {
  cy.get(filterFormSelector).click();
  cy.get('.dropdown-years').click();
  cy.get('.dropdown-years>.dropdown-list').contains('2024').click();
  cy.get('.dropdown-months > .dropdown-label').click();
  cy.get('.dropdown-months > .dropdown-list>li').contains('June').click();
  cy.get('.react-datepicker__day--011').contains('11').click();
  cy.get('body').then(($body) => {
    if (
      $body.text().includes('No Data Found from your Search/Filter') ||
      $body.text().includes('No request found') ||
      $body.text().includes('No Data Available in this Section') ||
      $body.text().includes('No records found') ||
      $body.text().includes('No Records Found')
    ) {
      cy.log('No data available');
    } else {
      cy.wait(2000);
      cy.get(elementDateSelector).each(($el) => {
        const dateText = $el.text().trim();
        expect(dateText).to.contain('June 11, 2024');
      });
    }
  });
});

Cypress.Commands.add('filterByCategory', () => {
  cy.get('.tutor-js-form-select').eq(1).click();
  cy.get('.tutor-form-select-options')
    .eq(1)
    .then(() => {
      cy.get('.tutor-form-select-option')
        .then(($options) => {
          const randomIndex = Cypress._.random(6, $options.length - 3);
          const $randomOption = Cypress.$($options[randomIndex]);
          cy.wrap($randomOption).find('span[tutor-dropdown-item]').click();
        })
        .then(() => {
          cy.get('body').then(($body) => {
            if (
              $body.text().includes('No Data Found from your Search/Filter') ||
              $body.text().includes('No request found') ||
              $body.text().includes('No Data Available in this Section') ||
              $body.text().includes('No records found') ||
              $body.text().includes('No Records Found')
            ) {
              cy.log('No data available');
            } else {
              cy.get('span.tutor-form-select-label[tutor-dropdown-label]')
                .eq(1)
                .invoke('text')
                .then((retrievedText) => {
                  cy.get('.tutor-fw-normal.tutor-fs-7').each(($category) => {
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

Cypress.Commands.add(
  'search',
  (searchInputSelector, searchQuery, courseLinkSelector, submitButtonSelector, submitWithButton = false) => {
    cy.get(searchInputSelector).type(`${searchQuery}{enter}`);
    if (submitWithButton) {
      cy.get(searchInputSelector).clear();
      cy.get(submitButtonSelector).click();
    }
    cy.get('body').then(($body) => {
      if (
        $body.text().includes('No Data Found from your Search/Filter') ||
        $body.text().includes('No request found') ||
        $body.text().includes('No Data Available in this Section') ||
        $body.text().includes('No records found') ||
        $body.text().includes('No Records Found')
      ) {
        cy.log('No data available');
      } else {
        let count = 0;
        cy.get(courseLinkSelector)
          .eq(0)
          .each(($link) => {
            const courseName = $link.text().trim();
            if (courseName.includes(searchQuery)) {
              count++;
              expect(courseName.toLowerCase()).to.include(searchQuery.toLowerCase());
              cy.get(courseLinkSelector)
                .eq(0)
                .its('length')
                .then((totalVisibleElements) => {
                  expect(count).to.eq(totalVisibleElements);
                });
            }
          });
      }
    });
  },
);

Cypress.Commands.add(
  'selectPageFromDropdownAndSaveChanges',
  (commonSelector, saveButtonSelector, apiFieldOption, dataValueAttribute) => {
    cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
      if (req.body.includes('tutor_option_save')) {
        req.alias = 'ajaxRequest';
      }
    });
    cy.get(`${commonSelector} > .tutor-option-field-input > .tutor-js-form-select`).click();
    cy.get(
      `${commonSelector} > .tutor-option-field-input > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > .tutor-form-select-option`,
    ).then((options) => {
      const randomIndex = Math.floor(Math.random() * options.length);
      cy.wrap(options[randomIndex]).click();
      cy.get(`${commonSelector} > .tutor-option-field-input > .tutor-js-form-select > span[tutor-dropdown-label]`)
        .invoke('attr', dataValueAttribute)
        .then((dataValue) => {
          cy.contains(`${saveButtonSelector}`).click({ force: true });

          cy.wait('@ajaxRequest').then((interception) => {
            if (interception.response) {
              expect(interception.response?.body.success).to.equal(true);
            } else {
              throw new Error('Response is undefined');
            }

            const requestBody = interception.request.body;
            const params = new URLSearchParams(requestBody);
            const tutorOptionId = params.get(`${apiFieldOption}`);
            expect(tutorOptionId).to.equal(dataValue);
          });
        });
    });
  },
);

Cypress.Commands.add('toggle', (inputName, fieldId) => {
  cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
    if (req.body.includes('tutor_option_save')) {
      req.alias = 'ajaxRequest';
    }
  });
  cy.get(`${fieldId} > .tutor-option-field-input > .tutor-form-toggle > .tutor-form-toggle-control`).click();

  cy.getByInputName(`${inputName}`)
    .invoke('attr', 'value')
    .then((dataValue) => {
      cy.contains('Save Changes').click({ force: true });

      cy.wait('@ajaxRequest').then((interception) => {
        if (interception.response) {
          expect(interception.response?.body.success).to.equal(true);
        } else {
          throw new Error('Response is undefined');
        }

        const requestBody = interception.request.body;
        const params = new URLSearchParams(requestBody);
        const tutorOptionId = params.get(`${inputName}`);

        expect(tutorOptionId).to.equal(dataValue);
      });
    });
});

Cypress.Commands.add('isEnrolled', () => {
  cy.get('body').then(($body) => {
    if (
      $body.text().includes('Add to cart') ||
      $body.text().includes('Add to Cart') ||
      $body.text().includes('View Cart')
    ) {
      return false;
    }
    return true;
  });
});

Cypress.Commands.add('handleCourseStart', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Retake This Course')) {
      cy.get('button').contains('Retake This Course').click();
      cy.get('button').contains('Reset Data').click();
      cy.wait('@ajaxRequest').then((interception) => {
        if (interception.response) {
          expect(interception.response?.body.success).to.equal(true);
        } else {
          throw new Error('Response is undefined');
        }
      });
    } else if ($body.text().includes('Continue Learning')) {
      cy.get('a').contains('Continue Learning').click();
    } else if ($body.text().includes('Start Learning')) {
      cy.get('a').contains('Start Learning').click();
    }
  });
});

Cypress.Commands.add('completeLesson', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Mark as Complete')) {
      cy.get('button').contains('Mark as Complete').click();
      cy.wait(1000);
      cy.get('body').should('not.contain', 'Mark as Complete');
    }
  });
});

Cypress.Commands.add('handleNextButton', () => {
  cy.get('a')
    .contains('Next')
    .parent()
    .then(($element) => {
      if ($element.attr('disabled')) {
        cy.get('.tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times').parent().click();
      } else {
        cy.wrap($element).click();
      }
    });
});

Cypress.Commands.add('handleAssignment', (isLastItem) => {
  cy.get('body').then(($body) => {
    const bodyText = $body.text();
    if (
      bodyText.includes('You have missed the submission deadline. Please contact the instructor for more information.')
    ) {
      cy.get('.tutor-btn-ghost').contains('Skip To Next').click();
      return;
    }

    if (bodyText.includes('Start Assignment Submit')) {
      cy.get('#tutor_assignment_start_btn').click();
      cy.wait('@ajaxRequest').then((interception) => {
        if (interception.response) {
          expect(interception.response?.statusCode).to.equal(200);
        } else {
          throw new Error('Response is undefined');
        }
      });
      cy.url().should('include', 'assignments');
      cy.setTinyMceContent('.wp-editor-area', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
      cy.get('#tutor_assignment_submit_btn').click();
      cy.get('body').should('contain.text', 'Your Assignment');
    }

    if (bodyText.includes('Submit Assignment')) {
      cy.setTinyMceContent('.wp-editor-area', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
      cy.get('#tutor_assignment_submit_btn').click();
      cy.get('body').should('contain.text', 'Your Assignment');
    }
    cy.get('body').then(($body) => {
      if ($body.text().includes('Continue Lesson')) {
        cy.get('a').contains('Continue Lesson').click();
      } else if (isLastItem) {
        cy.get('.tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times').parent().click();
      }
    });
  });
});

Cypress.Commands.add('handleQuiz', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Start Quiz')) {
      cy.get('button[name=start_quiz_btn]').click();
    }
    if (
      $body.text().includes('Start Quiz') ||
      $body.text().includes('Submit & Next') ||
      $body.text().includes('Submit Quiz')
    ) {
      cy.get('.quiz-attempt-single-question').each(($question, $index) => {
        if ($question.find('textarea').length) {
          cy.wrap($question).find('textarea').type('Sample answer for text area question.');
        }
        if ($question.find('input[type=text]').length) {
          cy.wrap($question)
            .find('input[type=text]')
            .each(($input) => {
              cy.wrap($input).type('Sample text input answer.');
            });
        }
        if ($question.find('#tutor-quiz-image-multiple-choice').length) {
          cy.wrap($question).find('.tutor-quiz-question-item').eq(0).find('input').click();
        }
        cy.get('button.tutor-quiz-next-btn-all').eq($index).click();
      });
    }
    cy.get('a')
      .contains('Next')
      .parent()
      .then(($element) => {
        if ($element.attr('disabled')) {
          cy.get('.tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times').parent().click();
        } else {
          cy.wrap($element).click();
        }
      });
  });
});

Cypress.Commands.add('handleMeetingLesson', (isLastItem) => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Mark as Complete')) {
      cy.get('button').contains('Mark as Complete').click();
    } else {
      if (isLastItem) {
        cy.get('.tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times').parent().click();
      } else {
        cy.get('.tutor-course-topic-item').children('a').first().click({ force: true });
      }
    }
  });
});

Cypress.Commands.add('handleZoomLesson', (isLastItem) => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Mark as Complete')) {
      cy.get('button').contains('Mark as Complete').click();
    } else {
      if (isLastItem) {
        cy.get('.tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times').parent().click();
      } else {
        cy.get('.tutor-course-topic-item').children('a').click({ force: true });
      }
    }
  });
});

Cypress.Commands.add('completeCourse', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('Complete Course')) {
      cy.get('button').contains('Complete Course').click();
    }
  });
});

Cypress.Commands.add('submitCourseReview', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('How would you rate this course?')) {
      cy.get('.tutor-modal-content .tutor-icon-star-line').eq(4).click();
      cy.get('.tutor-modal-content textarea[name=review]').type(
        "Just completed a course on TutorLMS, and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze. The interactive quizzes and discussions keep you engaged, and the user-friendly interface enhances the overall experience. The flexibility to learn at your own pace is a game-changer for busy professionals.",
      );
      cy.get('.tutor-d-flex > .tutor_submit_review_btn').click();
      cy.wait('@ajaxRequest').then((interception) => {
        if (interception.response) {
          expect(interception.response?.body.success).to.equal(true);
        } else {
          throw new Error('Response is undefined');
        }
      });
      cy.wait(5000);
    }
  });
});

Cypress.Commands.add('viewCertificate', () => {
  cy.get('body').then(($body) => {
    if ($body.text().includes('View Certificate')) {
      cy.get('a').contains('View Certificate').click();
      cy.url().should('include', 'tutor-certificate');
      cy.wait(5000);
    }
  });
});

Cypress.Commands.add('getSelectInput', (name: string, value: string, eq?: number) => {
  cy.get(`input[name*="${name}"]`)
    .eq(eq || 0)
    .scrollIntoView()
    .should('be.visible')
    .click();
  cy.wait(250);
  cy.get('.tutor-portal-popover')
    .last()
    .should('be.visible')
    .within(() => {
      cy.get('li').contains(value).click();
    });
});

Cypress.Commands.add('isAddonEnabled', (addon: Addon) => {
  return cy.window().then((win) => {
    const isEnabled = !!win._tutorobject.addons_data.find((item) => item.base_name === addon)?.is_enabled;
    return cy.wrap(isEnabled);
  });
});

Cypress.Commands.add('getWPMedia', (label: string, buttonText: string, replaceButtonText: string) => {
  cy.get('body').then(($body) => {
    // Check if any form-field-wrapper contains the label text
    if ($body.find('[data-cy=form-field-wrapper]').filter((i, el) => Cypress.$(el).text().includes(label)).length > 0) {
      cy.contains('[data-cy=form-field-wrapper]', label)
        .should('be.visible')
        .within(($wrapper) => {
          // Check if the upload button exists within the current wrapper
          const $uploadMedia = $wrapper.find('[data-cy="upload-media"]');
          if ($uploadMedia.length > 0) {
            cy.wrap($uploadMedia).contains(buttonText).click();
          } else {
            cy.get('[data-cy=media-preview] > img').should('be.visible');
            cy.get('[data-cy=replace-media]').contains(replaceButtonText).click();
          }
        })
        .then(() => cy.selectWPMedia());
    } else {
      cy.get('[data-cy=form-field-wrapper]')
        .find('[data-cy=upload-media]')
        .contains(buttonText)
        .click()
        .then(() => cy.selectWPMedia());
    }
  });
});

Cypress.Commands.add('selectWPMedia', () => {
  cy.wait('@queryAttachments').its('response.statusCode').should('eq', 200);

  cy.get('.media-frame')
    .should('be.visible')
    .within(() => {
      cy.get('#menu-item-browse').click();
      cy.get('.spinner.is-active').should('not.exist');
      cy.get('.attachment')
        .its('length')
        .then((length) => {
          if (length === 0) {
            // @TODO: Add a way to upload media
            // cy.get('.media-button-select').click();
          } else {
            cy.get('.attachment').then(($attachments) => {
              const selected = $attachments.filter('.selected').length;
              if (selected === 0) {
                cy.wrap($attachments[0]).click();
              } else {
                const notSelected = $attachments.filter(':not(.selected)');
                cy.wrap(notSelected[Cypress._.random(0, notSelected.length - 1)]).click();
              }
            });
          }
        })
        .then(() => {
          cy.get('.media-button-select').click();
        });
    });
});

Cypress.Commands.add('doesElementExist', (selector) => {
  return cy.get('body').then(($body) => $body.find(selector).length > 0);
});

Cypress.Commands.add('updateCourse', () => {
  cy.get('[data-cy=course-builder-submit-button]').click();
  cy.waitAfterRequest('updateCourse');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Course updated successfully');
});

Cypress.Commands.add('selectDate', (selector: string) => {
  cy.get(`input[name="${selector}"]`).scrollIntoView().click({ force: true });
  cy.wait(250);
  cy.get('.tutor-portal-popover')
    .last()
    .should('be.visible')
    .within(() => {
      cy.get('.rdp-day.rdp-today').click();
    });
});

// Course builder commands
Cypress.Commands.add('saveTopic', (title: string, summary?: string) => {
  cy.getByInputName('title').clear().type(title);
  if (summary) {
    cy.getByInputName('summary').clear().type(summary);
  }
  cy.get('[data-cy=save-topic]').click();
  cy.waitAfterRequest('saveTopic');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Topic saved successfully');
});

Cypress.Commands.add('saveLesson', (lessonData) => {
  cy.get('[data-cy=tutor-modal]').within(() => {
    if (lessonData.description) {
      cy.setTinyMceContent('[data-cy=tutor-tinymce]', lessonData.description);
    }
    if (lessonData.duration) {
      if (lessonData.duration.hour) {
        cy.getByInputName('duration.hour').clear().type(String(lessonData.duration.hour));
      }
      if (lessonData.duration.minute) {
        cy.getByInputName('duration.minute').clear().type(String(lessonData.duration.minute));
      }
      if (lessonData.duration.second) {
        cy.getByInputName('duration.second').clear().type(String(lessonData.duration.second));
      }
    }
    cy.getByInputName('title').clear().type(lessonData.title);
  });

  cy.getWPMedia('Featured Image', 'Upload Image', 'Replace Image');
  cy.getWPMedia('Video', 'Upload Video', 'Replace Thumbnail');
  cy.getWPMedia('Attachments', 'Upload Attachment', '');

  cy.get('[data-cy=save-lesson]').click();
  cy.waitAfterRequest('saveLesson');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Lesson saved successfully');
});

Cypress.Commands.add('saveAssignment', (assignmentData) => {
  cy.get('[data-cy=tutor-modal]').within(() => {
    cy.getByInputName('title').clear().type(assignmentData.title);
    if (assignmentData.summary) {
      cy.setTinyMceContent('[data-cy=tutor-tinymce]', assignmentData.summary);
    }
    if (assignmentData.time_duration) {
      cy.getByInputName('time_duration.value').clear().type(assignmentData.time_duration.value);
    }
    if (assignmentData.total_mark) {
      cy.getByInputName('total_mark').clear().type(String(assignmentData.total_mark));
    }
    if (assignmentData.pass_mark) {
      cy.getByInputName('pass_mark').clear().type(String(assignmentData.pass_mark));
    }
    if (assignmentData.upload_files_limit) {
      cy.getByInputName('upload_files_limit').clear().type(String(assignmentData.upload_files_limit));
    }
    if (assignmentData.upload_file_size_limit) {
      cy.getByInputName('upload_file_size_limit').clear().type(String(assignmentData.upload_file_size_limit));
    }
  });

  cy.getSelectInput('time_duration.time', 'Days');
  cy.getWPMedia('Attachments', 'Upload Attachment', '');

  cy.get('[data-cy=save-assignment]').click();
  cy.waitAfterRequest('saveAssignment');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Assignment created successfully');
});

Cypress.Commands.add('saveQuiz', (quizData) => {
  cy.doesElementExist('[data-cy=edit-quiz-title]').then((exists) => {
    if (exists) {
      cy.get('[data-cy=edit-quiz-title]').click();
    }
  });

  cy.get('[data-cy=tutor-modal]').within(() => {
    cy.getByInputName('quiz_title').clear().type(quizData.quiz_title);
    if (quizData.summary) {
      cy.getByInputName('quiz_description').clear().type(quizData.quiz_description);
    }
    cy.get('[data-cy=save-quiz-title]').click();

    cy.get('[data-cy=add-question]').click();
  });

  cy.get('.tutor-portal-popover').within(() => {
    cy.get('button').contains('True/False').click();
  });

  cy.doesElementExist('[data-cy=quiz-next').then((exists) => {
    if (exists) {
      cy.get('[data-cy=quiz-next').click();
      cy.wait(500);
      cy.getByInputName('quiz_option.time_limit.time_value')
        .clear()
        .type(String(quizData.quiz_option.time_limit.time_value));
      cy.getSelectInput('quiz_option.time_limit.time_type', 'Hours');
      cy.getByInputName('quiz_option.hide_quiz_time_display').check();
      cy.getSelectInput('quiz_option.feedback_mode', 'Reveal Mode');
      // cy.getByInputName('quiz_option.attempts_allowed').clear().type(String(quizData.quiz_option.attempts_allowed));
      cy.getByInputName('quiz_option.passing_grade').clear().type(String(quizData.quiz_option.passing_grade));
      cy.getByInputName('quiz_option.max_questions_for_answer')
        .clear()
        .type(String(quizData.quiz_option.max_questions_for_answer));
    }
  });

  cy.get('[data-cy=save-quiz]').click();
  cy.waitAfterRequest('saveQuiz');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Quiz saved successfully');
});

Cypress.Commands.add('deleteTopic', (index = 0) => {
  cy.get('[data-cy=delete-topic]').eq(index).click();
  cy.get('.tutor-portal-popover').within(() => {
    cy.get('[data-cy=confirm-button]').click();
  });
  cy.waitAfterRequest('deleteTopic');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Topic deleted successfully');
});

Cypress.Commands.add('deleteContent', (type, index = 0) => {
  cy.get(`[data-cy=delete-${type}]`).eq(index).click();
  cy.get('.tutor-portal-popover').within(() => {
    cy.get('[data-cy=confirm-button]').click();
  });
  cy.waitAfterRequest('deleteContent');
  cy.waitAfterRequest('getCourseContents');

  let successMessage = '';
  switch (type) {
    case 'lesson':
      successMessage = 'Lesson deleted successfully';
      break;
    case 'tutor_assignments':
      successMessage = 'Assignment deleted successfully';
      break;
    case 'tutor_quiz':
      successMessage = 'Quiz deleted successfully';
      break;
    default:
      successMessage = 'Content deleted successfully';
  }

  cy.get('[data-cy=tutor-toast]').should('be.visible').contains(successMessage);
});

Cypress.Commands.add('duplicateContent', (type, index = 0) => {
  cy.get(`[data-cy=duplicate-${type}]`).eq(index).click();
  cy.waitAfterRequest('duplicateContent');
  cy.waitAfterRequest('getCourseContents');
  cy.get('[data-cy=tutor-toast]').should('be.visible').contains('Duplicated successfully');
});

Cypress.Commands.add('monetizedBy', () => {
  cy.window().then((win) => {
    return win._tutorobject.settings?.monetize_by;
  });
});
