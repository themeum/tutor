import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Instructors", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.INSTRUCTORS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.INSTRUCTORS);
  });

  it("should create a instructor successfully", () => {
    const randomNumber = Math.floor(Math.random() * 100) + 1;

    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_add_instructor")) {
          req.alias = "addInstructorAjax";
        }
      }
    );

    cy.get("button")
      .contains("Add New")
      .click();

    cy.get('#tutor-instructor-add-new [name="first_name"]').type("John");
    cy.get('#tutor-instructor-add-new [name="last_name"]').type("Doe");
    cy.get('#tutor-instructor-add-new [name="user_login"]').type(
      `john_doe_${randomNumber}`
    );
    cy.get('#tutor-instructor-add-new [name="phone_number"]').type("123456789");
    cy.get('#tutor-instructor-add-new [name="email"]').type(
      `john.doe${randomNumber}@example.com`
    );
    cy.get('#tutor-instructor-add-new [name="password"]').type("password123");
    cy.get('#tutor-instructor-add-new [name="password_confirmation"]').type(
      "password123"
    );
    cy.setTinyMceContent(
      "#wp-tutor_profile_bio-editor-container",
      "This is a test bio."
    );

    cy.get("#tutor-new-instructor-form").submit();

    cy.wait("@addInstructorAjax").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

  it("should update a instructor successfully", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_update_instructor_data")) {
          req.alias = "updateInstructorAjax";
        }
      }
    );
    cy.get(".tutor-table tbody tr")
      .eq(0)
      .find("a")
      .contains("Edit")
      .click();
    cy.setTinyMceContent(
      ".tutor-instructor-edit-modal.tutor-is-active .wp-editor-container",
      "This is an updated bio."
    );
    cy.get("form.tutor-instructor-edit-modal.tutor-is-active").submit();
    cy.wait("@updateInstructorAjax").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

  it("should be able to search any instructor", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "John Doe";
    const courseLinkSelector = ".tutor-d-flex.tutor-align-center.tutor-gap-1";
    const submitButtonSelector = "";
    const submitWithButton = false;
    cy.search(
      searchInputSelector,
      searchQuery,
      courseLinkSelector,
      submitButtonSelector,
      submitWithButton
    );
  });

  it("should be able to perform bulk actions on all instructors", () => {
    const options = ["approved", "pending", "blocked"];
    options.forEach((option)=>{
      cy.performBulkAction(option)
    })
  });

  it("should show validation error message for password mismatch", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_add_instructor")) {
          req.alias = "addInstructorAjax";
        }
      }
    );
    cy.get("button")
      .contains("Add New")
      .click();

    cy.get('#tutor-instructor-add-new [name="first_name"]').type("John");
    cy.get('#tutor-instructor-add-new [name="last_name"]').type("Doe");
    cy.get('#tutor-instructor-add-new [name="user_login"]').type(`john_doe`);
    cy.get('#tutor-instructor-add-new [name="email"]').type(
      `john.doe@example.com`
    );
    cy.get('#tutor-instructor-add-new [name="password"]').type("password123");
    cy.get('#tutor-instructor-add-new [name="password_confirmation"]').type(
      "password1234"
    );

    cy.get("#tutor-new-instructor-form").submit();

    cy.wait("@addInstructorAjax").then((interception) => {
      expect(interception.response.body.success).to.equal(false);
    });
    cy.get(".tutor-alert-text").should(
      "include.text",
      "Your passwords should match each other. Please recheck."
    );
  });

  it("should change a instructor status successfully", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_instructor_bulk_action")) {
          req.alias = "statusUpdateAjax";
        }
      }
    );

    cy.get("body").then(($body) => {
      cy.get(".tutor-table tbody tr")
        .eq(0)
        .then(($row) => {
          for (let index = 0; index < 2; index++) {
            cy.wrap($row)
              .find(".tutor-table-row-status-update")
              .invoke("val")
              .then((status) => {
                if (status !== "pending") {
                  cy.wrap($row)
                    .find(".tutor-table-row-status-update")
                    .select("pending");
                } else {
                  cy.wrap($row)
                    .find(".tutor-table-row-status-update")
                    .select("approved");
                }
              });

            cy.wait("@statusUpdateAjax").then((interception) => {
              expect(interception.response.body.success).to.equal(true);
            });
          }
        });
    });
  });

  it("should visit a instructor profile successfully", () => {
    cy.get(".tutor-table tbody tr")
      .eq(0)
      .find("td")
      .eq(1)
      .then(($data) => {
        cy.wrap($data)
          .find("a")
          .invoke("attr", "href")
          .then((link) => cy.visit(link));
        cy.url().should("include", "profile");
        cy.get(".tutor-user-profile-content").should(
          "include.text",
          "Biography"
        );
      });
  });
});