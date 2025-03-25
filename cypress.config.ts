import { defineConfig } from 'cypress';
import 'dotenv/config';

export default defineConfig({
  projectId: process.env.CYPRESS_PROJECT_ID,

  e2e: {
    setupNodeEvents() {
      // implement node event listeners here
    },
    baseUrl: process.env.CYPRESS_base_url,
    experimentalRunAllSpecs: true,
    experimentalMemoryManagement: true,
  },

  viewportHeight: 900,
  viewportWidth: 1400,
  scrollBehavior: 'center',

  env: {
    base_url: process.env.CYPRESS_base_url,
    single_course_slug: process.env.CYPRESS_single_course_slug,
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
