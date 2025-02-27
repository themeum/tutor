import './commands';

Cypress.on('uncaught:exception', (err) => {
  console.error(err);
  return false;
});
