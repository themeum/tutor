import { defineConfig } from 'cypress';

export default defineConfig({
  e2e: {
    setupNodeEvents(on) {
      // implement node event listeners here
      on('before:run', (details) => {
        console.log('Before run:', details);
      });

      on('after:run', (results) => {
        console.log('After run:', results);
      });
    },
    baseUrl: 'http://themeum-tutor.local',
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
    env: {
      username: 'blind',
      password: 'abir',
    },
    viewportWidth: 1680,
    viewportHeight: 1050,
  },
});
