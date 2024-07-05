import { backendUrls } from "../../config/page-urls";

describe("Tutor Dashboard My Courses", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.GOOGLE_CLASSROOM}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.GOOGLE_CLASSROOM);
  });

  //  set api and save connection
  it("should upload google classroom integration json and save connection", () => {
    const filePath = "/Users/ollyo/Documents/google-classroom.json";
    cy.get("body").then(($body) => {
      if ($body.text().includes("Drag & Drop your JSON File here")) {
        cy.get("button")
          .contains("Browse File")
          .click();

        cy.get('input[type="file"]')
          .should("exist")
          .selectFile(filePath, {
            force: true,
          });
        cy.get("button")
          .contains("Load Credentials")
          .click();
        // another option change credentials
      }

      if ($body.text().includes("Please complete the authorization process")) {
        cy.get(":nth-child(4) > .tutor-btn")
          .contains("Allow Permissions")
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

  it("should handle import, publish, and preview classes", () => {
    function getClassFromUrl(url) {
      const match = url.match(/\/courses\/([^\/]*)\//);
      return match ? match[1] : "";
    }
    cy.intercept(
      "POST",
      `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
      (req) => {
        if (req.body.includes("tutor_gc_class_action")) {
          req.alias = "ajaxRequest";
        }
      }
    );

    cy.get("body").then(($body) => {
      if ($body.text().includes("Please complete the authorization process")) {
        cy.log("Authorization needed");
      } else {
        cy.get("table tbody tr")
          .should("have.length.greaterThan", 0)
          .then(() => {
            cy.get("button")
              .contains("Import")
              .then(($importBtn) => {
                if ($importBtn.is(":visible")) {
                  cy.wrap($importBtn).click();
                  cy.get("#tutor-popup-reset")
                    .contains("Yes, Import Student")
                    .click();
                  cy.wait("@ajaxRequest").then((interception) => {
                    expect(interception.response.body.success).to.equal(200);
                  });
                } else {
                  cy.get("button")
                    .contains("Publish")
                    .then(($publishBtn) => {
                      if ($publishBtn.is(":visible")) {
                        cy.wrap($publishBtn).click();
                        cy.wait("@ajaxRequest").then((interception) => {
                          expect(interception.response.body.success).to.equal(
                            200
                          );
                        });
                      } else {
                        cy.get("a")
                          .contains("Preview")
                          .then(($previewLink) => {
                            if ($previewLink.is(":visible")) {
                              const previewUrl = $previewLink.attr("href");
                              cy.visit(previewUrl);
                              cy.url().should(
                                "include",
                                getClassFromUrl(previewUrl)
                              );
                            }
                          });
                      }
                    });
                }
              });
          });
      }
    });
  });

  it("should copy classcode", () => {
    cy.get("table tbody tr")
      .should("have.length.greaterThan", 0)
      .then(() => {
        cy.get(".tutor-copy-text")
          .eq(0)
          .click()
          .then(() => {
            cy.wait(500);
            cy.contains("Copied");
          });
      });
  });
  it("should copy classlist shortcode", () => {
    cy.get("table tbody tr")
      .should("have.length.greaterThan", 0)
      .then(() => {
        cy.get(".tutor-iconic-btn.tutor-mr-n8.tutor-copy-text")
          .click()
          .then(() => {
            cy.wait(500);
            cy.contains("Copied");
          });
      });
  });

  it("should be able to search any class", () => {
    const searchInputSelector = "#tutor-gc-search-class";
    const searchQuery = "test";
    const courseLinkSelector = "td.tutor-gc-title>a";
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


  //   it("should trash, delete, restore a class", () => {
  //     // cy.intercept(
  //     //   "POST",
  //     //   `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`,
  //     //   (req) => {
  //     //     if (req.body.includes("tutor_gc_class_action")) {
  //     //       req.alias = "ajaxRequest";
  //     //     }
  //     //   }
  //     // );
  //     // make it eq(0) later
  //     cy.get("button[data-action^='trash']").eq(2).click()
  //     // cy.wait("@ajaxRequest").then((interception) => {
  //     //   expect(interception.response.body.success).to.equal(200);
  //     // });
  //   });

  //   it("should perform bulk actions",()=>{
  //   })

});
