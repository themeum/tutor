describe('Tutor Student Course Journey', () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${Cypress.env("single_course_slug")}/`)
  })

  it('should be able to enroll in a course and complete assignments', () => {
    // Click on enroll course button
    cy.get(".tutor-enroll-course-button").click()

    // Login as a student
    cy.getByInputName("log").type(Cypress.env("student_username"))
    cy.getByInputName("pwd").type(Cypress.env("student_password"))
    cy.get("#tutor-login-form button").contains("Sign In").click()
    cy.url().should("include", Cypress.env("single_course_slug"))

    cy.get('body').then(($body) => {
      // Submit review if already completed
      if ($body.text().includes("How would you rate this course?")) {
        cy.get(".tutor-modal-content .tutor-icon-star-line").eq(4).click()
        cy.get(".tutor-modal-content textarea[name=review]").type("Just completed [Course Name] on [Platform Name], and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze. The interactive quizzes and discussions keep you engaged, and the user-friendly interface enhances the overall experience. The flexibility to learn at your own pace is a game-changer for busy professionals. Quick responses from the support team and valuable supplementary materials make this course a standout. Highly recommend for a quality online learning journey!")
        cy.get(".tutor-modal-content button.tutor_submit_review_btn").click()
      }

      // Start the course
      if ($body.text().includes("Retake This Course")) {
        cy.get("button").contains("Retake This Course").click()
        cy.get("button").contains("Reset Data").click()
      } else if ($body.text().includes("Continue Learning")) {
        cy.get("a").contains("Continue Learning").click()
      } else if ($body.text().includes("Start Learning")) {
        cy.get("a").contains("Start Learning").click()
      }
    })

    cy.url().should("include", Cypress.env("single_course_slug"))

    cy.get(".tutor-course-topic-item").each(($topic, index, $list) => {
      const isLastItem = index === $list.length - 1;

      cy.url().then(($url) => {
        if ($url.includes("/lesson")) {
          cy.get("body").then(($body) => {
            if ($body.text().includes("Mark as Complete")) {
              cy.get("button").contains("Mark as Complete").click()
            }
          })

          cy.get("a").contains("Next").parent().then(($element) => {
            if ($element.attr('disabled')) {
              cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
            } else {
              cy.wrap($element).click()
            }
          })
        }

        if ($url.includes("/assignments")) {
          cy.get("body").then(($body) => {
            if ($body.text().includes("Start Assignment Submit")) {
              if ($body.text().includes("You have missed the submission deadline. Please contact the instructor for more information.")) {
                cy.get("a").contains("Skip To Next").click()
              } else {
                cy.get("#tutor_assignment_start_btn").click()
                cy.url().should('include', "assignments")

                cy.setTinyMceContent(".tutor-assignment-text-area", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sit amet purus lacinia diam pretium interdum. Nullam elementum tincidunt ipsum vel fringilla.")
                cy.get("#tutor_assignment_submit_btn").click()
              }
            }
          })

          cy.get("body").then(($body) => {
            if ($body.text().includes('Submit Assignment')) {
              cy.setTinyMceContent(".tutor-assignment-text-area", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sit amet purus lacinia diam pretium interdum. Nullam elementum tincidunt ipsum vel fringilla.")
              cy.get("#tutor_assignment_submit_btn").click()
            }
          })

          cy.get("body").then(($body) => {
            if ($body.text().includes("Continue Lesson")) {
              cy.get("a").contains("Continue Lesson").click()
            } else if (isLastItem) {
              cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
            }
          })
        }

        if ($url.includes("/quizzes")) {
          cy.get("body").then(($body) => {
            if ($body.text().includes("Start Quiz")) {
              cy.get("button[name=start_quiz_btn]").click()
            }

            if ($body.text().includes("Start Quiz") || $body.text().includes("Submit & Next") || $body.text().includes("Submit Quiz")) {
              cy.get(".quiz-attempt-single-question").each(($question, $index) => {
                if ($question.find("textarea").length) {
                  cy.wrap($question).find("textarea").type("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sit amet purus lacinia diam pretium interdum. Nullam elementum tincidunt ipsum vel fringilla.")
                }

                if ($question.find("input[type=text]").length) {
                  cy.wrap($question).find("input[type=text]").each(($input) => {
                    cy.wrap($input).type("Example answer.")
                  })
                }

                if ($question.find("#tutor-quiz-single-multiple-choice").length) {
                  cy.wrap($question).find(".tutor-quiz-answer-single").eq(0).find("input").click();
                }

                cy.get("button.tutor-quiz-next-btn-all").eq($index).click()
              })
            }

            cy.get("a").contains("Next").parent().then(($element) => {
              if ($element.attr('disabled')) {
                cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
              } else {
                cy.wrap($element).click()
              }
            })
          })
        }

        if ($url.includes("/meet-lessons")) {
          cy.get("body").then(($body) => {
            if ($body.text().includes("Mark as Complete")) {
              cy.get("button").contains("Mark as Complete").click()
            } else {
              if (isLastItem) {
                cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
              } else {
                cy.get(".tutor-course-topic-item").eq(index + 1).children("a").click({force: true})
              }
            }
          })
        }

        if ($url.includes("/zoom-lessons")) {
          cy.get("body").then(($body) => {
            if ($body.text().includes("Mark as Complete")) {
              cy.get("button").contains("Mark as Complete").click()
            } else {
              if (isLastItem) {
                cy.get(".tutor-course-topic-single-header a.tutor-iconic-btn span.tutor-icon-times").parent().click()
              } else {
                cy.get(".tutor-course-topic-item").eq(index + 1).children("a").click({force: true})
              }
            }
          })
        }
      })
    })

    cy.get("body").then(($body) => {
      if ($body.text().includes("Complete Course")) {
        cy.get("button").contains("Complete Course").click()
      }
    })

    // Review submit after course complete
    cy.get("body").then(($body) => {
      if ($body.text().includes("How would you rate this course?")) {
        cy.get(".tutor-modal-content .tutor-icon-star-line").eq(4).click()
        cy.get(".tutor-modal-content textarea[name=review]").type("Just completed [Course Name] on [Platform Name], and it's fantastic! The content is top-notch, instructors are experts in the field, and the real-world examples make learning a breeze. The interactive quizzes and discussions keep you engaged, and the user-friendly interface enhances the overall experience. The flexibility to learn at your own pace is a game-changer for busy professionals.")
        cy.get(".tutor-modal-content button.tutor_submit_review_btn").click()
      }
    })
  })
})
