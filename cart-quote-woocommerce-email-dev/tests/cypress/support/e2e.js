/**
 * Cypress Support File
 *
 * Custom commands and configuration for E2E testing.
 */

// Load grep plugin if available
try {
  require('@cypress/grep');
} catch (e) {
  // Plugin not installed, continue without it
}

Cypress.Commands.add('login', (username, password) => {
  cy.visit('/wp-login.php');
  cy.get('#user_login').clear().type(username || Cypress.env('wpUsername'));
  cy.get('#user_pass').clear().type(password || Cypress.env('wpPassword'));
  cy.get('#wp-submit').click();
  cy.url().should('not.include', '/wp-login.php');
});

Cypress.Commands.add('logout', () => {
  cy.visit('/wp-login.php?action=logout');
  cy.get('a').contains('log out').click();
});

Cypress.Commands.add('addProductToCart', (productId) => {
  cy.visit(`/?add-to-cart=${productId}`);
  cy.get('.woocommerce-message').should('be.visible');
});

Cypress.Commands.add('clearCart', () => {
  cy.visit('/cart/');
  cy.get('body').then(($body) => {
    if ($body.find('.woocommerce-cart-form__cart-item .remove').length > 0) {
      cy.get('.woocommerce-cart-form__cart-item .remove').each(($btn) => {
        cy.wrap($btn).click({ force: true });
      });
    }
  });
});

Cypress.Commands.add('submitQuoteForm', (data) => {
  cy.get('#billing_first_name').clear().type(data.firstName);
  cy.get('#billing_last_name').clear().type(data.lastName);
  cy.get('#billing_email').clear().type(data.email);
  cy.get('#billing_phone').clear().type(data.phone);
  cy.get('#billing_company').clear().type(data.company);
  cy.get('#preferred_date').clear().type(data.date);
  cy.get('#contract_duration').select(data.duration);
  cy.get('#cart-quote-form').submit();
});

Cypress.Commands.add('getAjaxNonce', () => {
  return cy.window().then((win) => {
    return win.cartQuoteFrontend?.nonce || '';
  });
});

Cypress.Commands.add('getAdminAjaxNonce', () => {
  return cy.window().then((win) => {
    return win.cartQuoteAdmin?.nonce || '';
  });
});

Cypress.Commands.add('interceptAjax', (action) => {
  cy.intercept('POST', '/wp-admin/admin-ajax.php').as(action);
});

Cypress.on('uncaught:exception', (err, runnable) => {
  if (err.message.includes('is not defined')) {
    return false;
  }
  return true;
});

beforeEach(() => {
  cy.clearCookies();
  cy.clearLocalStorage();
});

console.log('Cypress support file loaded');
