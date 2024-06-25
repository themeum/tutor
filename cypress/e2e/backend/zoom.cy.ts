import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.ZOOM}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.ZOOM);
  });
  it("should be able to search any meeting", () => {
    const searchInputSelector = "#tutor-backend-filter-search";
    const searchQuery = "New Zoom Meeting";
    const courseLinkSelector = ".tutor-zoom-meeting-item>td:nth-child(2)";
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
    cy.get(".tutor-form-select-options")
      .eq(1)
      .then(() => {
        cy.get(".tutor-form-select-option")
          .then(($options) => {
            cy.get(
              " .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(2) > .tutor-nowrap-ellipsis"
            )
              .eq(0)
              .click();
          })
          .then(() => {
            cy.get("body").then(($body) => {
              if (
                $body.text().includes("No Data Found from your Search/Filter")
              ) {
                cy.log("No data available");
              } else {
                cy.get("span.tutor-form-select-label[tutor-dropdown-label]")
                  .eq(1)
                  .invoke("text")
                  .then((retrievedText) => {
                    cy.get(
                      ".tutor-wp-dashboard-filter-item >.tutor-js-form-select >.tutor-form-select-dropdown >.tutor-form-select-options >.tutor-form-select-option >.tutor-nowrap-ellipsis"
                    ).each(($category) => {
                      cy.wrap($category)
                        .invoke("text")
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
  it("Should filter courses by a specific date", () => {
    cy.get(
      ":nth-child(3) > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
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
      if ($body.text().includes("No Data Found from your Search/Filter")) {
        cy.log("No data available");
      } else {
        cy.wait(2000);
        cy.get(".tutor-zoom-meeting-item>td>.tutor-fs-7").each(($el) => {
          const dateText = $el.text().trim();
          expect(dateText).to.contain("June 11, 2025");
        });
      }
    });
  });
  //   set api and save connection
  it("should set and save zoom api connection", () => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.ZOOM}&sub_page=set_api`);
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`
    ).as("ajaxRequest");
    cy.get("input[name='tutor_zoom_api[account_id]']")
      .clear()
      .type(`${Cypress.env("instructor_zoom_account_id")}`);
    cy.get("input[name='tutor_zoom_api[api_key]']")
      .clear()
      .type(`${Cypress.env("instructor_zoom_client_id")}`);
    cy.get("input[name='tutor_zoom_api[api_secret]']")
      .clear()
      .type(`${Cypress.env("instructor_zoom_client_secret")}`);
    cy.get("button#save-changes").click();

    cy.wait("@ajaxRequest").then((interception) => {
      expect(interception.response.body.success).to.equal(true);
    });
  });

  it("should start meeting", () => {
    cy.get("a.tutor-btn.tutor-btn-primary")
      .contains("Start Meeting")
      .invoke("removeAttr", "target")
      .click();
    cy.url().should("include", "zoom.us");
  });

  it("should edit a zoom meeting", () => {
    cy.intercept("POST", "/wordpress-tutor/wp-admin/admin-ajax.php").as(
      "ajaxRequest"
    );
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Found from your Search/Filter")) {
        cy.log("No data available");
      } else {
        cy.get("a.tutor-btn.tutor-btn-outline-primary.tutor-btn-md")
          .contains("Edit")
          .eq(0)
          .click();
        cy.get("input[data-name='meeting_title']")
          .eq(0)
          .clear()
          .type("Edited test zoom meeting");

        cy.get("textarea[data-name='meeting_summary'")
          .eq(0)
          .clear()
          .type("Edited zoom meeting summary", { force: true });
        cy.get(
          ".tutor-mb-12 > .tutor-v2-date-picker > .tutor-react-datepicker > .react-datepicker-wrapper > .react-datepicker__input-container > .tutor-form-wrap > .tutor-form-control"
        )
          .eq(0)
          .clear()
          .click();
        cy.get(".dropdown-years > .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-years .dropdown-list li")
          .contains("2025")
          .click();
        cy.get(".dropdown-container.dropdown-months .dropdown-label").click();
        cy.get(".dropdown-container.dropdown-months .dropdown-list li")
          .contains("May")
          .click();
        // Select the desired day
        cy.get(".react-datepicker__day")
          .contains("13")
          .click();
        cy.get("input[data-name='meeting_duration']")
          .eq(0)
          .clear()
          .type("1");
        cy.get('input[data-name="meeting_time"]')
          .eq(0)
          .clear()
          .type("08:00 PM");
        cy.get('select[data-name="meeting_duration_unit"]')
          .eq(0)
          .select("Hours");
        cy.get(
          "div[class='tutor-col-6'] div[class='tutor-form-control tutor-form-select tutor-js-form-select']"
        )
          .eq(0)
          .click();

        cy.get(
          ".meeting-modal-form-wrap > :nth-child(4) > :nth-child(1) > .tutor-js-form-select > .tutor-form-select-dropdown > .tutor-form-select-options > :nth-child(108) > .tutor-nowrap-ellipsis"
        )
          .eq(0)
          .click();
        cy.get("select[data-name='auto_recording']")
          .eq(0)
          .select("No Recordings");
        cy.get("input[data-name='meeting_password']")
          .eq(0)
          .type("1234");
        cy.get("button")
          .contains("Update Meeting")
          .click();
        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.statusCode).to.equal(200);
        });
      }
    });
  });

  it("should delete a zoom meeting", () => {
    cy.intercept("POST", "/wordpress-tutor/wp-admin/admin-ajax.php").as(
      "ajaxRequest"
    );
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Found from your Search/Filter")) {
        cy.log("No data available");
      } else {
        cy.get("a.tutor-iconic-btn")
          .eq(1)
          .click({ force: true });

        cy.get("button")
          .contains("Yes, Delete This")
          .click({ force: true });

        cy.wait("@ajaxRequest").then((interception) => {
          expect(interception.response.statusCode).to.equal(200);
        });
      }
    });
  });

  // settings
  it("should allow users to check checkboxes", () => {
    cy.get(":nth-child(4) > .tutor-nav-link")
      .contains("Settings")
      .click();
    cy.get('input[name="tutor_zoom_settings[join_before_host]"]')
      .check({ force: true })
      .should("be.checked");
    cy.get('input[name="tutor_zoom_settings[host_video]"]')
      .check({ force: true })
      .should("be.checked");
    cy.get('input[name="tutor_zoom_settings[participants_video]"]')
      .check({ force: true })
      .should("be.checked");
    cy.get('input[name="tutor_zoom_settings[mute_participants]"]')
      .check({ force: true })
      .should("be.checked");
    cy.get('input[name="tutor_zoom_settings[enforce_login]"]')
      .check({ force: true })
      .should("be.checked");
    // recording options
    cy.get("input#tutor_zoom_rec_local")
      .check()
      .should("be.checked");
    cy.get("input#tutor_zoom_rec_none").should("not.be.checked");
    cy.get("input#tutor_zoom_rec_cloud").should("not.be.checked");
    cy.get("input#tutor_zoom_rec_cloud")
      .check()
      .should("be.checked");
    cy.get("input#tutor_zoom_rec_none").should("not.be.checked");
    cy.get("input#tutor_zoom_rec_local").should("not.be.checked");
  });

  //   help
  it("Should make corresponding elements visible when accordion is clicked", () => {
    cy.get(":nth-child(5) > .tutor-nav-link")
      .contains("Help")
      .click();
    cy.get(".tutor-accordion-panel-handler-label").each(($accordion, index) => {
      cy.wrap($accordion).click();
      cy.get(`.tutor-fs-7.tutor-color-secondary`)
        .eq(index)
        .should("be.visible");
    });
  });
});
