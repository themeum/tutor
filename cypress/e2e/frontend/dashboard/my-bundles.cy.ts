import { frontendUrls } from "../../../config/page-urls"

describe("Tutor Dashboard My Bundles", () => {
    beforeEach(() => {
        cy.visit(`${Cypress.env("base_url")}/${frontendUrls.dashboard.MY_BUNDLES}`)
        cy.loginAsAdmin() // Or Instructor
        cy.url().should("include", frontendUrls.dashboard.MY_BUNDLES)
    })

    it ("should create a bundle course", () => {
        cy.get("a.tutor-add-new-course-bundle").click()

        cy.getByInputName("title").type("My new bundle course")
        cy.getByInputName("post_name").type("my-new-bundle-course")
        cy.setTinyMceContent("#wp-course_description-editor-container", "Our Digital Marketing Mastery bundle covers vital areas like social media strategies, SEO, email marketing, content creation, and paid advertising on platforms like Google and Facebook.")
        cy.get("textarea[name=course_benefits]").type("Participants gain hands-on experience with Google Analytics for data analysis and conversion rate optimization techniques.")
        
        cy.get(".tutor-courses .tutor-js-form-select").click()
        cy.get(".tutor-courses .tutor-js-form-select .tutor-form-select-option").then(($products) => {
            if ($products.length > 1) {
                cy.get(".tutor-courses .tutor-js-form-select").click()
                cy.wrap($products).eq(1).click()
            }
            if ($products.length > 2) {
                cy.get(".tutor-courses .tutor-js-form-select").click()
                cy.wrap($products).eq(2).click()
            }
        })

        cy.get("button[name=course_submit_btn]").click()
        cy.get("body").should("contain.text", "Course bundle updated successfully!")
        cy.get("a[title=Exit]").click()
    })

    it("should visit a bundle product", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-course-name a").eq(0).then(($a) => {
                    cy.wrap($a).click()
                    cy.wrap($a).invoke('attr', 'href').then((link) => {
                        cy.url().should('eq', link)
                    })
                })
            }
        })
    })

    it("should delete a bundle product", () => {
        cy.get("body").then(($body) => {
            if ($body.text().includes("No Data Available in this Section")) {
                cy.log("No data found")
            } else {
                cy.get(".tutor-course-card button[action-tutor-dropdown=toggle]").eq(0).click()
                cy.get(".tutor-dropdown-parent.is-open a").contains("Delete").click()
                cy.get(".tutor-modal.tutor-is-active button").contains("Yes, Delete This").click()
                // @TODO: Assert if item was actually deleted or not
            }
        })
    })
})
