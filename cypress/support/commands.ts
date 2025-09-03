import endpoints from '@TutorShared/utils/endpoints';
import { type Addon } from '@TutorShared/utils/util';
import { backendUrls } from 'cypress/config/page-urls';
import { type Interception } from 'cypress/types/net-stubbing';

/* eslint-disable @typescript-eslint/no-namespace */
export {};

interface UnifiedFilterOptions {
  selectFieldName: string;
  selectFieldValue?: string;
  filterButtonSelector?: string;
  filterFormSelector?: string;
  selectFieldSelector?: string;
  applyButtonText?: string;
  resultTableSelector?: string;
  resultColumnIndex?: number;
  noDataMessages?: string[];
  skipFirstOption?: boolean;
  waitAfterSelection?: number;

  datePickerYear?: string;
  datePickerMonth?: string;
  datePickerDay?: string;
  onNoData?: () => void;
  onFilterSuccess?: (selectedText: string) => void;
  customValidation?: (selectedText: string, resultElements: JQuery<HTMLElement>) => void;
}

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
      unifiedFilterElements(options: UnifiedFilterOptions): Chainable<JQuery<HTMLElement>>;
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
      toggle(inputName: string, fieldId: string, state?: boolean | undefined): Chainable<JQuery<HTMLElement>>;
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
      getPHPSelectInput: (name: string, value: string) => Chainable<void>;
      saveTutorSettings: () => Chainable<void>;

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
      deleteCourseById(courseId: string): Chainable<void>;
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
      $body.text().includes('No Records Found') ||
      $body.text().includes('No Courses Found.')
    ) {
      cy.log('No data available');
    } else {
      cy.getByInputName('tutor-bulk-checkbox-all').then(($checkboxes) => {
        const checkboxesArray = Cypress._.toArray($checkboxes);
        const randomIndex = Cypress._.random(0, checkboxesArray.length - 1);
        cy.wrap(checkboxesArray[randomIndex]).as('randomCheckbox');
        cy.get('@randomCheckbox').check();
        // cy.get(':nth-child(1) > :nth-child(1) > .td-checkbox > .tutor-form-check-input').check();
        cy.get('.tutor-mr-12 > .tutor-js-form-select').click();
        cy.get(`span[tutor-dropdown-item][data-key=${option}]`).eq(0).click();

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
      $body.text().includes('No Data Found.') ||
      $body.text().includes('No request found') ||
      $body.text().includes('No Data Available in this Section') ||
      $body.text().includes('No records found') ||
      $body.text().includes('No Records Found') ||
      $body.text().includes('No Courses Found.')
    ) {
      cy.log('No data available');
    } else {
      cy.get('#tutor-bulk-checkbox-all').click();
      cy.get('.tutor-mr-12 > .tutor-js-form-select').click();

      cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
        .eq(0)
        .invoke('text')
        .then((text) => {
          const expectedValue = text.trim();
          cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`).eq(0).click();
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
        $body.text().includes('No Records Found') ||
        $body.text().includes('No Courses Found.') ||
        $body.text().includes('No Data Found.')
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

Cypress.Commands.add('toggle', (inputName, fieldId, state = undefined) => {
  cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
    if (req.body.includes('tutor_option_save')) {
      req.alias = 'ajaxRequest';
    }
  });

  const toggleSelector = `${fieldId} > .tutor-option-field-input > .tutor-form-toggle > .tutor-form-toggle-control`;
  const checkboxSelector = `${fieldId} > .tutor-option-field-input > .tutor-form-toggle > input[type="checkbox"]`;

  // Check the current toggle state
  cy.get(checkboxSelector).then(($checkbox) => {
    const isChecked = $checkbox.prop('checked');

    const shouldToggle = (state && !isChecked) || (!state && isChecked) || state === null; // toggle by default if no explicit state

    if (shouldToggle) {
      cy.get(toggleSelector).click({ force: true });
    }

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

  cy.doesElementExist('[data-cy=quiz-next]').then((exists) => {
    if (exists) {
      cy.get('[data-cy=quiz-next]').click();
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

Cypress.Commands.add('getPHPSelectInput', (name: string, value: string) => {
  cy.get(`select[name*="${name}"]`).next().click({ force: true });
  cy.get('.tutor-form-select-options:visible').within(() => {
    cy.get('span').contains(value).click();
  });
});

Cypress.Commands.add('saveTutorSettings', () => {
  cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
    if (req.body.includes('tutor_option_save')) {
      req.alias = 'saveSettings';
    }
  });
  cy.get('button#save_tutor_option').click({ force: true });
  cy.waitAfterRequest('saveSettings');
});

Cypress.Commands.add('deleteCourseById', (id: string) => {
  cy.intercept('POST', `${Cypress.env('base_url')}/wp-admin/admin-ajax.php`, (req) => {
    if (req.body.includes(endpoints.GET_COURSE_DETAILS)) {
      req.alias = 'getCourseDetails';
    }
    if (req.body.includes(endpoints.UPDATE_COURSE)) {
      req.alias = 'updateCourse';
    }
  });
  cy.visit(`${Cypress.env('base_url')}${backendUrls.COURSE_BUILDER}${id}`);
  cy.loginAsAdmin();

  cy.waitAfterRequest('getCourseDetails');
  cy.get('[data-cy=dropdown-trigger]').click();
  cy.get('.tutor-portal-popover').within(() => {
    cy.get('[data-cy=move-to-trash]').click();
  });

  cy.waitAfterRequest('updateCourse');
  cy.wait(1000);
  cy.url().should('include', '/wp-admin/admin.php?page=tutor');
});

Cypress.Commands.add('unifiedFilterElements', (options: UnifiedFilterOptions) => {
  const defaultUnifiedFilterOptions: Partial<UnifiedFilterOptions> = {
    filterButtonSelector: '.tutor-wp-dashboard-filters-button',
    filterFormSelector: '.tutor-admin-dashboard-filter-form',
    applyButtonText: 'Apply Filters',
    noDataMessages: ['No Data Found.'],
    skipFirstOption: true,
    waitAfterSelection: 1000,
    datePickerYear: '2024',
    datePickerMonth: 'June',
    datePickerDay: '11',
  };

  cy.get('body').then(($body) => {
    if (options.noDataMessages?.some((message) => $body.text().includes(message))) {
      cy.log('No data found, skipping filter test');
      return;
    }

    const config = { ...defaultUnifiedFilterOptions, ...options };

    // Validate required options
    if (!config.filterButtonSelector || !config.filterFormSelector) {
      throw new Error('filterButtonSelector and filterFormSelector are required');
    }

    if (!config.selectFieldName && !config.selectFieldSelector) {
      throw new Error('Either selectFieldName or selectFieldSelector must be provided');
    }

    let selectedOptionText = '';

    cy.get('body').then(($body) => {
      const filterButtonExists = $body.find(config.filterButtonSelector || '').length > 0;

      if (!filterButtonExists) {
        cy.log('Filter button not found, skipping filter test');
        return;
      }

      cy.get(config.filterButtonSelector || '').click();

      cy.get(config.filterFormSelector || '')
        .should('be.visible')
        .within(() => {
          const handleOptionSelection = ($options: JQuery<HTMLElement>) => {
            if ($options.length <= 1) {
              cy.log('No valid options available for selection');
              return;
            }

            if (config.selectFieldName !== 'date' && config.selectFieldValue) {
              const selectedOption = $options.filter((index, option) => {
                return option.innerText.trim().includes(config?.selectFieldValue || '');
              });
              if (selectedOption.length > 0) {
                selectedOptionText = selectedOption.text().trim();
                cy.log(`Selected predefined option: ${selectedOptionText}`);
                cy.wrap(selectedOption).scrollIntoView().click();
                return;
              } else {
                cy.log(`Predefined option "${config.selectFieldValue}" not found`);
              }
            } else {
              const startIndex = config.skipFirstOption ? 1 : 0;
              const validOptionsCount = $options.length - startIndex;
              const randomIndex = Math.floor(Math.random() * validOptionsCount) + startIndex;

              const selectedOption = $options[randomIndex];
              selectedOptionText = selectedOption.innerText.trim();

              cy.log(`Selected: ${selectedOptionText} (${randomIndex}/${$options.length})`);

              cy.wrap(selectedOption).scrollIntoView().click();
            }

            if (config.waitAfterSelection) {
              cy.wait(config.waitAfterSelection);
            }
          };

          if (config.selectFieldName !== 'date') {
            cy.get('.tutor-wp-dashboard-filters-item').each(($filterItem) => {
              const hasTargetSelect = $filterItem.find(`select[name="${config.selectFieldName}"]`).length > 0;

              if (!hasTargetSelect) {
                return;
              }

              cy.wrap($filterItem).click();
              cy.wrap($filterItem).within(() => {
                cy.get('.tutor-form-select-options')
                  .should('be.visible')
                  .within(() => {
                    cy.get('.tutor-form-select-option').then(handleOptionSelection);
                  });
              });
            });
          } else if (config.selectFieldName === 'date') {
            cy.get('.react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control').click();
            cy.get('.dropdown-years').click();
            cy.get('.dropdown-years>.dropdown-list')
              .contains(config.datePickerYear || '2024')
              .click();

            cy.get('.dropdown-months > .dropdown-label').click();
            cy.get('.dropdown-months > .dropdown-list>li')
              .contains(config.datePickerMonth || 'June')
              .click();

            cy.get(`.react-datepicker__day--0${(config.datePickerDay || '11').padStart(2, '0').slice(-2)}`)
              .contains(config.datePickerDay || '')
              .click();
            selectedOptionText = `${config.datePickerMonth} ${config.datePickerDay}, ${config.datePickerYear}`;
          } else if (config.selectFieldSelector) {
            cy.get(config.selectFieldSelector).click();
            cy.get('.tutor-form-select-options')
              .should('be.visible')
              .within(() => {
                cy.get('.tutor-form-select-option').then(handleOptionSelection);
              });
          }

          if (config.applyButtonText) {
            cy.get('button.tutor-btn.tutor-btn-outline-primary')
              .contains(config.applyButtonText)
              .click({ force: true });
          }
        });

      // Handle results verification
      if (config.resultTableSelector && selectedOptionText) {
        cy.get('body').then(($bodyAfterFilter) => {
          const hasNoDataMessage = config.noDataMessages?.some((message) => $bodyAfterFilter.text().includes(message));

          if (hasNoDataMessage) {
            cy.log('No data found after applying filter');
            config.onNoData?.();
            return;
          }

          if (config.resultColumnIndex) {
            const resultSelector = `${config.resultTableSelector}>tbody>tr>td:nth-child(${config.resultColumnIndex})`;
            cy.get(resultSelector).then(($results) => {
              if (config.customValidation) {
                config.customValidation(selectedOptionText, $results);
              } else {
                cy.wrap($results).each(($resultRow) => {
                  cy.wrap($resultRow).should('contain.text', selectedOptionText);
                });
              }

              config.onFilterSuccess?.(selectedOptionText);
            });
          }
        });
      }
    });
  });
});
