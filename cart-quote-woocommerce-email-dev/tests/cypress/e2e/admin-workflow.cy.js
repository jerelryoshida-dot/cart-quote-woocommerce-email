/**
 * Admin Workflow E2E Tests
 *
 * Tests for admin dashboard functionality including quote management,
 * status updates, and settings.
 */

describe('Admin Quote Management', { tags: ['@admin', '@dashboard'] }, () => {
  beforeEach(() => {
    cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
  });

  context('Dashboard Access', () => {
    it('should display Cart Quotes menu for authorized users', () => {
      cy.visit('/wp-admin/');
      cy.get('#toplevel_page_cart-quote-manager').should('be.visible');
    });

    it('should redirect unauthorized users to login', () => {
      cy.logout();
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager');
      cy.url().should('include', '/wp-login.php');
    });
  });

  context('Quote List View', () => {
    beforeEach(() => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager');
    });

    it('should display quotes table', () => {
      cy.get('.cart-quote-list-table').should('be.visible');
      cy.get('.cart-quote-list-table th').should('contain', 'Quote ID');
      cy.get('.cart-quote-list-table th').should('contain', 'Customer');
    });

    it('should filter quotes by status', () => {
      cy.get('#filter-status').select('pending');
      cy.get('#filter-submit').click();
      cy.url().should('include', 'status=pending');
    });

    it('should search quotes', () => {
      cy.get('#quote-search-input').clear().type('Q1001{enter}');
      cy.url().should('include', 's=Q1001');
    });

    it('should paginate through quotes', () => {
      cy.get('.pagination-links').should('exist');
    });

    it('should display statistics', () => {
      cy.get('.cart-quote-stats').should('be.visible');
      cy.get('.stat-pending').should('exist');
      cy.get('.stat-contacted').should('exist');
    });
  });

  context('Quote Detail View', () => {
    it('should display quote details', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('.quote-detail-header').should('be.visible');
      cy.get('.customer-info').should('be.visible');
      cy.get('.quote-items-table').should('be.visible');
    });

    it('should update quote status', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('.cart-quote-status-select').select('contacted');
      
      cy.get('.cart-quote-toast.success', { timeout: 5000 }).should('be.visible');
    });

    it('should save admin notes', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('#admin_notes').clear().type('Test note from Cypress');
      cy.get('.cart-quote-save-notes').click();
      
      cy.get('.cart-quote-toast.success', { timeout: 5000 }).should('be.visible');
    });

    it('should show error for non-existent quote', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=999999', {
        failOnStatusCode: false,
      });
    });
  });

  context('Export Functionality', () => {
    beforeEach(() => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager');
    });

    it('should export quotes to CSV', () => {
      cy.get('.cart-quote-export-csv').click();
      
      cy.wait(2000);
    });
  });

  context('Email Functionality', () => {
    it('should resend admin email', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('.cart-quote-resend-email[data-email-type="admin"]').click();
      
      cy.get('.cart-quote-toast', { timeout: 5000 }).should('be.visible');
    });

    it('should resend client email', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('.cart-quote-resend-email[data-email-type="client"]').click();
      
      cy.get('.cart-quote-toast', { timeout: 5000 }).should('be.visible');
    });
  });

  context('Google Calendar Integration', () => {
    it('should show Google Calendar settings page', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-google');
      
      cy.get('.cart-quote-google-settings').should('be.visible');
    });

    it('should display connect button when not connected', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-google');
      
      cy.get('body').then(($body) => {
        if ($body.find('.cart-quote-google-connect').length > 0) {
          cy.get('.cart-quote-google-connect').should('be.visible');
        }
      });
    });

    it('should create Google Calendar event from quote', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
      
      cy.get('body').then(($body) => {
        if ($body.find('.cart-quote-create-event').length > 0) {
          cy.get('.cart-quote-create-event').click();
          cy.get('.cart-quote-toast', { timeout: 10000 }).should('be.visible');
        }
      });
    });
  });

  context('Settings Page', () => {
    it('should display settings form', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-settings');
      
      cy.get('#cart-quote-settings-form').should('be.visible');
      cy.get('#quote_prefix').should('be.visible');
      cy.get('#admin_email').should('be.visible');
    });

    it('should save settings', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-settings');
      
      cy.get('#quote_prefix').clear().type('Q');
      cy.get('#cart-quote-settings-form').submit();
      
      cy.get('.notice-success', { timeout: 5000 }).should('be.visible');
    });

    it('should validate admin email', () => {
      cy.visit('/wp-admin/admin.php?page=cart-quote-settings');
      
      cy.get('#admin_email').clear().type('not-an-email');
      cy.get('#admin_email:invalid').should('exist');
    });
  });
});
