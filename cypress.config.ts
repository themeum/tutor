import { defineConfig } from "cypress";

export default defineConfig({
  chromeWebSecurity: false,
  e2e: {
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
  viewportHeight: 900,
  viewportWidth: 1400,
  env: {
    base_url: 'http://localhost:8888/wordpress-tutor',
    single_course_slug: 'intro-to-javascript-for-beginners-free-2',
    paid_course_slug:'intro-to-paid-course-2-2',
    cod_course_slug:'intro-to-javascript-for-beginners-cod',
    student_username: 'tutor',
    student_password: 'zgB#X9hN4kkqJLd67T',
    admin_username: 'tutor',
    admin_password: 'zgB#X9hN4kkqJLd67T',
    instructor_username: 'tutor',
    instructor_password: 'zgB#X9hN4kkqJLd67T',
    instructor_zoom_account_id:'fcsXFECITP2sLoJxvK7VtQ',
    instructor_zoom_client_id:'KY2IWr_dSVi_FaTF62YHeA',
    instructor_zoom_client_secret:'O5TmiqEkJjY8lII98XfrJL8KnEn5Y6un',
  },
});
