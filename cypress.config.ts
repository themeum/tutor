import { defineConfig } from 'cypress';
const dotenv = require('dotenv');

dotenv.config();

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
    base_url: process.env.CYPRESS_base_url,
    single_course_slug: process.env.CYPRESS_single_course_slug,
    paid_course_slug: process.env.CYPRESS_paid_course_slug,
    cod_course_slug: process.env.CYPRESS_cod_course_slug,
    student_username: process.env.CYPRESS_student_username,
    student_password: process.env.CYPRESS_student_password,
    admin_username: process.env.CYPRESS_admin_username,
    admin_password: process.env.CYPRESS_admin_password,
    instructor_username: process.env.CYPRESS_instructor_username,
    instructor_password: process.env.CYPRESS_instructor_password,
    instructor_zoom_account_id: process.env.CYPRESS_instructor_zoom_account_id,
    instructor_zoom_client_id: process.env.CYPRESS_instructor_zoom_client_id,
    instructor_zoom_client_secret: process.env.CYPRESS_instructor_zoom_client_secret,
  },
});
