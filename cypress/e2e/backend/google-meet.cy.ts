import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GOOGLE_MEET}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.GOOGLE_MEET);
  });

  // set api and save connection
  it("should upload meet integration json and save connection", () => {
    const filePath = "/Users/ollyo/Documents/google-meet-integration.json";
    cy.get("body").then(($body) => {
      if ($body.text().includes("Drag & Drop your JSON File here")) {
        cy.get("#tutor-google-meet-credential-upload").selectFile(filePath, {
          force: true,
        });
      } else if ($body.text().includes("The app is not permitted yet!")) {
        cy.get("a")
          .contains("Go To Google's Consent Screen")
          .click();
      } else if ($body.text().includes("Reset Credential")) {
        cy.get(":nth-child(3) > .tutor-nav-link")
          .contains("Set API")
          .click();
        //   reset credentials
        cy.get("a")
          .contains("Reset Credential")
          .click();
        cy.get("button")
          .contains("Yes, I'am Sure")
          .click();
      }
    });
  });

  it("should start meeting", () => {
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No Data Available in this Section") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
        cy.get("a")
          .contains("Start Meeting")
          .invoke("removeAttr", "target")
          .click();

        cy.url().should("include", "/calendar");
      }
    });
  });

  it("should edit a google meeting", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_google_meet_new_meeting")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");

    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Records Found") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
        cy.get("a.tutor-btn.tutor-btn-outline-primary.tutor-btn-md")
          .contains("Edit")
          .eq(0)
          .click();

        cy.get("input[name='meeting_title']")
          .eq(0)
          .clear()
          .type("Edited test google meeting");

        cy.get("textarea[name='meeting_summary'")
          .eq(0)
          .clear()
          .type("Edited google meeting summary", { force: true });

        cy.get(
          ".tutor-gmi-meeting-time > :nth-child(1) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
        )
          .eq(0)
          .click();
        cy.get(".dropdown-years > .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-years .dropdown-list li")
          .contains("2025")
          .click();
        cy.get(".dropdown-container.dropdown-months .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-months .dropdown-list li")
          .contains("June")
          .click();
        cy.get(".react-datepicker__day")
          .contains("11")
          .click();

        cy.get("input[name='meeting_start_time']")
          .eq(0)
          .clear({ force: true })
          .type("09:30 PM");

        cy.get('input[name="meeting_end_time"]')
          .eq(0)
          .clear()
          .type("10:00 PM");
        cy.get("input[name='meeting_end_date']")
          .eq(0)
          .click();
        cy.get(".dropdown-years > .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-years .dropdown-list li")
          .contains("2025")
          .click();
        cy.get(".dropdown-container.dropdown-months .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-months .dropdown-list li")
          .contains("June")
          .click();
        cy.get(".react-datepicker__day")
          .contains("11")
          .click();

        cy.get('input[name="meeting_end_time"]')
          .eq(0)
          .clear()
          .type("10:00 PM");

        cy.get(".tutor-col-md-8 > .tutor-js-form-select")
          .eq(0)
          .click();
        cy.get(
          ".tutor-col-md-8 > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(108) > .tutor-nowrap-ellipsis"
        )
          .eq(0)
          .click();

        cy.get("input[name='meeting_attendees_enroll_students']").check({
          force: true,
        });
        cy.get("button")
          .contains("Update Meeting")
          .click();

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.request.body).to.include(
            "tutor_google_meet_new_meeting"
          );
          expect(interception.response.body.success).to.equal(true);
        });
      }
    });
  });

  it("should delete a google meeting", () => {
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_google_meet_delete")) {
          req.alias = "ajaxRequest";
        }
      }
    ).as("ajaxRequest");
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Records Found") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
        cy.get("a.tutor-iconic-btn")
          .eq(0)
          .click({ force: true });
        cy.get(
          "#tutor-common-confirmation-form > .tutor-d-flex > .tutor-btn-primary"
        ).click();
      }
    });
    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.request.body).to.include("tutor_google_meet_delete");
      expect(interception.response.body.success).to.equal(true);
    });
  });
  it("should be able to search any meeting", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "Google meet test";
    const courseLinkSelector =
      ".tutor-google-meet-meeting-item>td:nth-child(2)>div:first-child";
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
  it("should filter meetings", () => {
    cy.get(":nth-child(2) > .tutor-js-form-select").click();
    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Records Found") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
        cy.get(".tutor-form-select-options")
          .eq(1)
          .then(() => {
            cy.get(".tutor-form-select-option")
              .then(($options) => {
                cy.get(".tutor-form-select-options>div:nth-child(2)")
                  .eq(0)
                  .click();
              })
              .then(() => {
                cy.get("span.tutor-form-select-label[tutor-dropdown-label]")
                  .eq(0)
                  .invoke("text")
                  .then((retrievedText) => {
                    cy.get(
                      ".tutor-wp-dashboard-filter-item >.tutor-js-form-select >.tutor-form-select-dropdown >.tutor-form-select-options >.tutor-form-select-option >.tutor-nowrap-ellipsis"
                    ).each(($category) => {
                      cy.wrap($category)
                        .invoke("text")
                        .then((categoryText) => {
                          if (categoryText.trim() === retrievedText.trim()) {
                            cy.wrap($category).click({ force: true });
                          }
                        });
                    });
                  });
              });
          });
      }
    });
  });
  it("Should filter courses by a specific date", () => {
    cy.get(
      ".tutor-wp-dashboard-filter-items > :nth-child(3) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
    ).click();

    cy.get(".dropdown-years").click();
    cy.get(".dropdown-years>.dropdown-list")
      .contains("2025")
      .click();
    cy.get(".dropdown-months > .dropdown-label").click();
    cy.get(".dropdown-months > .dropdown-list")
      .contains("June")
      .click();
    cy.get(".react-datepicker__day--011")
      .contains("11")
      .click();

    cy.get("body").then(($body) => {
      if (
        $body.text().includes("No Data Found from your Search/Filter") ||
        $body.text().includes("No records found")
      ) {
        cy.log("No data available");
      } else {
        cy.wait(2000);
        cy.get(".tutor-fs-7 > span").each(($el) => {
          const dateText = $el.text().trim();
          expect(dateText).to.contain("June 11, 2025");
        });
      }
    });
  });
  // settings
  it("should save settings", () => {
    cy.get(":nth-child(4) > .tutor-nav-link")
      .contains("Settings")
      .click();
    cy.get(
      ".tutor-form-control.tutor-form-select.tutor-js-form-select"
    ).click();
    cy.get(".tutor-form-select-option")
      .contains("(GMT+6:00) Astana, Dhaka")
      .click();
    cy.get("input[value='30']")
      .check()
      .should("be.checked");
    cy.get("input[value='confirmed']")
      .check()
      .should("be.checked");
    cy.get("input[value='all']")
      .check()
      .should("be.checked");
    cy.get("input[value='opaque']")
      .check()
      .should("be.checked");
    cy.get("input[value='default']")
      .check()
      .should("be.checked");
  });
  //    help
  it("Should make corresponding elements visible when accordion is clicked", () => {
    cy.get(":nth-child(5) > .tutor-nav-link")
      .contains("Help")
      .click();
    cy.get(".tutor-accordion-item-header.tutor-card.tutor-mb-16").each(
      ($accordion, index) => {
        cy.wrap($accordion).click();
        cy.get(`.tutor-fs-7.tutor-color-secondary`)
          .eq(index)
          .should("be.visible");
      }
    );
  });
});
