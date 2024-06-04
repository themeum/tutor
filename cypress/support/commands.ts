declare namespace Cypress {
  interface Chainable {
    getByInputName(dataTestAttribute: string): Chainable<JQuery<HTMLElement>>;
    setTinyMceContent(
      selector: string,
      content: string
    ): Chainable<JQuery<HTMLElement>>;
    loginAsAdmin(): Chainable<JQuery<HTMLElement>>;
    loginAsInstructor(): Chainable<JQuery<HTMLElement>>;
    loginAsStudent(): Chainable<JQuery<HTMLElement>>;
    performBulkAction(option: string): Chainable<JQuery<HTMLElement>>;
    performBulkActionOnSelectedElement(
      option: string
    ): Chainable<JQuery<HTMLElement>>;
    filterByCategory(
      // selector: string,
    ): Chainable<JQuery<HTMLElement>>;
    search(
      searchInputSelector: string,
      searchQuery: string,
      courseLinkSelector: string,
      submitButtonSelector: string,
      submitWithButton: boolean
    ): Chainable<JQuery<HTMLElement>>;
    selectPageFromDropdownAndSaveChanges(
      commonSelector: string,
      saveButtonSelector: string,
      apiFieldOption: string,
      dataValueAttribute: string
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
  }
}

Cypress.Commands.add("getByInputName", (selector) => {
  return cy.get(`input[name="${selector}"]`);
});

Cypress.Commands.add("setTinyMceContent", (selector, content) => {
  // wait for tinymce to be loaded
  cy.window().should("have.property", "tinymce");

  // wait for the editor to be rendered
  cy.get(selector)
    .find("textarea")
    .as("editorTextarea")
    .should("exist");

  // set the content for the editor by its dynamic id
  cy.window().then((win) =>
    cy.get("@editorTextarea").then((element) => {
      const editorId = element.attr("id");
      const editorInstance = (win as any).tinymce.EditorManager.get().filter(
        (editor) => editor.id === editorId
      )[0];
      editorInstance.setContent(content);
    })
  );
});

Cypress.Commands.add("loginAsAdmin", () => {
  cy.wait(500);
  cy.getByInputName("log").type(Cypress.env("admin_username"));
  cy.getByInputName("pwd").type(Cypress.env("admin_password"));
  cy.get("form#loginform").submit();
});

Cypress.Commands.add("loginAsInstructor", () => {
  cy.getByInputName("log").type(Cypress.env("instructor_username"));
  cy.getByInputName("pwd").type(Cypress.env("instructor_password"));
  cy.get("#tutor-login-form button")
    .contains("Sign In")
    .click();
});

Cypress.Commands.add("loginAsStudent", () => {
  cy.getByInputName("log").type(Cypress.env("student_username"));
  cy.getByInputName("pwd").type(Cypress.env("student_password"));
  cy.get("#tutor-login-form button")
    .contains("Sign In")
    .click();
});

Cypress.Commands.add("performBulkActionOnSelectedElement", (option) => {
  cy.getByInputName("tutor-bulk-checkbox-all").then(($checkboxes) => {
    const checkboxesArray = Cypress._.toArray($checkboxes);
    const randomIndex = Cypress._.random(0, checkboxesArray.length - 1);
    cy.wrap(checkboxesArray[randomIndex]).as("randomCheckbox");
    cy.get("@randomCheckbox").check();
    cy.get(".tutor-mr-12 > .tutor-js-form-select").click();
    cy.get(
      `span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`
    ).click();

    cy.get("#tutor-admin-bulk-action-btn")
      .contains("Apply")
      .click();
    cy.get("#tutor-confirm-bulk-action").click();

    cy.get('@randomCheckbox').invoke('attr', 'value').then((id) => {
      if (option === "trash") {
        cy.get(`.tutor-table-row-status-update[data-id="${id}"]`).should('not.exist');
      } else {
        cy.get(`.tutor-table-row-status-update[data-id="${id}"]`)
          .invoke('attr', 'data-status')
          .then((status) => {
            expect(status).to.include(option);
          });
      }
    });
  });
});
// perform publish,pending,draft,trash on all courses
Cypress.Commands.add("performBulkAction", (option) => {
  cy.get("#tutor-bulk-checkbox-all").click();
  cy.get(".tutor-mr-12 > .tutor-js-form-select").click();

  cy.get(`span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`)
    .invoke("text")
    .then((text) => {
      const expectedValue = text.trim();
      cy.get(
        `span[tutor-dropdown-item][data-key=${option}].tutor-nowrap-ellipsis`
      ).click();
      cy.get("#tutor-admin-bulk-action-btn")
        .contains("Apply")
        .click();
      cy.get("#tutor-confirm-bulk-action")
        .contains("Yes, I'am Sure")
        .click();

      if (option === "trash") {
        cy.contains("No Data Available in this Section");
      } else {
        cy.get("select.tutor-table-row-status-update")
          .invoke("val")
          .then((selectedValue) => {
            expect(selectedValue).to.include(expectedValue.toLowerCase());
          });
      }
    });
});

Cypress.Commands.add(
  "filterByCategory",
  () => {
    cy.get(".tutor-js-form-select").eq(1).click()
    cy.get(".tutor-form-select-options").eq(1).then(()=>{
      cy.get('.tutor-form-select-option').then(($options) => {
        const randomIndex = Cypress._.random(6, $options.length - 3);
        const $randomOption = Cypress.$($options[randomIndex]);
        cy.wrap($randomOption).find('span[tutor-dropdown-item]').click();
      }).then(() => {
        cy.get('span.tutor-form-select-label[tutor-dropdown-label]').eq(1)
          .invoke('text')
          .then((retrievedText) => {
            console.log("Text:", retrievedText.trim());
            cy.get(".tutor-fw-normal.tutor-fs-7").each(($category) => {
              cy.wrap($category).invoke('text').then((categoryText) => {
                if (categoryText.trim() === retrievedText.trim()) {
                  cy.wrap($category).click();
                }
              });
            });
          });
      });
    }) 
  }
);

Cypress.Commands.add(
  "search",
  (
    searchInputSelector,
    searchQuery,
    courseLinkSelector,
    submitButtonSelector,
    submitWithButton = false
  ) => {
    cy.get(searchInputSelector).type(`${searchQuery}{enter}`);
    if (submitWithButton) {
      cy.get(searchInputSelector).clear();
      cy.get(submitButtonSelector).click();
    }
    let count = 0;
    cy.get(courseLinkSelector)
      .eq(0)
      .each(($link) => {
        const courseName = $link.text().trim();
        if (courseName.includes(searchQuery)) {
          count++;
          expect(courseName.toLowerCase()).to.include(
            searchQuery.toLowerCase()
          );
          cy.get(courseLinkSelector)
            .eq(0)
            .its("length")
            .then((totalVisibleElements) => {
              expect(count).to.eq(totalVisibleElements);
            });
        }
      });
  }
);

Cypress.Commands.add(
  "selectPageFromDropdownAndSaveChanges",
  (commonSelector, saveButtonSelector, apiFieldOption, dataValueAttribute) => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_option_save")) {
          req.alias = "ajaxRequest";
        }
      }
    );
    cy.get(
      `${commonSelector} > .tutor-option-field-input > .tutor-js-form-select`
    ).click();
    cy.get(
      `${commonSelector} > .tutor-option-field-input > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > .tutor-form-select-option`
    ).then((options) => {
      const randomIndex = Math.floor(Math.random() * options.length);
      cy.wrap(options[randomIndex]).click();
      cy.get(
        `${commonSelector} > .tutor-option-field-input > .tutor-js-form-select > span[tutor-dropdown-label]`
      )
        .invoke("attr", dataValueAttribute)
        .then((dataValue) => {
          cy.contains(`${saveButtonSelector}`).click({ force: true });

          cy.wait("@ajaxRequest").then((interception) => {
            expect(interception.response.body.success).to.equal(true);

            const requestBody = interception.request.body;
            const params = new URLSearchParams(requestBody);
            const tutorOptionId = params.get(`${apiFieldOption}`);
            expect(tutorOptionId).to.equal(dataValue);
          });
        });
    });
  }
);

Cypress.Commands.add("toggle", (inputName, fieldId) => {
  cy.intercept(
    "POST",
    `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
    (req) => {
      if (req.body.includes("tutor_option_save")) {
        req.alias = "ajaxRequest";
      }
    }
  );
  cy.get(
    `${fieldId} > .tutor-option-field-input > .tutor-form-toggle > .tutor-form-toggle-control`
  ).click();

  cy.getByInputName(`${inputName}`)
    .invoke("attr", "value")
    .then((dataValue) => {
      cy.contains("Save Changes").click({ force: true });

      cy.wait("@ajaxRequest").then((interception) => {
        expect(interception.response.body.success).to.equal(true);

        const requestBody = interception.request.body;
        const params = new URLSearchParams(requestBody);
        const tutorOptionId = params.get(`${inputName}`);

        expect(tutorOptionId).to.equal(dataValue);
      });
    });
});

Cypress.Commands.add("isEnrolled", () => {
  cy.get("body").then(($body) => {
    if ($body.find("button[name='add-to-cart']").length > 0) {
      return false;
    }
    if ($body.find("a.tutor-woocommerce-view-cart").length > 0) {
      return false;
    } else {
      return true;
    }
  });
});

Cypress.Commands.add("handleCourseStart", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Retake This Course")) {
      cy.get("button")
        .contains("Retake This Course")
        .click();
      cy.get("button")
        .contains("Reset Data")
        .click();
      cy.wait("@ajaxRequest").then((interception) => {
        expect(interception.response.body.success).to.equal(true);
      });
    } else if ($body.text().includes("Continue Learning")) {
      cy.get("a")
        .contains("Continue Learning")
        .click();
    } else if ($body.text().includes("Start Learning")) {
      cy.get("a")
        .contains("Start Learning")
        .click();
    }
  });
});

Cypress.Commands.add("completeLesson", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Mark as Complete")) {
      cy.get("button")
        .contains("Mark as Complete")
        .click();
      cy.wait(1000);
      cy.get("body").should("not.contain", "Mark as Complete");
    }
  });
});

Cypress.Commands.add("handleNextButton", () => {
  cy.get("a")
    .contains("Next")
    .parent()
    .then(($element) => {
      if ($element.attr("disabled")) {
        cy.get(
          ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
        )
          .parent()
          .click();
      } else {
        cy.wrap($element).click();
      }
    });
});

Cypress.Commands.add("handleAssignment", (isLastItem) => {

  cy.get("body").then(($body) => {
    const bodyText = $body.text();

    if (bodyText.includes("You have missed the submission deadline. Please contact the instructor for more information.")) {
      cy.get('.tutor-btn-ghost').contains("Skip To Next").click();
      return;
    }

    if (bodyText.includes("Start Assignment Submit")) {
      cy.get("#tutor_assignment_start_btn").click(); 
      cy.wait('@ajaxRequest').then((interception) => {
        expect(interception.response.statusCode).to.equal(200);
      });
      cy.url().should("include", "assignments");
      cy.setTinyMceContent(
        ".tutor-assignment-text-area",
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
      );
      cy.get("#tutor_assignment_submit_btn").click();
      cy.get("body").should("contain.text", "Your Assignment");
    }

    if (bodyText.includes("Submit Assignment")) {
      cy.setTinyMceContent(
        ".tutor-assignment-text-area",
        "Lorem ipsum dolor sit amet, consectetur adipiscing elit."
      );
      cy.get("#tutor_assignment_submit_btn").click();
      cy.get("body").should("contain.text", "Your Assignment");
    }
    cy.get("body").then(($body) => {
      if ($body.text().includes("Continue Lesson")) {
        cy.get("a").contains("Continue Lesson").click()
      } else if (isLastItem) {
        cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
      }
    })

  });
});

Cypress.Commands.add("handleQuiz", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Start Quiz")) {
      cy.get("button[name=start_quiz_btn]").click();
    }
    if (
      $body.text().includes("Start Quiz") ||
      $body.text().includes("Submit & Next") ||
      $body.text().includes("Submit Quiz")
    ) {
      cy.get(".quiz-attempt-single-question").each(($question, $index) => {
        if ($question.find("textarea").length) {
          cy.wrap($question)
            .find("textarea")
            .type("Sample answer for text area question.");
        }
        if ($question.find("input[type=text]").length) {
          cy.wrap($question)
            .find("input[type=text]")
            .each(($input) => {
              cy.wrap($input).type("Sample text input answer.");
            });
        }
        if ($question.find("#tutor-quiz-single-multiple-choice").length) {
          cy.wrap($question)
            .find(".tutor-quiz-answer-single")
            .eq(0)
            .find("input")
            .click();
        }
        cy.get("button.tutor-quiz-next-btn-all")
          .eq($index)
          .click();
      });
    }
    cy.get("a")
      .contains("Next")
      .parent()
      .then(($element) => {
        if ($element.attr("disabled")) {
          cy.get(
            ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
          )
            .parent()
            .click();
        } else {
          cy.wrap($element).click();
        }
      });
  });
});

Cypress.Commands.add("handleMeetingLesson", (isLastItem) => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Mark as Complete")) {
      cy.get("button")
        .contains("Mark as Complete")
        .click();
    } else {
      if (isLastItem) {
        cy.get(
          ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
        )
          .parent()
          .click();
      } else {
        cy.get(".tutor-course-topic-item")
          .children("a")
          .click({ force: true });
      }
    }
  });
});

Cypress.Commands.add("handleZoomLesson", (isLastItem) => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Mark as Complete")) {
      cy.get("button")
        .contains("Mark as Complete")
        .click();
    } else {
      if (isLastItem) {
        cy.get(
          ".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times"
        )
          .parent()
          .click();
      } else {
        cy.get(".tutor-course-topic-item")
          .children("a")
          .click({ force: true });
      }
    }
  });
});

Cypress.Commands.add("completeCourse", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("Complete Course")) {
      cy.get("button")
        .contains("Complete Course")
        .click();
    }
  });
});

Cypress.Commands.add("submitCourseReview", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("How would you rate this course?")) {
      cy.get(".tutor-modal-content .tutor-icon-star-line")
        .eq(4)
        .click();
      cy.get(".tutor-modal-content textarea[name=review]").type(
        "Just completed a course on TutorLMS, and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze. The interactive quizzes and discussions keep you engaged, and the user-friendly interface enhances the overall experience. The flexibility to learn at your own pace is a game-changer for busy professionals."
      );
      cy.get(".tutor-modal-content button.tutor_submit_review_btn").click();
      cy.wait("@ajaxRequest").then((interception) => {
        expect(interception.response.body.success).to.equal(true);
      });
      cy.wait(5000);
    }
  });
});

Cypress.Commands.add("viewCertificate", () => {
  cy.get("body").then(($body) => {
    if ($body.text().includes("View Certificate")) {
      cy.get("a")
        .contains("View Certificate")
        .click();
      cy.url().should("include", "tutor-certificate");
      cy.wait(5000);
    }
  });
});
