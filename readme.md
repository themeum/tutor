<img src=".github/tutor-github.png" alt="TutorLMS" width="100%"/>

## What is Tutor LMS?

Tutor LMS is a complete, feature-packed, and robust WordPress LMS plugin to easily create & sell courses online. All the features of this learning management system hit all the checkpoints for a full-fledged online course marketplace.

## <a id="wpcs"></a>WPCS configuration

<b>Step 1:</b> Please install these two composer package.

```
1. composer global require squizlabs/php_codesniffer
2. composer global require wp-coding-standards/wpcs
```

<b>Step 2:</b> Set WordPress as default coding standard. `(change your_username)`

```
phpcs --config-set installed_paths /Users/your_username/.composer/vendor/wp-coding-standards/wpcs
phpcs --config-set default_standard WordPress
```

<b>Problem Fix:</b>

If phpcs and phpcbf command not found as command, set it to your path variable.

`export PATH="/Users/your_username/.composer/vendor/bin:$PATH"`

## Development Setup

Follow the steps

1. [Configure WPCS](#wpcs) to write code that complies with the WordPress Coding Standard.
2. Clone the repository `git clone https://github.com/themeum/tutor.git`.
3. Checkout to `dev` brunch `git checkout dev`
4. Make your own brunch `git checkout -b your_brunch_name`
5. Go to vscode extension tab and install recommended extension.
6. Run `composer install` for install PHP dependency.
7. Run `npm install` for install js dependency.
8. Run `npm run watch`
9. Now open any SCSS file from assets directory and hit save.

## Contribution Guide

1. Make sure your code complies with the WordPress Coding Standard (WPCS) before open a PR.
2. Open a pull request (PR) to the `dev` branch. PRs to other branches will be closed without any discussion.

## Unit Testing

To run PHP unit testing in the development environment follow the below steps:

Go to the terminal then hit commands:

1. `composer install`
2. `bash bin/install-wp-tests.sh db_name user_name password host latest` ( [checkout details](https://make.wordpress.org/cli/handbook/misc/plugin-unit-tests/) )
3. `vendor/bin/phpunit --info`

If everything goes well then you should see the PHP unit info

## Running Cypress Tests

Follow these steps to run Cypress tests for the Tutor plugin:

### Prerequisites

1. Ensure Node.js is installed (v14+ recommended).
2. Install dependencies:
   ```bash
   npm install
   ```

### Running Tests

#### Open Cypress Test Runner

To open the Cypress interactive test runner:

```bash
npm run cy:open
```

This launches the Cypress UI where you can select and run tests interactively.

#### Run All Tests Headlessly

To run all tests in headless mode:

```bash
npm run test
```

#### Run Specific Test Files

To run specific test files:

```bash
npx cypress run --spec "cypress/e2e/backend/**/*.cy.ts"
```

#### Run Tests with a Specific Browser

```bash
npx cypress run --browser chrome
```

### Test Structure

- **E2E Tests**: Located in `cypress/e2e/` directory.
- **Backend Tests**: Located in `cypress/e2e/backend/`.
- **Frontend Tests**: Located in `cypress/e2e/frontend/`.
- **Course Builder Tests**: Located in `cypress/e2e/course-builder/`.
- **Fixtures**: Test data in `cypress/fixtures/`.

### Environment Configuration

1. Create a `.env` file in the project root based on the `.env.example` template:

   ```
   # WordPress site URL
   CYPRESS_base_url=<YOUR WORDPRESS SITE>

   # Admin credentials
   CYPRESS_admin_username=<ADMIN USERNAME>
   CYPRESS_admin_password=<ADMIN PASSWORD>

   # Zoom credentials
   CYPRESS_instructor_zoom_account_id=<YOUR ZOOM ACCOUNT ID>
   CYPRESS_instructor_zoom_client_id=<YOUR ZOOM CLIENT ID>
   CYPRESS_instructor_zoom_client_secret=<YOUR ZOOM CLIENT SECRET>
   ```

2. Ensure your WordPress test site is running with the Tutor plugin installed and activated.

### View Test Results

- **Test Reports**: Generated in the `cypress/reports` directory.
- **Screenshots**: Automatically saved to `cypress/screenshots` when tests fail.
- **Videos**: Saved in `cypress/videos` when running headlessly.

### Troubleshooting

- If tests fail with authentication errors, check your credentials in the `.env` file.
- Clear `cypress/screenshots` and `cypress/videos` directories if they become too large.
- If tests hang, try running them separately or increasing the timeout: `npx cypress run --config defaultCommandTimeout=10000`.

## System Requirements

1. PHP – 7.0 (or later)
2. Database – MariaDB – 10.1 or later / MySQL – 5.7 or later
3. WordPress 5.5 or higher
4. Browser – Chrome, Firefox, Safari
5. Internet Explorer is not supported
6. Server Modules – mod_rewrite, cURL, fsockopen

7. Download the free Tutor LMS 2.0 Beta file
   To get the free version of the Tutor LMS 2.0 :

8. Navigate to the Tutor LMS GitHub.
9. Download the Tutor 2.0 install zip file
10. Create a backup version of your entire site before you plan to install it. Ideal usage, we reiterate, would be to install it on a test site to see and try out the new features without breaking anything.
11. Download the [Pro Tutor LMS 2.0 Beta](https://www.themeum.com/account/downloads/) file
12. Follow along to download the pro version of Tutor LMS 2.0.

Note: The pro version is only available to the pro users of Tutor LMS and can only be availed by them. Check the previous section to download the free release if you do not own Tutor Pro.

Log in to your Themeum account and navigate to the downloads page.
Click on download under the release file and the zip file should start downloading for you.
Installing the Tutor LMS Beta Plugins (Both Free & Pro)
The installation process for both the free and pro versions is the same since we are installing a zip file. Once again we urge you to create a back of your site or use a test environment before going ahead with the installation.

<table>
    <tbody>
        <tr>
            <td align="center" width="33.3333%">
                <img src=".github/addons/content-drip.png" alt="Content Drip" height="100px" />
                <h4>Content Drip</h4>
                Unlock lessons by schedule or when the student meets specific condition.
            </td>
            <td align="center" width="33.3333%">
                <img src=".github/addons/enrollments.png" alt="Enrollments" height="100px" />
                <h4>Enrollments</h4>
                Take advanced control on enrollments. Enroll the student manually.
            </td>
            <td align="center" width="33.3333%">
                <img src=".github/addons/gradebook.png" alt="Gradebook" height="100px" />
                <h4>Gradebook</h4>
                Shows student progress from assignment and quiz
            </td>
        </tr>
        <tr>
            <td align="center" width="33.3333%">
                <img src=".github/addons/calendar.png" alt="Calendar" height="100px" />
                <h4>Calendar</h4>
                Allow students to see everything in a calendar view.
            </td>
            <td align="center" width="33.3333%">
                <img src=".github/addons/restrict-content-pro.png" alt="Enrollments" height="100px" />
                <h4>Restrict Content Pro</h4>
                Unlock Course depending on Restrict Content Permission.
            </td>
            <td align="center" width="33.3333%">
                <img src=".github/addons/quiz-import-export.png" alt="Quiz Export/Import" height="100px" />
                <h4>Quiz Export/Import</h4>
                Save time by exporting/importing quiz data with easy options.
            </td>
        </tr>
        <tr>
            <td align="center">
                <img src=".github/addons/pmpro.png" alt="Paid Memberships Pro" height="100px" />
                <h4>Paid Memberships Pro</h4>
                Maximize revenue by selling membership access to all of your courses.
            </td>
            <td align="center">
                <img src=".github/addons/tutor-assignments.png" alt="Assignments" height="100px" />
                <h4>Assignments</h4>
                Tutor assignments is a great way to assign tasks to students.
            </td>
            <td align="center">
                <img src=".github/addons/tutor-certificate.png" alt="Certificate" height="100px" />
                <h4>Certificate</h4>
                Students will be able to download a certificate after course completion.
            </td>
        </tr>
        <tr>
            <td align="center">
                <img src=".github/addons/tutor-course-attachments.png" alt="Course Attachments" height="100px" />
                <h4>Course Attachments</h4>
                Add unlimited attachments/ private files to any Tutor course
            </td>
            <td align="center">
                <img src=".github/addons/tutor-course-preview.png" alt="Course Preview" height="100px" />
                <h4>Course Preview</h4>
                Unlock some lessons for students before enrollment.
            </td>
            <td align="center">
                <img src=".github/addons/tutor-email.png" alt="E-Mail" height="100px" />
                <h4>E-Mail</h4>
                Send email on various tutor events
            </td>
        </tr>
        <tr>
            <td align="center">
                <img src=".github/addons/tutor-multi-instructors.png" alt="Multi Instructors" height="100px" />
                <h4>Multi Instructors</h4>
                Start a course with multiple instructors by Tutor Multi Instructors
            </td>
            <td align="center">
                <img src=".github/addons/tutor-prerequisites.png" alt="Prerequisites" height="100px" />
                <h4>Prerequisites</h4>
                Specific course you must complete before you can enroll new course by Tutor Prerequisites
            </td>
            <td align="center">
                <img src=".github/addons/tutor-report.png" alt="Report" height="100px" />
                <h4>Report</h4>
                Check your course performance through Tutor Report stats.
            </td>
        </tr>
        <tr>
            <td align="center">
                <img src=".github/addons/tutor-zoom.png" alt="Tutor Zoom Integration" height="100px" />
                <h4>Tutor Zoom Integration</h4>
                Connect Tutor LMS with Zoom to host live online classes. Students can attend live classes right from the lesson page.
            </td>
            <td align="center">
                <img src=".github/addons/google-classroom.png" alt="Google Classroom Integration" height="100px" />
                <h4>Google Classroom Integration</h4>
                Helps connect Google Classrooms with Tutor LMS courses, allowing you to use features like Classroom streams and files directly from the Tutor LMS course.
            </td>
            <td align="center">
                <img src=".github/addons/wc-subscriptions.png" alt="WooCommerce Subscriptions" height="100px" />
                <h4>WooCommerce Subscriptions</h4>
                Capture Residual Revenue with Recurring Payments.
            </td>
        </tr>
        <tr>
            <td align="center">
                <img src=".github/addons/wpml.png" alt="WPML Multilingual CMS" height="100px" />
                <h4>WPML Multilingual CMS</h4>
                Create multilingual courses, lessons, dashboard and more for a global audience.
            </td>
            <td align="center">
                <img src=".github/addons/notifications.png" alt="Notifications" height="100px" />
                <h4>Notifications</h4>
                Get notifications on frontend dashboard for specified tutor events.
            </td>
            <td align="center">
                <img src=".github/addons/buddypress.png" alt="BuddyPress" height="100px" />
                <h4>BuddyPress</h4>
                Discuss about course and share your knowledge with your friends through BuddyPress
            </td>
        </tr>
    </tbody>
</table>

## FAQ

1. Is it safe to use Tutor LMS 2.0 Beta on your production site?

   Tutor LMS 2.0 Beta is an experimental build meant for testing purposes. We strongly recommend you not to use Tutor LMS 2.0 Beta on your production sites. Use it on staging environments and remember to backup your entire website before updating.

2. Will I get any support for Tutor LMS 2.0 Beta?

   Since this is an experimental release and should not be used on production sites, we won’t be providing any support for Tutor LMS 2.0 Beta.

3. Where can I report bugs or provide feedback?

   In the description section, you’ll see a form with the text [Report an Issue](https://forms.gle/xHw7TQSLGAcmbySy9). In this form, you can fill in your issues or provide your feedback regarding the Beta release.
