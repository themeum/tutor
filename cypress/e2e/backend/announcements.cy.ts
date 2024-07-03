import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Announcements", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.ANNOUNCEMENTS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.ANNOUNCEMENTS);
  });

  it("should create a new announcement", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_announcement_create")) {
          req.alias = "ajaxRequest";
        }
      }
    );
    cy.get("button[data-tutor-modal-target=tutor_announcement_new]").click();
    cy.get("#tutor_announcement_new input[name=tutor_announcement_title]").type(
      "Important Announcement - Upcoming Student Assembly"
    );
    cy.get(
      "#tutor_announcement_new textarea[name=tutor_announcement_summary]"
    ).type(
      "I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly."
    );
    cy.get("#tutor_announcement_new button")
      .contains("Publish")
      .click();

    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

  it("should update an announcement", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_announcement_create")) {
          req.alias = "ajaxRequest";
        }
      }
    );

    cy.get("button[data-tutor-modal-target=tutor_announcement_new]").click();
    cy.get("#tutor_announcement_new input[name=tutor_announcement_title]")
      .clear()
      .type("Important Announcement - Updated Announcement Title");
    cy.get(
      "#tutor_announcement_new textarea[name=tutor_announcement_summary]"
    ).type(
      "I trust this message finds you well. As we prepare for the commencement of a dynamic new semester, we have pivotal information to share in our upcoming Student Assembly."
    );
    cy.get("#tutor_announcement_new button")
      .contains("Publish")
      .click();

    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

  it("should view and delete an announcement", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_announcement_delete")) {
          req.alias = "ajaxRequest";
        }
      }
    );

    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No request found") ||
        $body.text().includes("No Data Available in this Section") ||
        $body.text().includes("No records found") ||
        $body.text().includes("No Records Found")
      ) {
        cy.log("No data found");
      } else {
        cy.get("button.tutor-announcement-details")
          .eq(0)
          .click();
        cy.get(
          ".tutor-modal.tutor-is-active button.tutor-modal-btn-delete"
        ).click();
        cy.get(".tutor-modal.tutor-is-active button")
          .contains("Yes, Delete This")
          .click();

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });
  });

  it("should be able to search any announcement", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "Important Announcement";
    const courseLinkSelector =
      ".td-course.tutor-color-black.tutor-fs-6.tutor-fw-medium";
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

  it("should filter announcements", () => {
    cy.get(":nth-child(2) > .tutor-js-form-select").click();
    cy.get(
      ":nth-child(2) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis"
    ).click({ force: true });
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No request found") ||
        $body.text().includes("No Data Available in this Section") ||
        $body.text().includes("No records found") ||
        $body.text().includes("No Records Found")
      ) {
        cy.log("No data available");
      } else {
        cy.get(".tutor-fs-7.tutor-fw-medium.tutor-color-muted").each(
          ($announcement) => {
            console.log($announcement);
          }
        );
      }
    });
  });

  it("should filter announcements", () => {
    cy.get(":nth-child(2) > .tutor-js-form-select").click();
    cy.get(
      ":nth-child(2) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options"
    ).then(($option) => {
      const selectedOptionText = $option.text().trim();
      // console.log("se ", selectedOptionText);
      cy.wrap($option).click({ force: true });
      cy.get("body").then(($body) => {
        if (
          $body.text().includes("No Data Found from your Search/Filter") ||
          $body.text().includes("No request found") ||
          $body.text().includes("No Data Available in this Section") ||
          $body.text().includes("No records found") ||
          $body.text().includes("No Records Found")
        ) {
          cy.log("No data available");
        } else {
          cy.get(".tutor-fs-7.tutor-fw-medium.tutor-color-muted").each(
            ($announcement) => {
              cy.wrap($announcement)
                .invoke("text")
                .then((announcementText) => {
                  console.log("asd ", announcementText);
                  console.log("sele ", selectedOptionText);
                  expect(selectedOptionText).to.include(
                    announcementText.replace(/Course:\s*/g, "").trim()
                  );
                });
            }
          );
        }
      });
    });
  });
  it("Should filter announcements by a specific date", () => {
    const filterFormSelector =
      ".react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control";
    const elementDateSelector = "tbody>tr>td:nth-child(2)";
    cy.filterElementsByDate(filterFormSelector, elementDateSelector);
  });

  it("should perform bulk action on all annoucements", () => {
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No request found") ||
        $body.text().includes("No Data Available in this Section") ||
        $body.text().includes("No records found") ||
        $body.text().includes("No Records Found")
      ) {
        cy.log("No data found");
      } else {
        cy.get("#tutor-bulk-checkbox-all").click();
        cy.get(".tutor-mr-12 > .tutor-js-form-select").click();
        cy.get(
          ".tutor-mr-12 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis"
        )
          .contains("Delete Permanently")
          .click();
        cy.get("#tutor-admin-bulk-action-btn").click();
        cy.get("#tutor-confirm-bulk-action")
          .contains("Yes, I'am Sure")
          .click();
        cy.contains("No Data Available in this Section");
      }
    });
  });
});
