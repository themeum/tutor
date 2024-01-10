import { defineConfig } from "cypress";

export default defineConfig({
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
  viewportHeight: 900,
  viewportWidth: 1400,
  env: {
    base_url: 'http://localhost:10003',
    single_course_slug: 'the-complete-javascript-course-2019-build-real-projects',
    student_username: 'student',
    student_password: 'test123',
    admin_username: 'admin',
    admin_password: 'admin',
    instructor_username: 'instructor',
    instructor_password: 'test123',
  },
});
