import { backendUrls } from "../../config/page-urls";

describe("Tutor Admin Tags", () => {
  beforeEach(() => {
    cy.visit(`${Cypress.env("base_url")}/${backendUrls.TAGS}`);
    cy.loginAsAdmin();
    cy.url().should("include", backendUrls.TAGS);
  });

  it ("should create a tag successfully", () => {
      const tagName = "Blockchain";
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
          if (req.body.includes("add-tag")) {
              req.alias = "addTagAjax"
          }
      });
      cy.getByInputName("tag-name").type(tagName)
      cy.get("#tag-slug").type(tagName.toLowerCase())
      cy.get("textarea[name=description]").type("Blockchain tag for courses")
      cy.get("#submit").click()

      cy.wait("@addTagAjax").then((interception) => {
          expect(interception.response.statusCode).to.equal(200);
      });

      cy.get("body").then(($body) => {
          if (!$body.text().includes("A term with the name provided already exists in this taxonomy.")) {
              cy.get(".notice").should("include.text", "Item added.");
              cy.get(".wp-list-table tbody .column-name").should("include.text", tagName)
          }
      })
  })

  it ("should update a tag successfully", () => {
      cy.get(".wp-list-table tbody tr").eq(0).find("a").contains("Edit").click({ force: true })
      cy.get("textarea[name=description]").clear().type("Blockchain tag for courses updated")
      cy.get("input[type=submit]").click()

      cy.get(".notice").should("include.text", "Item updated.");

      cy.get(".notice a").contains("Go to Tags").click()
      cy.get(".wp-list-table tbody .column-description").should("include.text", "Blockchain tag for courses updated")
  })

  it("should be able to search any tag", () => {
      const searchInputSelector = "#tag-search-input";
      const searchQuery = "Blockchain";
      const courseLinkSelector = ".row-title";
      const submitButtonSelector="#search-submit";
      const submitWithButton=true;

      cy.search(searchInputSelector, searchQuery, courseLinkSelector,submitButtonSelector,submitWithButton);
    });

  it ("should delete a tag successfully", () => {
      cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
          if (req.body.includes("delete-tag")) {
              req.alias = "deleteTagAjax"
          }
      });
      cy.get("body").then(($body) => {
          if ($body.text().includes("No Data Available in this Section")) {
            cy.log("No data found");
          } else {
            cy.get(".wp-list-table tbody tr").eq(0).find("a").contains("Delete").click({ force: true })
            cy.wait("@deleteTagAjax").then((interception) => {
              expect(interception.response.body).to.equal("1");
          });
          }
        });

  })

  it("should perform bulk action", () => {
    cy.get("body").then(($body) => {
      if ($body.text().includes("No Data Available in this Section")) {
        cy.log("No data found");
      } else {
        cy.get("input[name='delete_tags[]']")
          .eq(0)
          .check();
        cy.get("#bulk-action-selector-top").select("delete");
        cy.get("#doaction").click();
        // Verify that the success message appears
        cy.get("#message > p").should("contain", "Items deleted.");
      }
    });
  });
});
