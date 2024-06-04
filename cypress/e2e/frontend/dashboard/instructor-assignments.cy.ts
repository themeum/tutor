import { frontendUrls } from "../../../config/page-urls";

describe("Tutor Dashboard Assignments", () => {
  beforeEach(() => {
    cy.visit(
      `${Cypress.env("base_url")}/${frontendUrls.dashboard.ASSIGNMENTS}`
    );
    cy.loginAsInstructor();
    cy.url().should("include", frontendUrls.dashboard.ASSIGNMENTS);
  });
  
  it("should filter announcements", () => {
    cy.get(".tutor-col-lg-6 > .tutor-js-form-select").click();
    cy.get(
      ".tutor-col-lg-6 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options span[tutor-dropdown-item]"
    ).then(($options) => {
      const randomIndex = Cypress._.random(1, $options.length - 1);
      const $randomOption = $options.eq(randomIndex);
      cy.wrap($randomOption).click({ force: true });
      const selectedOptionText = $randomOption.text().trim();
      cy.get("body").then(($body) => {
        if ($body.text().includes("No Data Found from your Search/Filter")) {
          cy.log("No data available");
        } else {
          cy.get(".tutor-fs-7>a").each(
            ($announcement) => {
              cy.wrap($announcement).should("contain.text", selectedOptionText);
            }
          );
        }
      });
    });
  });
  it("should evaluate all the assignments", () => { 
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".table-assignment tbody tr").each(($row, $index) => {
          const totalSubmits = Number(
            $row
              .find("td")
              .eq(2)
              .text()
          );
          
          if (totalSubmits > 0) {
            // Only evaluate assignment which has any submission
            cy.get(".table-assignment tbody tr a.tutor-btn-outline-primary")
              .eq($index)
              .invoke("attr", "href")
              .then((link) => {
                cy.visit(link);
                cy.url().should("include", "assignments/submitted");
                cy.get(".tutor-btn.tutor-btn-outline-primary.tutor-btn-sm").eq(0).click()
                cy.get("body").then(($body) => {
                  if ($body.text().includes("Evaluate")) {
                    cy.get(".tutor-btn.tutor-btn-primary.tutor-mt-16")
                      .contains("Evaluate")
                      .click();
                    cy.url().should("include", "view_assignment");

                    cy.url().then((url) => {
                      cy.intercept("POST", url).as("ajaxRequest");

                      cy.get("input[type=number]").clear().type("5");
                      cy.get("textarea").clear().type(
                        "The assignment displays a strong grasp of the subject, excellent organization, and effective communication, reflecting high-level critical thinking."
                      );
                      cy.get("button")
                        .contains("Evaluate this submission")
                        .click();

                        cy.intercept(
                            "POST",
                            `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
                            (req) => {
                              if (req.body.includes("tutor_evaluate_assignment_submission")) {
                                req.alias = "ajaxRequest";
                              }
                            }
                          );

                      cy.wait("@ajaxRequest").then((interception) => {
                        expect(interception.response.statusCode).to.equal(200)
                      });
                      cy.get('.submitted-assignment-title > .tutor-btn')
                      .contains("Back")
                      .click();
                      cy.get('.tutor-mb-24 > .tutor-btn').contains("Back")
                      .click();
                    });
                  } else {
                    cy.get('.tutor-mb-24 > .tutor-btn').contains("Back")
                    .click();
                  }
                });
              });
          }
        });
      }
    });
  });
});
