# TutorLMS Changelog

### 1.9.7 - August 19, 2021

* New: Filters for instructor list in the backend dashboard
* New: Previous button for a single quiz (default enabled from settings)
* New: Pop up for review after course completion
* Update: "Add Option" button for "True/False" quiz is disabled by default
* Update: Redundant demo link for quizzes are removed 
* Update: Improved loading Icon while generating certificate
* Update: New "Go to home" button added for "Access Denied Page" for registration disable 
* Update: Zoom API key check and save combined
* Update: Meeting host dropdown made disabled since the always single host is used
* Fix: Course topic title and description editing issue
* Fix: Duplicate order statements in earning section of instructor profile
* Fix: Date Formats of Tutor LMS were not matched with set WordPress date format
* Fix: Translations issues for several internal texts
* Fix: PHP error for content drip add-on while trying to fetch property post_type of non-object
* Fix: Header footer shown twice while using Oxygen Tutor LMS integration


### 1.9.6 - August 04, 2021

* New: Popup alert message when students abandon a quiz
* New: Popup alert message when admin/instructor tries to delete an enrolment
* Update: Set WooCommerce product as sold individually when created automatically from frontend course builder
* Update: Enrolment required page design update
* Update: Notification design update for when WordPress registration is disabled
* Fix: Course retake button appears even if disabled from dashboard
* Fix: Pagination not appearing in quiz attempt
* Fix: Matching quiz layout CSS issue for long text
* Fix: Student can submit the quiz even after time limit is expired
* Fix: Email event list checkboxes not showing in dashboard if WPML add-on enabled
* Fix: Course progress not deleting when enrolment is deleted
* Fix: Quiz not showing immediately after creating in course builder
* Fix: Lesson count shows 0 in enrolled courses on the frontend dashboard
* Fix: XSS vulnerability in student list in the dashboard
* Fix: The option “When time expires” fixed in Quiz settings


### 1.9.5 - July 18, 2021

* New: WPML compatibility addon
* New: Course retake feature
* New: Quiz attempt filter in backend dashboard
* Update: Paid Membership Pro Integration architecture
* Fix: Assignment attachment not deleting from server when assignment is deleted


### 1.9.4 - July 13, 2021

* New: Zoom added to the Frontend dashboard
* New: Field to add Course Tags in Frontend course builder
* Update: Design update in Zoom backend dashboard
* Fix: Lesson video duration were not fetched for autofill URL
* Fix: Deprecated warnings in PHP 8


### 1.9.3 - June 23, 2021

* Update: Push Notification addon logo
* Fix: Some addons not showing up on the addon list


### 1.9.2 - June 21, 2021

* New: Push notification add-on
* New: Show a removable warning in WordPress admin dashboard if signup disabled
* Update: Updated design in the forgot password page
* Update: Enroll button text changed to 'Start Learning' for public courses 
* Update: Quiz question field placeholder text change
* Fix: Courses by other instructors now showing in course archives
* Fix: Instructor rejected notice is visible forever
* Fix: Last question in a quiz can be submitted without answering even if required
* Fix: Texts after double quote not showing in quiz info and question input field on edit
* Fix: Comma inside quiz title and description causes error in import/export
* Fix: Assignment metadata not saving while creating an assignment
* Fix: Some texts in email were not translatable
* Fix: XSS vulnerability in announcement summary


### 1.9.1 - June 04, 2021

* New: Enable/disable auto redirection to enrolled courses on auto order completion
* Fix: Parameter count in plugin activated hook
* Fix: Zoom meeting redirecting to post list while saving
* Fix: Students can no longer give feedback without selecting star rating


### 1.9.0 - June 02, 2021

* New: Assign different certificate templates to courses separately
* New: tutor_quiz/single/after/wrap hook added in single quiz template
* New: Video duration will be automatically fetched when inserting lesson videos
* New: Filter hook added to course filter
* New: Static texts in JS files are now translatable
* New: Automatic order completion redirects to Enrolled Courses
* Update: Button text changed for rejecting instructor requests
* Update: Default course count per page changed to 12 from 3
* Fix: Dashboard shortcode not working
* Fix: Wrong Q&A count in dashboard
* Fix: Student can Add to Cart from course archive even after the course is full
* Fix: SQL error in custom post types


### 1.8.10 - May 10, 2021

* New: Instructors can now give feedback on quiz attempts from the frontend
* Update: Security enhancements
* Fix: PHP notice error in new enrollment page
* Fix: User dashboard showing multiple times on Avada Theme
* Fix: Wrong percentage stats on course completion in report
* Fix: Custom template from 'Certificate customizer' plugin not working
* Fix: Quiz answers security issue fixed
* Fix: User display name doesn't update on certificate after name change
* Fix: Question/Answer reveal mode doesn't show anything for correct answer
* Fix: Profile Picture and bio disappearing after checkout
* Fix: Categories not showing hierarchically in course filter
* Fix: Private course not found on the latest version of WordPress
* Fix: CSS class typo
* Fix: Grammatical text error 


### 1.8.9 - April 28, 2021

* New: Option to disable detailed quiz attempt view from student profile
* Update: Improved license key submission form
* Update: Lessons, quizzes, and assignments removed from WordPress search
* Update: Security enhancement
* Fix: Instructor can see all other users' files in WP Media Manager
* Fix: Broken link of the password reset page
* Fix: Undefined variable notice in Zoom add-on
* Fix: HTML5 videos not playing in lessons
* Fix: 'Edit with frontend course builder' button visible for other instructors
* Fix: Incorrect Lesson order for duplicated courses
* Fix: 'View certificate' page conflict with BuddyBoss theme
* Fix: Empty data insert & redundant hooks calling on course attachments addons
* Fix: Empty meta data insert for lesson 
* Fix: Content drip option not loading in the classic editor's lesson editing page


### 1.8.8 - April 05, 2021 

* Fix: Patched Local File Inclusion vulnerability (Props to WPScan)

### 1.8.7 - April 01, 2021 

* Update: Improved Queuing system for Tutor LMS announcement and content drip email
* Fix: Users were not able to answer to a Q&A
* Fix: Custom player didn’t work for Vimeo
* Fix: The user-submitted reviews for the first time didn’t show up
* Fix: Line break in Quiz description not working
* Fix: An instructor couldn’t see his/her own courses if the preview was disabled
* Fix: Page not found for imported quizzes


### 1.8.6 - March 22, 2021 

* New: Search assignments by Student Name
* New: Toast message added after evaluating assignments
* New: Updated process for sending Announcement and Content Drip emails
* Fix: Instructors profile photos were not saving in the WordPress admin panel
* Fix: Import quizzes not working for Windows users due to file type error
* Fix: Disabling Content Drip email not working


### 1.8.5 - March 12, 2021

* Update: "Start Meeting" button will be disabled for expired meetings
* Fix: Editors with Instructor role can now edit all post types except other's courses
* Fix: Yoast conflict with Frontend Course Builder


### 1.8.4 - March 04, 2021

* New: Ordering option in Zoom meeting list
* New: Instructor list filter option in shortcode using attribute filter="on"
* Update: Tutor Pro plugin file size optimized from 6.2MB to 2.5MB
* Update: Quiz export/import file size limit waived
* Fix: 'Headers already sent' in course editor frontend
* Fix: Course Prerequisite is not limiting access to quizzes and assignments
* Fix: Quiz title not found in attempt review email
* Fix: Guzzlehttp conflict


### 1.8.3 - February 16, 2021

* Fix: Unexpected trailing comma error in PHP lower version


### 1.8.2 - February 15, 2021

* New: Certificate link added to the course completion email notification
* New: Settings added to enable/disable course enrollment expiration feature
* Update: Zoom meeting input date format updated to dd/mm/yyyy to avoid internal errors
* Update: If enrollment expiration is set to 0, you will see 'lifetime' on the frontend course page
* Update: Security features enhanced in wpdb query & Quiz. Thanks Wordfence!
* Update: Option added to enter decimal value in withdrawal request
* Update: Code optimization in the frontend Earnings report
* Fix: 404 console error due to min.map asset files
* Fix: An active PHP session was detected issue
* Fix: Division by 0 warning in Gradebook
* Fix: Zoom meeting list pagination issue


### 1.8.1 - February 05, 2021

* New: Course enrolment expiration
* New: Email Notification for course enrolment expiration
* Update: Improved frontend asset loading on Zoom Meetings
* Update: Confirmation message after saving Zoom Meetings
* Fix: Meeting access issue while using Zoom on Course Single Page
* Fix: Quiz not loading on the lesson page
* Fix: Max student count not saving


### 1.8.0 - February 03, 2021

* New: Search filter in Assignments
* New: Confirmation message while saving data added 
* New: Frontend and backend UX for Assignments redesigned
* New: Thank you notice when instructors submit a course for review added 
* Update: All the email notification templates moved to Pro version
* Update: HTML tag support in Quiz description added
* Update: Date time column in Student enrolment report added
* Update: User capability check in announcements Ajax action added
* Fix: Redundant or duplicate email issue when student enrolments are changed.
* Fix: Add New Course, Explore Addons buttons issue in Tutor LMS setup wizard
* Fix: Incorrect lesson count (due to trashed course) in the report page
* Fix: Profile and cover photo saving issue in frontend dashboard
* Fix: Graph issue in the Earnings tab in the Report addon
* Fix: Remove item issue in WooCommerce cart page
* Fix: Quiz fails to import issue


### 1.7.9 - January 21, 2021

* New: Setting to enable showing instructor name on the certificate
* Update: Announcement feature now has a separate menu for better management and avoid email trigger conflicts
* Update: Enhanced security features in all ajax requests and other necessary places. Thanks Wordfence!
* Update: Add WooCommerce subscription compatibility in the front end course builder
* Fix: Zoom meetings fail to update or save on sites running languages other than English
* Fix: Preview button was not working for courses in Admin Dashboard
* Fix: Multiple email notification issue upon manual enrolment
* Fix: Issue while attaching multiple files in assignments
* Fix: Lesson add/edit access issue for multi instructors
* Fix: Improved navigation for Course Reports page
* Fix: Translation issue in Paid Membership Pro
* Fix: User access issue in Zoom meeting list


### 1.7.8 - January 07, 2021

* Update: Quality of certificates' PDF files improved
* Update: A clear all filter button on the course filter page added
* Update: Monetization activation issue while deactivating WooCommerce plugin
* Fix: Course lesson and assignment auto-draft issue in course builder
* Fix: PDF certificate not downloading on Firefox web browser
* Fix: Wrong course eligibility status on Paid Membership Pro plugin activation
* Fix: Conjunction sign "&" not working issue in the assignments description section
* Fix: Courses in draft status showing issue while showing category lists from the archives
* Fix: CSS issue while selecting quiz time limit.


### 1.7.7 - December 30, 2020

* New: More options to control button colors in login, registration, and enrolment
* Update: Enhanced security features in gradebook and other necessary places. Thanks Wordfence!
* Update: Translation support for several of static strings added
* Fix: Fatal error on course archive and shortcode page for misconfigured monetization settings
* Fix: Auto-save email notification issue on frontend course builder
* Fix: Lesson preview access issue for admin


### 1.7.6 - December 04, 2020

* New: Added translation support on email notification templates.
* Update: Q&A timestamp is now aligned with WordPress timezone settings.
* Update: Student notification emails now utilise the BCC field for better privacy.
* Fix: Instructor profile is now responsive on all devices.
* Fix: Backslashes issue in quiz module.


### 1.7.5 - December 01, 2020

* New: Introducing customizable shortcode to showcase your instructors in a list 
* New: Upload a cover photo for the public profile of a user
* New: Public Profile is getting a brand new design
* Update: Withdrawal and Phone number fields now accept numbers only.
* Update: {site_url} and {site_name} attributes added to all Tutor Email addon emails.
* Update: Added certificate view page, template override option.
* Fix: Add to Cart option still showed after adding the product in the Course Details Page
* Fix: Showed wrong information for multiple pages for the Students list in the Dashboard  →Report →Courses →Course Details section.
* Fix: Student's name didn't show up when downloading the certificate as a PDF.
* Fix: Grammatical corrections in the enrolment section of the course page.
* Fix: The course was displaying free even when it was part of a membership package.
* Fix: Course dependent on Paid Membership plugin got canceled after a user enrolled in it.
* Fix: Backslashes issue in quiz question answer title multiple type question answer titles
* Fix: Conflict issue with BuddyPress, BuddyBoss that showed 404 page


### 1.7.4 - November 09, 2020

* New: 4 new email notifications for withdrawal requests.
* New: 3 new email notifications for content drip published lessons, quizzes and assignments.
* New: 3 new email notification for instructor registration management system.
* New: Filter hook to modify sub nav menu of settings page in frontend dashboard.
* New: Assignment evaluation status column in the frontend dashboard.
* New: You can now enable or disable specific course filter options.
* Update: Show 'Continue Course' instead of 'Add to Cart' (if already purchased) on the course archive page
* Update: Added 'course_filter' and 'column_per_row' attributes in 'tutor_course' shortcode
* Update: Withdrawal page design improvement
* Fix: Course URL not showing after course completion in BuddyPress post.
* Fix: Compatibility issue with Divi Builder.


###  1.7.3 - October 21, 2020

* Update: Added option to turn on course archive filters


###  1.7.2 - October 21, 2020

* New: Course filter option in Course Archive page
* New: Public Course/Private course option
* New: Now student will get email notification after his enrollment
* Update: Hide Become Instructor button if someone’s already applied
* Update: Enable/disable Video source and choose default video source option
* Update: Email Notification after instructor sign-up (Now admin will receive mail notification)
* Update: Assignment page with new deadline notice, expired message and more.
* Update: Instructor Request page with new prompts
* Fix: Quiz submission email recipients
* Fix: Enrolment email notification issue
* Fix: Assignment submission email recipients
* Fix: Canceling order does not update the student count on instructor dashboard
* Fix: Fatal Error in the Sales tab of Reports Addon
* Fix: RTL layout issue in Add-on list
* Fix: Color settings
* Fix: Minor text issues


###  1.7.1 - October 09, 2020

* New: REST API
* New: Zoom Integration
* New: Google Classroom Integration
* New: 3rd Party Google reCaptcha plugin support
* Fix: Compatibility issue with WCFM
* Fix: Quiz attempt review issue
* Fix: Course duplication issue
* Fix: Assignment search issue


### 1.7.0 - September 04, 2020

* New: Set commission per instructor
* New: Instructor signature on the certificate
* New: Duplicate any course from the Tutor LMS course list
* New: Settings to disable certificate in a single course
* New: Settings to disable Q&A on a specific course
* New: Force download for course attachment
* New: Compatibility with the GeneratePress theme
* Fix: Review issue for answers regarding Open Ended/Essay questions
* Fix: Wrong percentage was showing on quiz results
* Fix: Course got published automatically
* Fix: Deprecated unparenthesized method

### 1.6.9 - August 20, 2020

* New: 13 new email notification
* New: Added 6 new action hooks
```
do_action('tutor_after_student_signup', $user_id);
do_action('tutor_enrollment/after/cancel', $enrol_id);
do_action('tutor_enrollment/after/delete', $enrol_id);
do_action('tutor_enrollment/after/complete', $enrol_id);
do_action('tutor_announcements/after/save', $announcement_id);
do_action('tutor_quiz/attempt/submitted/feedback', $attempt_id);
```
* New: Added helper method `tutils()->get_enrolment_by_enrol_id($enrol_id)` to get enrollment details by enrolid
* New: Added helper method `tutils()->get_student_emails_by_course_id($course_id)` to get array list of enrolled user emails
* Update: User can disable Tutor LMS native login system
* Update: ImageMagick dependency removed from certificate generator
* Update: Option added to control course content access for instructors and administrators
* Update: Topic Summary toggle option added in course page
* Fix: Certificate Unicode fonts issue
* Fix: Certificate image generation issues
* Fix: Issue with deleting reviews from Reports addon
* Fix: Lesson page access issue for administrator 
* Fix: CSS issue in back-end course builder
* Fix: SQL syntax error on course page

### 1.6.8 - July 30, 2020

* Fix: WooCommerce enrollment issue

### 1.6.7 - July 28, 2020

* Update: Admins/instructors can view their course content from the front-end
* Update: Add dynamic template support for Course Prerequisites
* Update: Add action hook "tutor_after_review_update" in Ajax class
* Fix: Enrollment issue while updating EDD payment status
* Fix: Enrollment issue in WooCommerce manual order
* Fix: Certificates translation issue
* Fix: Login redirect issue

### 1.6.6 - July 15, 2020

* New: Student/instructor profile completion
* Update: Delete all related course data when permanently deleting a course
* Update: Course status Publish to Published in instructor's my course panel
* Update: Server-side validation in the Q&A tab
* Update: WordPress date format support in certificates
* Update: Improved Dashboard sidebar menu
* Fix: Quiz restart issue after completion when Quiz Auto Start is enabled
* Fix: Maximum Students limit wasn’t working for manual enrollment
* Fix: Quick edit vanishes course metadata (Intro video, Benefits, Requirements, Targeted Audience, Materials Included)
* Fix: Incorrect quiz result issue for randomized multiple choice question answers
* Fix: Tutor Instructor user role update issue
* Fix: Unanswered question count issue

### 1.6.5 - July 2, 2020

* New: Sales & Students report for the Report Add-on(Pro)
* Update: New student column in quiz attempt on frontend dashboard
* Remove: 'Mr.' text removed from the content of all email notifications
* Fix: Quiz retry and reveal mode error for certain types of quizzes
* Fix: Resource issue from the course page
* Fix: Logo image size issue for frontend course builder
* Fix: Couldn't stop loading certain tutor CSS/JS
* Fix: Blank attachment & prerequisites while updating course using quick edit
* Fix: EDD purchase history in frontend dashboard
* Fix: Quiz import file size limit increased
* Fix: CSV quiz import issue for Windows OS

### 1.6.4 - June 15, 2020

* New: Quiz Feedback system
* New: Now instructor can manage Q&A from the frontend dashboard
* Update: Quiz attempt and my quiz attempt icon
* Update: Quiz attempt re-designed
* Remove: Unnecessary css file from certificate addon
* Fix: {enroll_time} parameter issue in Q&A email notification template
* Fix: Special Character & Content issue in Q&A email notification template
* Fix: Maximum Number of students for Course Enrolment

### 1.6.3 - 20 May, 2020

* New: Now students can answer Q&A from the frontend
* New: Set answer word limit for Open-Ended and Essay type questions
* New: Replace the login error message for an incorrect password with your own custom message
* Fix: Randomize feature not working for answering options
* Fix: Close login popup without show any error message.
* Fix: Password/username validation messages and UX
* Fix: Social share content issue.
* Fix: Course duration and video playback time validation.

### 1.6.2 - 14 May, 2020

* Added: Display Name preference on the certificate, added settings to Dashboard > Settings > Profile
* Added: Custom HTML support for quiz question description
* Added: Quiz status pending if contains open ended and short questions.
* Fixed: Tutor LMS > Enrollments is showing same date for all items
* Fixed: Does not show 11th course on Dashboard
* Fixed: Check file extension while uploading the video
* FIxed: Wrong link in withdraw preference in dashboard
* FIxed: What Will I Learn section is hidden for enrolled students
* Fixed: Missing translations in dashboard
* Update: Course status 'Publish' to 'Published' in dashboard

### 1.6.1 - 29 April, 2020

* Added: Course Completion Process. Flexible and Strict mode. in strict mode, students have to complete all lessons and pass all quizzes in order to complete any course.
* Added: Quiz question validation, all type of quiz are now under validation except quiz ordering type
* Added: hook, `tutor/course/enrol_status_change/after`
* Added: utils method, `get_course_by_enrol_id($enrol_id)`;
* Added utils method, `course_enrol_status_change($enrol_id = false, $new_status = '')`;
* Improved: curriculum content add button group design, now it's link style button.
* Improved: course completion progress bar, is now counting quiz attempts and assignment. It will show 100% progress when you done all lessons, quiz, and assignments.
* Fixed: Tutor is sending the Course Enrollment email to the instructor without completing the payment
* Fixed: quiz question description div print even if the description is not exist


### 1.6.0 - 16 April, 2020

* New: Quiz Export/Import add-on (Pro)
* Added: Method `tutils()->cancel_course_enrol()`
* Added: Function `is_single_course();`
* Added: Action hook `do_action('tutor_new_instructor_after', $user_id);` at register instructor and apply as an instructor
* Added: action hook `do_action(“tutor_course_builder_before_quiz_btn_action”, $quiz_id);`
* Improved: Certificate generator PDF compatibility with PHP 7.3
* Removed: Options from the _tutorobject JavaScript Variable
* Fixed: Email notification showing raw variable {instructor_username}
* Fixed: Paid Membership Pro expiration issue
* Fixed: ability to empty and deleting empty additional meta fields from the post_meta.
* Fixed: Earning option enable if the marketplace was selected during the setup wizard

### 1.5.9 - 08 April, 2020

* Fixed: Dashboard > Earning > Statements link fixed from Earning page
* Fixed: Course author flag, the flag will be only author name besides.
* Fixed: Instructor search options
* Fixed: Capability to add more than 10 Instructors
* Fixed: Fill the gap quiz option is not accepting capital letters
* Fixed: Perfect report showing from starting day to ending day, scenario: Last Month, This Month, Last Week, This Week, Date Range.
* Fixed: Allow Publishing Course option is not working in Gutenberg editor backend
* Fixed: Quiz image matching question image covering the matching words issue
* Fixed: Withdrawal timezone issue
* Fixed: One instructor can view others withdrawal amount
* Fixed: (Oxygen Builder Integration) Student getting Instructor dashboard
* Fixed: few spelling issue

### 1.5.8 - 31 March, 2020

* Updated: Wishlist will now show Tutor LMS popup login form to non-logged users
* Update: No more course price in a single course if the course already enrolled.
* Fix: Set value 0 to show default value in option panel
* Fix: Auto-assign Admin while approving pending course at Gutenberg editor
* Fix: Admin added as an instructor by default in courses
* Fix: Disable review option working properly, disabled course review form
* Fix: Quiz Attempts end time
* Fix: Quiz Attempts answers order
* Fix: Wrong calculation when enabled fees deduction before instructor and admin share divide.
* Deprecated: tutor_archive_course_add_to_cart() from v.1.5.8
* Removed: "tutor_archive_course_add_to_cart()" from the "plugins/tutor/templates/archive-course.php". Please update if you had overridden this file to your theme.

### 1.5.7 - 19 March, 2020 =

* New: Setup Wizard for faster and smoother launch
* New: get_tutor_all_withdrawal_methods() function to get all available withdrawal methods
* Update: Optimized database query on get_course_first_lesson() on Utils
* Update: Show/hide withdrawal requests and Instructors menu from Tutor Admin Menu based on enable/disable course marketplace
* Fix: Timezone issue at quiz start and the remaining time
* Fix: Few text domains added for translation support
* Fix: Continue to Lesson button issue when 100% of the course is complete

### 1.5.6 - 06 March, 2020

* New: Feature image support for lessons
* Fix: Courses per page issue on course archives in Divi Builder
* Fix: Search issue on custom course archive pages
* Fix: Redirect to next lesson issue after completing lessons (headers sent)

### 1.5.5 - 27 February, 2020

* New: Custom links now supported in Dashboard menu
* New: Dashboard headers added in shortcode/oxygen dashboard template
* Update: All purchase history items now sorted by descending order
* Fix: Create new account translate issue. templates/global/login.php line number 79
* Fix: Dashboard page load and logout issue by Shortcode
* Fix: Guest add to cart issue on course archive page for both WooCommerce and EDD
* Fix: Course Settings single tab toggle click hide issue fix
* Fix: Active links with page in the dashboard for shortcode/oxygen
* Fix: E-Mail to Students on Quiz Completed is now working (Pro)

### 1.5.4 - 11 February, 2020

* Fix: An infinite loop in frontend course builder for SEO related plugin activation: `classes/Shortcode.php`line number `53`
* Fix: Maintenance mode override for wp-login.php page
* Improvement: Frontend dashboard performance

### 1.5.3 - 04 February, 2020

* Added: Go auto next after finish lesson (When no video)
* Added: Nonce field at add instructor form to determine that request comes from the dedicated page.
* Updated: Instructor approved/blocked by ajax request in post method with the nonce check (Security Update)

### 1.5.2 - 29 January, 2020 

* Tutor LMS plugin is now running under 'plugins_loaded' hook (Architectural update)
* Added: hide frontend admin bar based on the option check
* Updated: Preview lesson got the full view with unenrolled behavior (Pro)
* FIxed docs links in edit quiz modal in the quiz builder
* Fixed: reviews database query reviews now query with or without user ID
* Fixed: wishlist query, added where post type = courses and post_status = publish
* Fixed: ask the question from assignments and quiz page.
* Fixed: permission issue for auto-installation tutor plugin

### 1.5.1 - 08 January, 2020

* Fixed an update during lesson content from modal

### 1.5.0 - 06 January, 2020

* Certificate verification public URL (Pro)
* Added: filter `apply_filters('tutor_courses_base_slug', $course_post_type)`
* Added: fitler `apply_filters('tutor_lesson_base_slug', $lesson_post_type)`
* Fixed: lesson editor content post issue, some content sometime not saved before.
* Fixed: file system issue after update option hook, while create maintance mode file.

### 1.4.9 - 24 December, 2019

* Added: BuddyPress Integration (TutorLMS Pro)
* Added: Go next lesson after ending video lesson
* Added: Hide course-product from shop page.
* Added: action hook `do_action('tutor_quiz/start/before', $quiz_id, $user_id);`
* Added: action hook `do_action('tutor_quiz/start/after', $quiz_id, $user_id, $attempt_id);`
* Added: filter `apply_filters('is_completed_course', $is_completed, $course_id, $user_id)`

### 1.4.8 - 10 December, 2019

* Added: Restrict Content Pro Integration (TutorLMS Pro)
* Added: Course Details Page elements enable / disable
* Added: action hook `do_action( "tutor_save_course_after", $post_ID, $post);`
* Added: action hook `do_action('tutor/course/started', $course_id);`
* Added: action hook `do_action('tutor/lesson/created', $lesson_id);`
* Fixed: implode parameter in utils `utils()->get_total_quiz_attempts_by_course_ids()`;


### 1.4.7 - 28 November, 2019

* Added: Next Previous Lesson|quiz|assignments
* Added: User Profile Update From Backend and Frontend Dashboard is now synced, no matters it's from media or browser file input.
* Added: Quiz description implementation
* Added: `tutor_single_quiz_content()` to get quiz description within Standard Loop

### 1.4.6 - 11 November, 2019

* Added: Maintenance Mode
* Added: Frontend course edit link from Course Edit Page Admin bar LINK
* Fixed: bug during add topic in course builder
* Fixed: certificate download fatal error related microtime() in tutor-pro version

### 1.4.5 - 21 October, 2019

* Added: Disable Course Review option
* Updated: removed media uploader on dashboard profile photo upload added native file upload system.
* Updated: Time GMT from WordPress settings
* Fixed: course start and continue to lesson order fixed

### 1.4.4 - 16 October, 2019

* Fixed: quiz question sortable sorting item
* Improved: quiz draggable answer drop accessibility
* Fixed: prevented fatal error in single quiz question with no option and trying to finish quiz.

### 1.4.3 - 11 October, 2019

* Added: Reset Password, Tutor LMS native login system
* Added: re-generate tutor pages, create new page if any issue on those page.
* Added: Added quiz attempt view by student (TutorLMS Pro)
* Added: function `tutor_action_field()` to generate tutor action field within form
* Added: Instructor can delete submitted assignment.
* Added: `tutor_redirect_back()` Redirect to back or a specific URL and terminate the script.
* Added: `tutor_get_template_html( $template_name, $variables = array() );` function, it will return view as HTML code, usefull for E-Mail
* Added: Frontend Course Builder Page Logo Upload Option, Settings > General > Tutor LMS Pro Settings
* Updated: Options is now expandable, pass additional options params to  filter `tutor/options/extend/attr`
* Updated: User Login controlling by Tutor
* Updated: media upload field improved with media delete option
* Deleted: Quiz Deprecated code.
* Fixed: Touch supports for quiz Sortable / draggable items
* Fixed: Quiz matching question type repeated issue
* Fixed: Admin gets automatically added when review course
* Fixed: Instructor total course does not update after deleting a course
* Fixed: Updated time to tutor_time() to get WordPress UTC time

### 1.4.2 - 25 September, 2019

* Added: Greadbook Addons in the Tutor LMS Pro version
* Added: action hook after quiz attempt end, `do_action('tutor_quiz/attempt_ended', $attempt);`, `do_action('tutor_quiz/attempt_analysing/before', $attempt);`
* Added: Assignment submmiting / submitted flag to lesson sidebar in lesson single page
* Added: Template support from Tutor Pro
* Added: action hook after addon enable disable,
do_action('tutor_addon_before_enable_disable');
do_action('tutor_addon_before_enable', $addonFieldName);
do_action('tutor_addon_after_enable', $addonFieldName);
do_action('tutor_addon_before_disable', $addonFieldName);
do_action('tutor_addon_after_disable', $addonFieldName);
do_action('tutor_addon_after_enable_disable');
* Updated: Tutor Settings page URL is now updating when change settings page
* Fixed: Rating placing issue, sometime it missed rating value, but it will never hapen again.
* Fixed: `.tutor-icon-angle-left` `.tutor-icon-angle-right` toggle during lesson single sidebar show hide
* Updated: Addon icons


### 1.4.1 - 10 September, 2019

* Added: utils method `get_course_settings($course_id = 0, $key = null, $default = false)`
* Added: `get_tutor_course_settings($course_id = 0, $key = null, $default = false);`
* Added: Content Drip Addon (Pro)
* Added: Course settings with developer support
* Added: tutor_alert() function to print various type of alert, warning, success, danger

### 1.4.0 - 30 August, 2019

* Added: Page builder support on the lesson.
* Added: Enrollments list on in admin area, admin can now cancel, approve enrolment or delete. (Pro)
* Added: Manually enrollment student to a course  (Pro)
* Added: students quiz attempts on the frontend
* Added: Sticky lesson sidebar on the spotlight mode
* Added: Course permalink on the dashboard Most Popular Courses
* Added: Quiz Questions Order settings, Student answer to quiz questions as per order. 4 types of order, (random, sorting, asc, desc)
* Added: Redirect to the current course page after register.
* Added: Review update from dashboard > review > Given
* Added: Received reviews on all courses in dashboard
* Added: Assignment submission validation for the answer.
* Added: Quiz question options validation and correct answer validation
* Fixed: save and continue to click on question edit modal, open question lists immediately under a quiz.
* Fixed: text formatting issue in the quiz, some of the single or double quote comes with a slash. used `stripslashes();`
* Fixed: some default value issue on the quiz.
* Fixed: quiz page spotlight mode.
* Fixed: Multiples text translation issues
* Fixed: Chart-js Initiate in the pro version
* Fixed: Report Datepicker css/js in the frontend dashboard
* Fixed: Rating delete issues, it was actually report.js loading issues
* Fixed: Multiple E-Mail template loading issue
* Updated: Turned off reload the page while tutor settings save.

### 1.3.9 - 19 August, 2019

* Fixed: Resetting Paid Membership Pro plugins settings when saving tutor settings.
* Fixed: quiz question issue when no settings saved.
* Fixed: Complete lesson button responsive issue

### 1.3.8 - 09 August, 2019

* Fixed: `tutor_get_template()` function, it's now checking template from child-theme also, if template not found in the child theme, then it will look from the parent theme.
* Fixed: Show/Hide browse Q&A based on settings.
* Fixed: create_certificate under init hook from Tutor Certificate Addon (Pro)

### 1.3.7 - 08 August, 2019

* Added: WooCommerce Subscriptions Addon in the pro version
* Added action hook `do_action('tutor_is_enrolled_before', $course_id, $user_id);` and filter hook `apply_filters('tutor_is_enrolled', $getEnrolledInfo, $course_id, $user_id);`
* Added: user social links ability from frontend dashboard
* Fixed: Quiz attempt allowed 0 issue has been fixed. Strictly checking if it's zero, then it will be no limit
* Fixed: Tutor LMS Pro addon loading issue on the windows machine
* Fixed: query public profile user by user_nicename instead of user_login
* Fixed: touch event on click Lessn and Q&A navigation in mobile
* Updated: student public profile design updated

### 1.3.6 - 05 August, 2019

* Added: Centralized course monetization partner system, it's moved to under monetization tab in the settings. A centralized system to select course selling platform
* Added: Tutor Assignments on the free versions add-on lists to notify users
* Added: a function `get_tutor_option($key = null, $default = false)` to get tutor option, an alies of `tutils()->get_option($key, $default);`
* Added: a function `update_tutor_option($key = null, $value = false)` to update tutor option, an alies of `tutils()->update_option($key, $value);`
* Added: Paid Membership Pro for subscription plan (Pro)
* Fixed: WC Notice print when adding to cart course
* Fixed: Single course template loading issue

### 1.3.5 - 29 July, 2019

* Added: Direct publish course from frontend course builder if the current user is an administrator
* Added: a helper method to get course type `tutils()->price_type()`
* Added: course type (free or paid) to the course editor from
* Added: Filter to change template path apply_filters('tutor_get_template_path', $template_location, $template)
* Updated: `is_course_purchasable()` is now checking if there is any course type. if it free, then it will return false under 'is_course_paid' filter
* Fixed: add to wishlists, it didn't work on multiple courses wishlists
* Fixed: duplicate entry for the topic in the course builder

### 1.3.4 - 23 July, 2019

* Added: Frontend Drag and Drop Course Builder with quiz builder and assignment creation options
* Added: Lifetime deal license checking and validation to get regular update and pro features in the pro version.
* Added: add new instructor action hook and filter hook,
`do_action('tutor_add_new_instructor_form_fields_before'); `, `do_action('tutor_add_new_instructor_form_fields_after');`, `do_action('tutor_add_new_instructor_before');`, `do_action('tutor_add_new_instructor_after', $user_id);`, `apply_filters('add_new_instructor_data', $userdata)`
* Added: Dashboard subpage and dashboard menu item load permission basis
* Added: Topic toggle in lesson single page and information about toggle icon added
* Added: Course content is now linkable on the enrolled course page
* Added: Filter `apply_filters('get_tutor_load_template_variables', $variables);` at `tutor_load_template()` function
Added: action hook `do_action('tutor_load_template_before', $template, $variables);`, `do_action('tutor_load_template_before_after', $template, $variables);` at `tutor_load_template()` function
* Added: create and attached product with course while creating a course from frontend
* Added: Full-Screen Mode, students now can learn the lesson in full-screen mode without any inturruption.
* Added: Enable disable settings for YouTube and Vimeo Video default player
* Added: a new helper functions called `tutils()`, it's alies of `tutor_utils()`
* Fixed: complete lesson button from mobile view.
* Updated: Tutor frontend dashboard menu title now could be string or array with the `show_ui` key in the`array()`, show_ui key will be true or false to show it in the menu item visible or not.
* Fixed: Total Enrolled count in course details page
* Fixed: Course content looping count, lesson exact count by course content in the lesson.
* Fixed: some addon loading issue, tutor pro classes autoloading issue"
* Fixed: Delete video meta when select none from the video source in the course and lesson option.

### 1.3.3 - 21 June, 2019

* Added: Tutor Assignment Addon in the pro version
* Added: Upgrade to pro text in plugin action links if Tutor Pro LMS does not exist or not installed
* Added: tutor_assignments course post type
* Added: `tutor_course_contents_post_types` course content filter
* Rename: categories to course categories
* Updated: some template hook modified
* Fixed: tutor pro function checking `function_exists('tutor_pro');`
* Fixed: `wp_enqueue_editor()` in Assets class frontend frontend_scripts method conflict issue with divi builder

### 1.3.2 - 29 May, 2019

* Added: centralized tutor version upgrading system by a dedicated class called `Upgrader`
* Added: add to cart guest mode in WooCommerce integration based on Tutor LMS > settings > woocommerce
* Added: Gutenberg support on course edit page based on settings
* Added: Automatic free plugin install from pro when the pro version is activated but the free plugin does not exist (pro)
* Added: nice notice bar for install or activate free version from pro (pro)
* Added: Pro text in the tutor LMS dashboard menu when Tutor Pro plugin installed and activated
* Updated: a design for course listing, removed hover and moved it to straight course gird footer
* Fixed: a Gutenberg bug related post author on course post type, usually post_author column saved 0 if course edit with Gutenberg, we fixed this issue.
* Fixed: a template condition in the course description
* Removed: license restriction for features, it required now for auto-update (pro)
* Fixed: Response design all issue fixed
* Fixed: array count check and return issue in utils
* Fixed: a bug in dashboard/settings, withdraw menu removed from subscriber/users dashboard, and only shown in the instructor's dashboard.

### 1.3.1 - 22 May, 2019

* Added: status in the purchase history list
* Added: Shortcode for the coruse query, full shortcode `[tutor_course id="20,64" exclude_ids="567,332" category="18,19" order="desc" count="3"]`
* Added: a simple shortcode builder to build [tutor_course] Shortcode from classic editor
* Added: Tutor course widget to display course to sidebar
* Added: `utils()->get_raw_course_price($course_id)`, it will be return to your regular price and sale price as object
* Added: Course page edit by Pagebuilder integration which supports frontend editing
* Added: `$size = 'post-thumbnail',` Arguments at function `get_tutor_course_thumbnail()` And `get_tutor_course_thumbnail_src();`
* Fixed: Hide zero rating count in the course loop grid
* Fixed: some translation issue
* Changed: a template the_content(), reordered position in course single page
* Upgraded: course url upgrade to plural format, courses

### 1.3.0 - 17 May, 2019

* Added: Flash Msg ability to show flash msg in a different view or different action
* Added: Styling, change default color scheme
* Added: return at tutor_course_loop_thumbnail() based on $echo condition
* Added: Purchase history from the dashboard
* Added: Become Instructor Button on student dashboard based on settings
* Added: Enable Disable Course Market place, default Tutor LMS will be single instructor format.
* Fixed: a template div closing/return issue in dashboard > reviews menu
* Fixed: Tutor course content export-import in WordPress way
* Fixed: Some responsive issue in course details and lesson page
* Reduced: Instructor and Student Registration form field. removed phone number and profile bio filed from the registration page.
* Moved: tutor icon CSS file from tutor-front.css file to individually

### 1.2.20 - 10 May, 2019

* Added: Reset Password from Tutor dashboard
* Added: Course Category Image
* Added: my course delete / trash from the dashboard
* Added: profile photo upload from dashboard profile edit page
* Added: Profile Edit from settings page
* Fixed: a default value issue in option checkbox
* Fixed: a permission issue for administrator while attempt to edit other authors course
* Fixed: course post type in rewrite URL, if it changes via the filter
* Removed: Gutenburg Addon for tutor dashboard as it's not necessary, now dashboard comes with settings > selected dashboard page.
* Renamed: Gutenberg blocks label

### 1.2.13 - 26 April, 2019

* Added: Instructor role to the administrator during plugin activation
* Fixed: Course Permission options settings, it was shown always log-in page
* Fixed: Enable students to show reviews wrote on their profile from settings
* Fixed: Show completed course settings

### 1.2.12 - 25 April, 2019

* Added: Synced role with tutor instructor role when changing it from WP User Edit page
* Added: Flatpro theme compatibility
* Fixed: Some design issue in quiz builder
* Fixed: WooCommerce price error when WC is not exists
* Removed: Edit Icon on true/false type question in the quiz builder

### 1.2.11 - 24 April, 2019

* Added: few action hooks at the quiz
* Added: a filter hook to support pro version more perfectly

### 1.2.1 - 23 April, 2019

* Added: email field type at withdrawals methods form generator
* Added: addon lists added to regular version when the pro is not exists
* Fixed: Some design issue in quiz builder
* Fixed: Paypal E-Mail field name, physical address field name at e-check method

### 1.2.0 - 19 April, 2019

* Added: Earning calculation, report, statements at front dashboard
* Added: multiple withdraw method with development support, withdraw confirmation, approved, reject from admin
* Added: database table , `{$wpdb->prefix}tutor_earnings`,  `{$wpdb->prefix}tutor_withdraws`
* Added: RTL CSS support
* Added: Footer text only on Tutor LMS pages in admin dashboard
* Added: a default parameter of $instructor_id = 0 on `tutor_utils()->get_courses_by_instructor()`
* Added: second parameter `$post_status = 'publish'` on  `tutor_utils()->get_courses_by_instructor()`, `$post_status = 'any'` will be return any type of course belongs with current or given user id
* Fixed: Storefront theme compatibility container width
* Fixed: Available Instructors Display on the Course edit page
* Fixed: muiltiple course order issue by WooCommerce
* Fixed: Another author post edit prevented by Tutor
* Changed: Student dashboard to Tutor Dashboard, shortcode, templates, url

### 1.1.1 - 29 March, 2019

* Fixed: quiz builder init sortable question types on load quiz via $.ajax();
* Added: Storefront Theme compatibility

### 1.1.0 - 28 March, 2019

* Fixed: Multiple Instructor, search feature working when pro is not exists
* Fixed: an issue in time update when time limit 0 in quiz attempt, it was return in front-js middle point, so rest of js not worked

### 1.0.9 - 28 March, 2019

* Limit: Quiz Question 5 types
* Updated: course saving message to course related words
* Fixed: is course complete check an issue if user not logged in return false
* Fixed: an issue: in WooCommerce discount coupon for course
* Fixed: Lesson slug will be updated instantly right after update lesson content and title
* Changed: Some text, spelling

### 1.0.8 - 25 March, 2019

* Added: WooCommerce and EDD support to sell courses


### 1.0.7 - 22 March, 2019

* Added: Radio input type at options panel
* Added: required indication for required fields in add new instructor page from admin panel
* Updated: Moved some option select to radio
* Updated: Attempts allowed field slider to number field
* Updated: Removed zero decimal value from the course complete progress bar
* Fixed: redirect back to the quiz after quiz attempt submit
* Fixed: No time limit quiz attempt finishing instantly, it should be no time limit
* Fixed: video poster field show hide based on video source at the course, lesson

### 1.0.6 - 20 March, 2019

* Added: Drop tutor LMS related database table while uninstalling
* Updated: option tab design
* Fixed: Fix a condition of adding autoload class

### 1.0.5 - 18 March, 2019

* Updated Rating and review, added individual 5 stars, 4 stars, 3 stars, 2 stars, 1 star rating count with the meter

### 1.0.4 - 15 March, 2019

* Added: Social share
* Added: course author should be the auto instructor
* Updated: Show login form in enrolment box when the user in guest mode and trying to purchase/enroll a course
* Fixed: Question and answer query for instructor which course only belongs with him.
* Fixed: rating and review placing issue, some time it was stuck
* Added: translation pot file and languages directory
* Fixed: Few known bugs


### 1.0.3 - 08 March, 2019

* Added: onclick topic title slide topic body in course builder
* Added: Add new instructor from instructors > add new instructor, in the dashboard
* Removed: some commented unused code
* Removed: Save and exit button in quiz builder
* Removed: some console.log() at javascript files
* Updated: course level input select to radio
* Updated: Moved question type in the top in quiz builder question
* Updated: Course builder lesson and quiz design
* Updated: Quiz attempt view for the instructor
* Updated: quiz attempts query for instructor and removed attempts which are not submitted yet.
* Fixed: Frontend Template compatible, .tutor-container class CSS
* Fixed: Perfectly topic toggle arrow down up, on click in course builder
* Fixed: some buttons hover CSS in the quiz, course builder
* Fixed: When Tutor Pro does not exist, get enroll button will be in course loop with the free price
* Fixed: Ability to check multiple answers instant right after add question in quiz question type multiple_choice
* Fixed: Image answering question type result in the quiz
* Fixed: a bug in quiz attempt review, it was multi-time decrease when mark as incorrect, not actually marked.
* Fixed: Few known bugs

### 1.0.2 - 06 March, 2019

* Added: button save, save and closed in quiz modal
* Added: Registering user and redirect to Dashboard URL
* Added: questions sorting in quiz builder
* Added: question specific class in quiz answer options
* Update: Quiz attempt design with questions
* Update: some text, typo
* Fixed: instructor database query in instructor lists
* Fixed: query randomized questions limit by quiz
* Fixed: Starting quiz for first time return 0, it should redirect to the quiz page

### 1.0.1 - 05 March, 2019

* Added a course_column in attempts table
* Showed his courses attempts on instructors panel
* Added v.1, added v.1.0.0 compatibility
* Showed full courses comment in guest/public view of single course
* Renamed tags to skills
* Fixed few bugs, improved performance

### 1.0.0 - 01 March, 2019

* Added Quiz, quiz builder, course builder
* Fixed huge bugs from alpha version
* Stable Release

### 1.0.0-alpha - 06 Feb, 2019

* Alpha Release