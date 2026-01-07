import './commands';

// eslint-disable-next-line @typescript-eslint/no-explicit-any
(window as any).__TUTOR_TEXT_DOMAIN__ = 'tutor';

Cypress.on('uncaught:exception', (err) => {
  // eslint-disable-next-line no-console
  console.error(err);
  return false;
});
