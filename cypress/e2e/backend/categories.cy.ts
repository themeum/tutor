import { backendUrls } from "../../config/page-urls"

describe("Tutor Admin Categories", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${backendUrls.CATEGORIES}`)
        cy.loginAsAdmin()
        cy.url().should("include", backendUrls.CATEGORIES)
    })

    it ("should create a category successfully", () => {
        const categoryName = "Blockchain";

        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("add-tag")) {
                req.alias = "addCategoryAjax"
            }
        });

        cy.getByInputName("tag-name").type(categoryName)
        cy.get("#tag-slug").type(categoryName.toLowerCase())
        cy.get("textarea[name=description]").type("Blockchain category for courses")
        cy.get("#submit").click()

        cy.wait("@addCategoryAjax").then((interception) => {
            expect(interception.response.statusCode).to.equal(200);
        });

        cy.get("body").then(($body) => {
            if (!$body.text().includes("A term with the name provided already exists with this parent.")) {
                cy.get(".notice").should("include.text", "Item added.");
                cy.get(".wp-list-table tbody .column-name").should("include.text", categoryName)
            }
        })
    })


    it ("should update a category successfully", () => {
        cy.get(".wp-list-table tbody tr").eq(0).find("a").contains("Edit").click({ force: true })
        cy.get("textarea[name=description]").clear().type("Blockchain category for courses updated")
        cy.get("input[type=submit]").click()

        cy.get(".notice").should("include.text", "Item updated.");

        cy.get(".notice a").contains("Go to Categories").click()
        cy.get(".wp-list-table tbody .column-description").should("include.text", "Blockchain category for courses updated")
    })

    it ("should delete a category successfully", () => {
        cy.intercept("POST", `${Cypress.env("base_url")}/wp-admin/admin-ajax.php`, (req) => {
            if (req.body.includes("delete-tag")) {
                req.alias = "deleteCategoryAjax"
            }
        });

        cy.get(".wp-list-table tbody tr").eq(0).find("a").contains("Delete").click({ force: true })

        cy.wait("@deleteCategoryAjax").then((interception) => {
            expect(interception.response.body).to.equal("1");
        });
    })

    it("should be able to search any category", () => {
        const searchQuery = "test"
        // cy.get('#tag-search-input').type(`${searchQuery}{enter}`);
        // cy.get("#search-submit").click()

    cy.get('#tag-search-input')
      .type(searchQuery)
      .then(($input) => {
        if (Cypress.dom.isFocused($input)) {
          cy.get('#tag-search-input').type('{enter}');
        } else {
          cy.get("#search-submit").click();
        }
      });
  
        let count = 0;
  
        cy.get('.row-title').each(($link) => {
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

    
})
