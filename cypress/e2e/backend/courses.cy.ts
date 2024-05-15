import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.COURSES}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.COURSES);
  });

  it("should show warning when no course is selected", () => {
      cy.get(".tutor-form-select-label").then(()=>{
          cy.get("#tutor-admin-bulk-action-btn").contains("Apply").click()
      })

      cy.contains("Nothing was selected for bulk action.");
  });

  it("should be able to search any course", () => {
      const searchQuery = "Intro"
      cy.get('#tutor-backend-filter-search').type(`${searchQuery}{enter}`);

      let count = 0;

      cy.get('.tutor-table-link').eq(0).each(($link) => {
          const courseName = $link.text().trim();

          if (courseName.includes(searchQuery)) {
              count++;

              expect(courseName.toLowerCase()).to.include(searchQuery.toLowerCase());
              cy.get('.tutor-table-link').eq(0).its('length').then((totalVisibleCourses) => {
                  expect(count).to.eq(totalVisibleCourses);
              });
          }
      });
  });

  it("should be able to duplicate a course successfully", () => {

      cy.get(".tutor-iconic-btn[action-tutor-dropdown]").eq(0).click()
      cy.get('.tutor-dropdown-item').contains("Duplicate").click()
      cy.get('.tutor-table-link').eq(0).invoke('text').then(courseName => {

      const duplicatedCourseName = `${courseName.trim()}`;
          expect(duplicatedCourseName.includes('(Copy')).to.be.true;
          cy.contains(duplicatedCourseName).should('exist');
      });

  });

  it("should be able to delete a course successfully", () => {

      cy.get(".tutor-iconic-btn[action-tutor-dropdown]").eq(0).click()
      cy.get('.tutor-dropdown-item').contains("Delete Permanently").click()
      cy.get('#tutor-common-confirmation-form > .tutor-d-flex > .tutor-btn-primary').contains("Yes, I'am Sure").click()

      cy.get('.tutor-table-link').eq(0).invoke('text').then(courseName => {
          cy.contains(courseName).should('not.exist');
      });

  });

it("should be able to perform bulk actions on all courses", () => {
    const options = ["publish", "pending", "draft","trash"];
  
    options.forEach((option) => {
      cy.performBulkAction(option);
    });
  });


});
