import './commands';

Cypress.on('uncaught:exception', (err) => {
  // eslint-disable-next-line no-console
  console.error(err);
  return false;
});
