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
    base_url: 'http://localhost:8888/wordpress-tutor',
    single_course_slug: 'intro-to-javascript-for-beginners',
    student_username: 'tutor',//student
    student_password: 'zgB#X9hN4kkqJLd67T',//test123
    admin_username: 'tutor',
    admin_password: 'zgB#X9hN4kkqJLd67T',
    instructor_username: 'tutor',
    instructor_password: 'zgB#X9hN4kkqJLd67T',
  },
});
