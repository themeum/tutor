import { defineConfig } from 'cypress';
import 'dotenv/config';
import fs from 'fs';

export default defineConfig({
  projectId: process.env.CYPRESS_PROJECT_ID,

  e2e: {
    setupNodeEvents(on) {
      on('task', {
        checkFileExists(filename) {
          if (fs.existsSync(filename)) {
            return fs.readFileSync(filename, 'utf8');
          }

          return null;
        },
      });
    },
    baseUrl: process.env.CYPRESS_base_url,
    experimentalRunAllSpecs: true,
    experimentalMemoryManagement: true,
    experimentalModifyObstructiveThirdPartyCode: true,
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
    cart: process.env.CYPRESS_tutor_cart_url,
    woo_commerce_cart: process.env.CYPRESS_woo_commerce_cart_url,
    checkout: process.env.CYPRESS_tutor_checkout_url,
    woo_commerce_checkout: process.env.CYPRESS_woo_commerce_checkout_url,
    paypal_merchant_email: process.env.CYPRESS_PAYPAL_MERCHANT_EMAIL,
    paypal_client_id: process.env.CYPRESS_PAYPAL_CLIENT_ID,
    paypal_secret_id: process.env.CYPRESS_PAYPAL_SECRET_ID,
    paypal_webhook_id: process.env.CYPRESS_PAYPAL_WEBHOOK_ID,
    paypal_personal_email: process.env.CYPRESS_PAYPAL_PERSONAL_EMAIL,
    paypal_personal_password: process.env.CYPRESS_PAYPAL_PERSONAL_PASSWORD,
  },
});
