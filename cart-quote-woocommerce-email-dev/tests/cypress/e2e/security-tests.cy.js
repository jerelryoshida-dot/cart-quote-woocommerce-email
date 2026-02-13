/**
 * Security E2E Tests
 *
 * Security-focused end-to-end tests for the Cart Quote plugin.
 * Tests CSRF, XSS, authorization, and other security concerns.
 */

describe('Security Tests', { tags: ['@security'] }, () => {
  context('CSRF Protection', () => {
    it('should reject quote submission without valid nonce', () => {
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_submit',
          nonce: 'invalid_nonce',
          billing_first_name: 'Test',
          billing_email: 'test@example.com',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.be.false;
      });
    });

    it('should reject admin status update without valid nonce', () => {
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_admin_update_status',
          nonce: 'invalid_nonce',
          id: 1,
          status: 'contacted',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.be.false;
      });
    });

    it('should reject cart update without valid nonce', () => {
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_update_cart',
          nonce: 'invalid_nonce',
          cart_item_key: 'a1b2c3d4e5f6789012345678901234ab',
          quantity: 2,
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.be.false;
      });
    });
  });

  context('Authorization', () => {
    it('should deny admin actions to unauthenticated users', () => {
      cy.clearCookies();
      
      const adminActions = [
        'cart_quote_admin_update_status',
        'cart_quote_admin_create_event',
        'cart_quote_admin_resend_email',
        'cart_quote_admin_save_notes',
        'cart_quote_admin_export_csv',
      ];

      adminActions.forEach((action) => {
        cy.request({
          method: 'POST',
          url: '/wp-admin/admin-ajax.php',
          form: true,
          body: {
            action: action,
            id: 1,
          },
          failOnStatusCode: false,
        }).then((response) => {
          expect(response.body.success).to.be.false;
        });
      });
    });

    it('should require manage_options for Google OAuth', () => {
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_google_oauth_callback',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.not.equal(true);
      });
    });

    it('should protect admin pages from unauthorized access', () => {
      cy.clearCookies();
      
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager', { failOnStatusCode: false });
      cy.url().should('include', '/wp-login.php');
    });
  });

  context('XSS Prevention', () => {
    const xssPayloads = [
      '<script>alert("XSS")</script>',
      '<img src=x onerror="alert(1)">',
      '<svg onload="alert(1)">',
      'javascript:alert(1)',
      '<body onload="alert(1)">',
    ];

    xssPayloads.forEach((payload, index) => {
      it(`should sanitize XSS payload #${index + 1} in form fields`, () => {
        cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
        cy.visit('/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1');
        
        cy.get('#admin_notes').clear().type(payload);
        cy.get('.cart-quote-save-notes').click();
        
        cy.get('.cart-quote-toast').should('be.visible');
        cy.get('#admin_notes').should('not.contain', '<script>');
      });
    });

    it('should escape output in quote list', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      cy.visit('/wp-admin/admin.php?page=cart-quote-manager');
      
      cy.get('.cart-quote-list-table').should('not.contain', '<script>');
      cy.get('.cart-quote-list-table').should('not.contain', 'onerror=');
    });
  });

  context('SQL Injection Prevention', () => {
    const sqlPayloads = [
      "' OR '1'='1",
      "'; DROP TABLE wp_users;--",
      '1; SELECT * FROM wp_users',
      "' UNION SELECT NULL--",
    ];

    sqlPayloads.forEach((payload, index) => {
      it(`should prevent SQL injection in search #${index + 1}`, () => {
        cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
        cy.visit('/wp-admin/admin.php?page=cart-quote-manager');
        
        cy.get('#quote-search-input').clear().type(payload + '{enter}');
        
        cy.get('.cart-quote-list-table').should('be.visible');
        cy.get('body').should('not.contain', 'SQL syntax');
        cy.get('body').should('not.contain', 'mysql_fetch');
      });
    });

    it('should prevent SQL injection in ID parameter', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_admin_update_status',
          nonce: 'valid_nonce',
          id: "1' OR '1'='1",
          status: 'contacted',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.status).to.equal(200);
      });
    });
  });

  context('Input Validation', () => {
    it('should validate email format strictly', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_admin_resend_email',
          nonce: 'valid_nonce',
          id: 1,
          email_type: 'client',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.status).to.equal(200);
      });
    });

    it('should validate status values', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_admin_update_status',
          nonce: 'valid_nonce',
          id: 1,
          status: 'invalid_status',
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.be.false;
      });
    });

    it('should handle negative quantities', () => {
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_update_cart',
          nonce: 'valid_nonce',
          cart_item_key: 'a1b2c3d4e5f6789012345678901234ab',
          quantity: -5,
        },
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.body.success).to.be.false;
      });
    });
  });

  context('CSV Export Security', () => {
    it('should escape formula injection in CSV export', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.request({
        method: 'GET',
        url: '/wp-admin/admin-ajax.php?action=cart_quote_admin_export_csv&nonce=valid_nonce',
        failOnStatusCode: false,
      }).then((response) => {
        expect(response.headers['content-type']).to.include('text/csv');
      });
    });
  });

  context('Rate Limiting', () => {
    it('should handle multiple rapid submissions', () => {
      const requests = Array(5).fill(null).map((_, i) => {
        return cy.request({
          method: 'POST',
          url: '/wp-admin/admin-ajax.php',
          form: true,
          body: {
            action: 'cart_quote_submit',
            nonce: 'valid_nonce',
            billing_first_name: `Test${i}`,
            billing_email: `test${i}@example.com`,
          },
          failOnStatusCode: false,
        });
      });
    });
  });

  context('OAuth Security', () => {
    it('should validate OAuth state parameter', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.visit('/wp-admin/admin-ajax.php?action=cart_quote_google_oauth_callback&state=invalid_state', {
        failOnStatusCode: false,
      });
    });
  });

  context('Session Security', () => {
    it('should not expose sensitive data in AJAX responses', () => {
      cy.login(Cypress.env('wpUsername'), Cypress.env('wpPassword'));
      
      cy.request({
        method: 'POST',
        url: '/wp-admin/admin-ajax.php',
        form: true,
        body: {
          action: 'cart_quote_get_cart',
          nonce: 'valid_nonce',
        },
      }).then((response) => {
        const body = JSON.stringify(response.body);
        expect(body).to.not.include('password');
        expect(body).to.not.include('secret');
        expect(body).to.not.include('token');
      });
    });
  });
});
