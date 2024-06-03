import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Course Bundles", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.COURSE_BUNDLES}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.COURSE_BUNDLES);
  });

  it("should be able to search any course", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "test";
    const courseLinkSelector = ".tutor-table-link";
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

  it("should filter by category",()=>{
    cy.filterByCategory()
  })

  it("should perform bulk actions on selected course", () => {
    const options = ["publish", "pending", "draft", "trash"];
    options.forEach((option) => {
      cy.performBulkActionOnSelectedElement(option);
    });
  });

  it("should be able to perform bulk actions on all courses", () => {
    const options = ["publish", "pending", "draft", "trash"];
    options.forEach((option) => {
      cy.performBulkAction(option);
    });
  });

  it("should create a bundle course successfully", () => {
    cy.get(".tutor-wp-dashboard-header a")
      .contains("Add New")
      .click();
    cy.getByInputName("post_title").type("My new bundle course");
    cy.setTinyMceContent(
      "#wp-content-editor-container",
      "Our Digital Marketing Mastery bundle covers vital areas like social media strategies, SEO, email marketing, content creation, and paid advertising on platforms like Google and Facebook."
    );
    cy.get("textarea[name=course_benefits]").type(
      "Participants gain hands-on experience with Google Analytics for data analysis and conversion rate optimization techniques."
    );

    cy.get(".tutor-courses .tutor-js-form-select").click();
    cy.get(
      ".tutor-courses .tutor-js-form-select .tutor-form-select-option"
    ).then(($products) => {
      if ($products.length > 1) {
        cy.get(".tutor-courses .tutor-js-form-select").click();
        cy.wrap($products)
          .eq(1)
          .click();
      }
      if ($products.length > 2) {
        cy.get(".tutor-courses .tutor-js-form-select").click();
        cy.wrap($products)
          .eq(2)
          .click();
      }
    });

    cy.getByInputName("publish").click();
    cy.get(".notice").should("contain.text", "Post published.");
    cy.get("#sample-permalink a")
      .invoke("attr", "href")
      .then((link) => {
        cy.get("a")
          .contains("View post")
          .click();
        cy.url().should("equal", link);
      });
  });

  it("should update a bundle course successfully", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_get_course_bundle_data")) {
          req.alias = "curseRemoveAjax";
        }
      }
    );

    cy.get(".tutor-table tbody tr button[action-tutor-dropdown=toggle]")
      .eq(0)
      .click();
    cy.get(".tutor-dropdown-parent.is-open a")
      .contains("Edit")
      .click();
    cy.url().should("include", "action=edit");
    cy.wait("@curseRemoveAjax").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });

    cy.get("#tutor-bundle-course-list-wrapper").then(($courses) => {
      if (!$courses.text().includes("Select courses to see overview")) {
        cy.get(".tutor-card.tutor-course-card .tutor-bundle-course-delete a")
          .eq(0)
          .click({ force: true });
        cy.wait("@curseRemoveAjax").then((interception) => {
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });

    cy.get("#publish").click();
    cy.get(".notice").should("contain.text", "Post updated.");
    cy.get("#sample-permalink a")
      .invoke("attr", "href")
      .then((link) => {
        cy.get("a")
          .contains("View post")
          .click();
        cy.url().should("equal", link);
      });
  });

  it("should visit a random bundle product", () => {
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-table tbody tr").then(($rows) => {
          const randomNumber = Math.floor(Math.random() * $rows.length);
          cy.wrap($rows[randomNumber])
            .find("a")
            .invoke("attr", "href")
            .then((link) => {
              cy.visit(link);
              cy.url().should("eq", link);
            });
        });
      }
    });
  });

  it("should change a bundle products status", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-table tbody tr")
          .eq(0)
          .then(($row) => {
            cy.wrap($row)
              .find("button[action-tutor-dropdown=toggle]")
              .click();

            for (let index = 0; index < 2; index++) {
              cy.wrap($row)
                .find(".tutor-table-row-status-update")
                .invoke("attr", "data-status")
                .then((status) => {
                  if (status !== "draft") {
                    cy.wrap($row)
                      .find(".tutor-table-row-status-update")
                      .select("draft");
                  } else {
                    cy.wrap($row)
                      .find(".tutor-table-row-status-update")
                      .select("publish");
                  }
                });

              cy.wait("@ajaxRequest").then((interception) => {
                expect(interception.response.body.success).to.equal(true);
              });
            }
          });
      }
    });
  });

  it("should delete a bundle product successfully", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");

    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get(".tutor-table tbody tr button[action-tutor-dropdown=toggle]")
          .eq(0)
          .click();
        cy.get(".tutor-dropdown-parent.is-open a")
          .contains("Delete")
          .click();
        cy.get(".tutor-modal.tutor-is-active button")
          .contains("Yes, I'am Sure")
          .click();

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });
  });

});
