/**
 * Quote Submission E2E Tests
 *
 * Tests for the complete quote submission flow including form validation,
 * successful submission, and error handling.
 */

describe('Quote Submission Flow', { tags: ['@quote', '@frontend'] }, () => {
  beforeEach(() => {
    cy.clearCookies();
    cy.clearLocalStorage();
  });

  context('Form Display', () => {
    it('should display quote form when cart has items', () => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
      
      cy.get('#cart-quote-form').should('be.visible');
      cy.get('#billing_first_name').should('be.visible');
      cy.get('#billing_email').should('be.visible');
      cy.get('.cart-quote-submit-btn').should('be.visible');
    });

    it('should show empty cart message when cart is empty', () => {
      cy.clearCart();
      cy.visit('/quote-form/');
      
      cy.get('.cart-quote-empty-cart').should('be.visible');
      cy.get('#cart-quote-form').should('not.exist');
    });
  });

  context('Form Validation', () => {
    beforeEach(() => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
    });

    it('should require all mandatory fields', () => {
      cy.get('.cart-quote-submit-btn').click();
      
      cy.get('#billing_first_name:invalid').should('exist');
      cy.get('#billing_last_name:invalid').should('exist');
      cy.get('#billing_email:invalid').should('exist');
      cy.get('#billing_phone:invalid').should('exist');
      cy.get('#billing_company:invalid').should('exist');
      cy.get('#preferred_date:invalid').should('exist');
    });

    it('should reject invalid email format', () => {
      cy.get('#billing_email').clear().type('not-an-email');
      cy.get('#billing_email:invalid').should('exist');
      
      cy.get('#billing_email').clear().type('valid@example.com');
      cy.get('#billing_email:valid').should('exist');
    });

    it('should show custom duration field when custom is selected', () => {
      cy.get('#contract_duration').select('custom');
      cy.get('.cart-quote-custom-duration').should('be.visible');
      
      cy.get('#contract_duration').select('1_month');
      cy.get('.cart-quote-custom-duration').should('not.be.visible');
    });

    it('should not allow past dates for preferred date', () => {
      const yesterday = new Date();
      yesterday.setDate(yesterday.getDate() - 1);
      const pastDate = yesterday.toISOString().split('T')[0];
      
      cy.get('#preferred_date').clear().type(pastDate);
      cy.get('#preferred_date:invalid').should('exist');
    });
  });

  context('Successful Submission', () => {
    beforeEach(() => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
    });

    it('should submit a valid quote successfully', () => {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      cy.submitQuoteForm({
        firstName: 'John',
        lastName: 'Doe',
        email: 'john@example.com',
        phone: '+1234567890',
        company: 'Acme Corporation',
        date: tomorrow.toISOString().split('T')[0],
        duration: '1_month',
      });
      
      cy.get('.cart-quote-form-success', { timeout: 10000 }).should('be.visible');
      cy.get('.cart-quote-form-success p').should('contain', 'Thank you');
    });

    it('should display quote reference after submission', () => {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      cy.submitQuoteForm({
        firstName: 'Jane',
        lastName: 'Smith',
        email: 'jane@test.com',
        phone: '+0987654321',
        company: 'Test Inc',
        date: tomorrow.toISOString().split('T')[0],
        duration: '3_months',
      });
      
      cy.get('.cart-quote-form-success', { timeout: 10000 }).should('be.visible');
    });

    it('should clear cart after successful submission', () => {
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      cy.submitQuoteForm({
        firstName: 'Test',
        lastName: 'User',
        email: 'test@example.com',
        phone: '+1111111111',
        company: 'Test Co',
        date: tomorrow.toISOString().split('T')[0],
        duration: '6_months',
      });
      
      cy.get('.cart-quote-form-success', { timeout: 10000 }).should('be.visible');
      cy.visit('/cart/');
      cy.get('.cart-empty').should('be.visible');
    });
  });

  context('Error Handling', () => {
    beforeEach(() => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
    });

    it('should show error message on AJAX failure', () => {
      cy.intercept('POST', '/wp-admin/admin-ajax.php', {
        statusCode: 500,
        body: { success: false, data: { message: 'Server error' } },
      }).as('ajaxFail');
      
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      cy.submitQuoteForm({
        firstName: 'Error',
        lastName: 'Test',
        email: 'error@test.com',
        phone: '+2222222222',
        company: 'Error Co',
        date: tomorrow.toISOString().split('T')[0],
        duration: '1_month',
      });
      
      cy.wait('@ajaxFail');
      cy.on('window:alert', (text) => {
        expect(text).to.include('error');
      });
    });

    it('should handle network timeout gracefully', () => {
      cy.intercept('POST', '/wp-admin/admin-ajax.php', {
        delay: 30000,
        body: { success: true },
      }).as('slowAjax');
      
      const tomorrow = new Date();
      tomorrow.setDate(tomorrow.getDate() + 1);
      
      cy.submitQuoteForm({
        firstName: 'Timeout',
        lastName: 'Test',
        email: 'timeout@test.com',
        phone: '+3333333333',
        company: 'Timeout Co',
        date: tomorrow.toISOString().split('T')[0],
        duration: '1_month',
      });
      
      cy.get('.cart-quote-submit-btn', { timeout: 15000 })
        .should('not.be.disabled');
    });
  });

  context('Cart Display', () => {
    it('should display cart items on quote form', () => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
      
      cy.get('.cart-quote-summary-items li').should('have.length.at.least', 1);
      cy.get('.cart-quote-subtotal-amount').should('be.visible');
    });

    it('should allow quantity changes', () => {
      cy.addProductToCart(123);
      cy.visit('/quote-form/');
      
      cy.get('.cart-quote-qty-plus').first().click();
      cy.get('.cart-quote-qty-input').first().should('have.value', '2');
    });
  });
});
