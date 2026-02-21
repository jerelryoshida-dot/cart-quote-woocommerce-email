/**
 * Frontend JavaScript for Cart Quote WooCommerce & Email
 *
 * @package CartQuoteWooCommerce
 * @author Jerel Yoshida
 * @since 1.0.4
 */

(function($) {
    'use strict';

    /**
     * Update cart item quantity via AJAX (single request)
     */
    function updateCartItemQuantity(cartItemKey, quantity, $row) {
        if (!cartItemKey) {
            if (typeof cartQuoteFrontend !== 'undefined' && cartQuoteFrontend.debug) {
                console.log('Cart Quote: Missing cart item key');
            }
            return;
        }

        if (typeof cartQuoteFrontend === 'undefined') {
            console.log('Cart Quote: cartQuoteFrontend not defined');
            return;
        }

        var $wrapper = $row.closest('.cart-quote-form-wrapper');
        
        $.ajax({
            url: cartQuoteFrontend.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cart_quote_update_cart',
                nonce: cartQuoteFrontend.nonce,
                cart_item_key: cartItemKey,
                quantity: quantity
            },
            success: function(response) {
                if (response.success) {
                    // Update UI directly from response (no second AJAX call)
                    if (response.data.items) {
                        response.data.items.forEach(function(item) {
                            var $itemRow = $wrapper.find('li[data-cart-item-key="' + item.key + '"]');
                            if ($itemRow.length) {
                                $itemRow.find('.item-price').html(item.line_total);
                                var $input = $itemRow.find('.cart-quote-qty-input');
                                if ($input.length) {
                                    $input.val(item.quantity);
                                }
                            }
                        });
                    }
                    
                    // Update subtotal if present in wrapper
                    if (response.data.subtotal) {
                        $wrapper.find('.cart-quote-subtotal-amount').html(response.data.subtotal);
                    }
                    
                    // Update global cart count if present
                    if (response.data.cart_count !== undefined) {
                        $('.cart-quote-count').text(response.data.cart_count);
                    }
                } else {
                    // Show error message if available
                    if (response.data && response.data.message) {
                        console.error('Cart Quote: ' + response.data.message);
                    }
                }
            },
            error: function(xhr, status, error) {
                // Log error for debugging
                if (typeof cartQuoteFrontend !== 'undefined' && cartQuoteFrontend.debug) {
                    console.error('Cart Quote AJAX Error:', {
                        status: status,
                        error: error,
                        response: xhr.responseText
                    });
                }
                
                // Restore original quantity on error
                var originalQty = $row.find('.cart-quote-qty-input').data('original-qty');
                if (originalQty) {
                    $row.find('.cart-quote-qty-input').val(originalQty);
                }
            }
        });
    }

// Error handling utility functions
function showError($element, message) {
    var errorContainer = $element.data('error-container');
    
    if (!errorContainer) {
        errorContainer = $('<div class="error-message-container" role="alert"></div>');
        $element.after(errorContainer);
        $element.data('error-container', errorContainer);
    }
    
    errorContainer
        .stop(true, true)
        .html(message)
        .fadeIn(200)
        .scrollTop();
    
    return errorContainer;
}

function hideError($element) {
    var errorContainer = $element.data('error-container');
    
    if (errorContainer) {
        errorContainer
            .stop(true, true)
            .fadeOut(150, function() {
                $(this).empty();
            });
    }
}

// Email validation helper
function isValidEmail(email) {
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Document ready
    $(document).ready(function() {
        
        // Handle + button click for Quote Form
        $(document).on('click', '.cart-quote-summary-items .cart-quote-qty-plus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $btn = $(this);
            var $row = $btn.closest('li');
            var $input = $row.find('.cart-quote-qty-input');
            
            var cartItemKey = $row.attr('data-cart-item-key');
            
            var currentVal = parseInt($input.val()) || 1;
            var newVal = currentVal + 1;
            
            // Store original quantity for rollback on error
            $input.data('original-qty', currentVal);
            
            $input.val(newVal);
            updateCartItemQuantity(cartItemKey, newVal, $row);
        });
        
        // Handle - button click for Quote Form
        $(document).on('click', '.cart-quote-summary-items .cart-quote-qty-minus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $btn = $(this);
            var $row = $btn.closest('li');
            var $input = $row.find('.cart-quote-qty-input');
            
            var cartItemKey = $row.attr('data-cart-item-key');
            
            var currentVal = parseInt($input.val()) || 1;
            var newVal = currentVal - 1;
            
            if (newVal < 1) {
                newVal = 1;
            }
            
            // Store original quantity for rollback on error
            $input.data('original-qty', currentVal);
            
            $input.val(newVal);
            
            if (newVal !== currentVal) {
                updateCartItemQuantity(cartItemKey, newVal, $row);
            }
        });
        
        // Handle manual input change for Quote Form
        $(document).on('change', '.cart-quote-summary-items .cart-quote-qty-input', function(e) {
            var $input = $(this);
            var $row = $input.closest('li');
            var cartItemKey = $row.attr('data-cart-item-key');
            var currentVal = parseInt($input.data('original-qty')) || parseInt($input.val()) || 1;
            var quantity = parseInt($input.val()) || 1;
            
            if (quantity < 1) {
                quantity = 1;
                $input.val(1);
            }
            
            // Store original quantity for rollback on error
            $input.data('original-qty', currentVal);
            
            updateCartItemQuantity(cartItemKey, quantity, $row);
        });

        // Handle table quantity controls (Cart Widget)
        $(document).on('click', '.cart-quote-table .cart-quote-qty-plus', function(e) {
            e.preventDefault();
            var $input = $(this).siblings('.cart-quote-qty-input');
            var currentVal = parseInt($input.val()) || 1;
            $input.val(currentVal + 1).trigger('change');
        });

        $(document).on('click', '.cart-quote-table .cart-quote-qty-minus', function(e) {
            e.preventDefault();
            var $input = $(this).siblings('.cart-quote-qty-input');
            var currentVal = parseInt($input.val()) || 1;
            if (currentVal > 1) {
                $input.val(currentVal - 1).trigger('change');
            }
        });

        $(document).on('change', '.cart-quote-table .cart-quote-qty-input', function(e) {
            var $row = $(this).closest('tr');
            var cartItemKey = $row.attr('data-cart-item-key');
            var quantity = parseInt($(this).val()) || 0;

            if (typeof cartQuoteFrontend !== 'undefined' && cartItemKey) {
                $.ajax({
                    url: cartQuoteFrontend.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'cart_quote_update_cart',
                        nonce: cartQuoteFrontend.nonce,
                        cart_item_key: cartItemKey,
                        quantity: quantity
                    },
                    success: function(response) {
                        if (response.success && response.data.subtotal) {
                            $('.cart-quote-subtotal-value').html(response.data.subtotal);
                        }
                    }
                });
            }
        });

        // Remove item button
        $(document).on('click', '.cart-quote-remove-btn', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var $row = $btn.closest('tr, li');
            var cartItemKey = $btn.attr('data-cart-item-key');
            var productName = $btn.attr('data-product-name') || 'this item';
            var $wrapper = $row.closest('.cart-quote-form-wrapper');

            if (typeof cartQuoteFrontend === 'undefined' || !cartItemKey) {
                return;
            }

            // Show confirmation dialog
            var confirmMessage = 'Are you sure you want to remove "' + productName + '" from your cart?';
            if (!confirm(confirmMessage)) {
                return;
            }

            // Animate out immediately for better UX
            $row.css('opacity', '0.5');

            $.ajax({
                url: cartQuoteFrontend.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_remove_item',
                    nonce: cartQuoteFrontend.nonce,
                    cart_item_key: cartItemKey
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(200, function() {
                            $(this).remove();
                            
                            // Update subtotal if in quote form
                            if ($wrapper.length && response.data.subtotal) {
                                $wrapper.find('.cart-quote-subtotal-amount').html(response.data.subtotal);
                            }
                            
                            // Update cart widget subtotal
                            if (response.data.subtotal) {
                                $('.cart-quote-subtotal-value').html(response.data.subtotal);
                            }
                            
                            // Reload if cart is empty
                            if (response.data.cart_count === 0) {
                                location.reload();
                            }
                        });
                    } else {
                        // Restore opacity on error
                        $row.css('opacity', '1');
                    }
                },
                error: function() {
                    $row.css('opacity', '1');
                }
            });
        });

        // Enhanced quote form submission with better validation
        $(document).on('submit', '#cart-quote-form', function(e) {
            e.preventDefault();

            var $form = $(this);
            var $wrapper = $form.closest('.cart-quote-form-wrapper');
            var $submitBtn = $form.find('.cart-quote-submit-btn');
            var originalText = $submitBtn.text();

            var isValid = true;
            var errorMessage = '';

            // Reset all errors
            $form.find('.error').removeClass('error');
            $('.error-message-container').hide();
            $form.find('.field-error').removeClass('field-error');

            // Check if meeting is requested
            var meetingRequested = $('#meeting_requested').is(':checked');

            // If meeting is requested, validate date and time
            if (meetingRequested) {
                var $dateField = $('#preferred_date');
                var $timeField = $('#preferred_time');

                var dateError = '';
                var timeError = '';

                if (!$dateField.val()) {
                    $dateField.addClass('error');
                    dateError = '<strong>Date Error:</strong> Please select a preferred start date for your meeting.';
                    $dateField.parent().addClass('field-error');
                }

                if (!$timeField.val()) {
                    $timeField.addClass('error');
                    timeError = '<strong>Time Error:</strong> Please select a preferred meeting time.';
                    $timeField.parent().addClass('field-error');
                }

                // Show appropriate error message
                if (dateError || timeError) {
                    showError($form, dateError + '<br>' + timeError);
                    isValid = false;

                    // Scroll to first error
                    $form.find('.error').first().focus();
                    $form.find('.error-message-container').show();

                    return;
                }

                // Additional date validation
                var selectedDate = new Date($dateField.val());
                var today = new Date();
                today.setHours(0, 0, 0, 0);

                if (selectedDate < today) {
                    $dateField.addClass('error');
                    showError($form, '<strong>Date Error:</strong> Please select a future date.');
                    $dateField.parent().addClass('field-error');
                    isValid = false;
                    return;
                }
            }

            // Validate other required fields
            $form.find('[required]').not('#preferred_date').each(function() {
                var $field = $(this);
                
                if (!$field.val()) {
                    $field.addClass('error');
                    $field.parent().addClass('field-error');
                    
                    if (!errorMessage) {
                        errorMessage = 'Please fill in all required fields.';
                    }
                } else {
                    // Validate email format
                    if ($field.attr('type') === 'email' && !isValidEmail($field.val())) {
                        $field.addClass('error');
                        showError($form, '<strong>Email Error:</strong> Please enter a valid email address.');
                        isValid = false;
                        return;
                    }
                    
                    // Validate phone format (optional validation)
                    if ($field.attr('type') === 'tel' && $field.val()) {
                        var phoneRegex = /^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/;
                        if (!phoneRegex.test($field.val())) {
                            $field.addClass('error');
                            showError($form, '<strong>Phone Error:</strong> Please enter a valid phone number.');
                            isValid = false;
                            return;
                        }
                    }
                }
            });

            if (!isValid) {
                $form.find('.error').first().focus();
                $form.find('.error-message-container').show();
                return;
            }

            // Disable button
            $submitBtn.prop('disabled', true).text('Processing...');

            if (typeof cartQuoteFrontend !== 'undefined') {
                $.ajax({
                    url: cartQuoteFrontend.ajaxUrl,
                    type: 'POST',
                    data: $form.serialize() + '&action=cart_quote_submit&nonce=' + cartQuoteFrontend.nonce,
                    success: function(response) {
                        if (response.success) {
                            $form.hide();
                            var successMessage = $wrapper.data('success-message') || 'Thank you! Your quote request has been submitted.';
                            $wrapper.find('.cart-quote-form-success p').text(successMessage);
                            $wrapper.find('.cart-quote-form-success').show();

                            if (response.data.redirect_url) {
                                setTimeout(function() {
                                    window.location.href = response.data.redirect_url;
                                }, 2000);
                            }
                        } else {
                            showError($form, '<strong>Error:</strong> ' + (response.data.message || 'An error occurred.'));
                            $submitBtn.prop('disabled', false).text(originalText);
                        }
                    },
                    error: function() {
                        showError($form, '<strong>Error:</strong> An error occurred. Please try again.');
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                });
            }
        });

        // Enhanced meeting checkbox toggle with better animations
        $(document).on('change', '#meeting_requested', function() {
            var $meetingFields = $('.cart-quote-meeting-fields');
            var $dateField = $('#preferred_date');
            var $timeField = $('#preferred_time');
            var isChecked = $(this).is(':checked');
            
            if (isChecked) {
                // Show fields with animation
                $meetingFields
                    .stop(true, true)
                    .slideDown({
                        duration: 300,
                        easing: 'swing',
                        complete: function() {
                            $dateField.focus();
                            $dateField.addClass('focused-field');
                        }
                    });
                
                // Update ARIA attributes for accessibility
                $meetingFields.attr('aria-hidden', 'false');
                $meetingFields.find('input, select').attr('aria-required', 'true');
                
                // Add visual feedback for checkbox
                $(this).closest('.cart-quote-field').addClass('checkbox-checked');
                
            } else {
                // Hide fields with animation
                $meetingFields
                    .stop(true, true)
                    .slideUp({
                        duration: 250,
                        easing: 'swing',
                        complete: function() {
                            // Remove error states
                            $meetingFields.find('.error').removeClass('error');
                            $dateField.removeAttr('aria-required');
                            $timeField.removeAttr('aria-required');
                            $dateField.removeClass('focused-field');
                            $timeField.removeClass('focused-field');
                        }
                    });
                
                // Update ARIA attributes
                $meetingFields.attr('aria-hidden', 'true');
                
                // Remove visual feedback for checkbox
                $(this).closest('.cart-quote-field').removeClass('checkbox-checked');
            }
        });

        // Contract duration toggle
        $(document).on('change', '#contract_duration', function() {
            var $customField = $('.cart-quote-custom-duration');
            if ($(this).val() === 'custom') {
                $customField.slideDown();
            } else {
                $customField.slideUp();
                $customField.find('input').val('');
            }
        });

    });

    /**
     * Global API: Refresh mini-cart data
     * Can be called by third-party plugins after cart modifications
     * 
     * @param {Object} options - Optional configuration
     * @param {boolean} options.full - Force full refresh including dropdown items (default: true)
     * @param {boolean} options.animated - Show animation during refresh (default: false)
     * @param {Function} options.onSuccess - Callback after successful refresh
     * @param {Function} options.onError - Callback on error
     * 
     * @example
     * // Basic usage
     * window.cartQuoteRefreshMiniCart();
     * 
     * // With options
     * window.cartQuoteRefreshMiniCart({
     *     full: false,  // Update only count/subtotal
     *     animated: true,
     *     onSuccess: function() { console.log('Cart refreshed!'); }
     * });
     */
    window.cartQuoteRefreshMiniCart = function(options) {
        options = options || {};
        var fullRefresh = options.full !== undefined ? options.full : true;
        var animated = options.animated || false;
        
        if (typeof cartQuoteFrontend === 'undefined') {
            throw new Error('Cart Quote: cartQuoteFrontend is not defined. Ensure the plugin scripts are properly loaded.');
        }
        
        // Loading state
        if (animated) {
            $('.cart-quote-mini-cart').addClass('refreshing');
        }
        
        $.ajax({
            url: cartQuoteFrontend.ajaxUrl,
            type: 'POST',
            data: {
                action: 'cart_quote_get_cart',
                nonce: cartQuoteFrontend.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    // Update all mini-cart instances on page
                    $('.cart-quote-mini-cart-container').each(function() {
                        var $container = $(this);
                        var $cart = $container.find('.cart-quote-mini-cart');
                        var $toggle = $cart.find('.cart-quote-mini-toggle');
                        var $dropdown = $cart.find('.cart-quote-mini-dropdown');
                        
                        // Update count badge
                        var $count = $toggle.find('.cart-quote-mini-count');
                        if ($count.length) {
                            $count.text(response.data.count);
                            if (response.data.count > 0) {
                                $count.removeClass('cart-empty');
                            } else {
                                $count.addClass('cart-empty');
                            }
                        }
                        
                        // Update subtotal in toggle
                        var $toggleSubtotal = $toggle.find('.cart-quote-mini-subtotal');
                        if ($toggleSubtotal.length) {
                            $toggleSubtotal.html(response.data.formatted_subtotal);
                        }
                        
                        // Full refresh: update dropdown items and total
                        if (fullRefresh) {
                            if (response.data.is_empty) {
                                // Show empty cart message
                                var emptyCartHTML = `
                                    <div class="cart-quote-mini-empty-state">
                                        <div class="empty-cart-icon">
                                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M3 3h2l8 4-8 4v2a2 2 0 012 2h12a2 2 0 012-2v-2a2 2 0 01-2 2z"/>
                                                <circle cx="9" cy="9" r="2"/>
                                            </svg>
                                        </div>
                                        <div class="empty-cart-content">
                                            <h3>Your cart is empty</h3>
                                            <p>Add items to get started</p>
                                        </div>
                                    </div>
                                `;
                                $dropdown.html(emptyCartHTML);
                            } else {
                                // Rebuild items list
                                var $itemsList = $dropdown.find('.cart-quote-mini-items');
                                if ($itemsList.length) {
                                    $itemsList.empty();
                                    
                                     response.data.items.forEach(function(item) {
                                        var $item = $('<li class="cart-quote-mini-item"></li>');
                                        
                                        var tierBadgeHtml = '';
                                        if (item.tier_data) {
                                            var tierLabel = '';
                                            if (item.tier_data.tier_level) {
                                                tierLabel = 'Tier ' + item.tier_data.tier_level;
                                                if (item.tier_data.description) {
                                                    tierLabel += ': ' + item.tier_data.description;
                                                } else if (item.tier_data.tier_name) {
                                                    tierLabel += ': ' + item.tier_data.tier_name;
                                                }
                                            } else if (item.tier_data.description) {
                                                tierLabel = item.tier_data.description;
                                            } else if (item.tier_data.tier_name) {
                                                tierLabel = item.tier_data.tier_name;
                                            }
                                            
                                            if (tierLabel) {
                                                tierBadgeHtml = '<div class="item-tier-badge">' +
                                                    '<span class="tier-desc">' +
                                                        tierLabel +
                                                        '<span class="tier-qty">x' + item.quantity + '</span>' +
                                                    '</span>' +
                                                    '<span class="tier-price">' + item.line_total + '</span>' +
                                                '</div>';
                                            }
                                        }
                                        
                                        $item.html(
                                            '<div class="item-header">' +
                                                '<span class="item-name">' + item.product_name + '</span>' +
                                                '<span class="item-price">' + item.line_total + '</span>' +
                                            '</div>' +
                                            tierBadgeHtml
                                        );
                                        $itemsList.append($item);
                                    });
                                }
                                
                                // Update subtotal in dropdown
                                var $amount = $dropdown.find('.cart-quote-mini-total .subtotal-amount');
                                if ($amount.length) {
                                    $amount.html(response.data.formatted_subtotal);
                                }
                            }
                        }
                    });
                    
                    // Remove loading state
                    if (animated) {
                        $('.cart-quote-mini-cart').removeClass('refreshing');
                    }
                    
                    // Trigger custom event for other scripts
                    $(document).trigger('cartQuoteMiniCartRefreshed', [response.data]);
                    
                    // Call success callback if provided
                    if (typeof options.onSuccess === 'function') {
                        options.onSuccess(response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Cart Quote Mini-Cart Refresh Failed:', error);
                
                // Remove loading state
                if (animated) {
                    $('.cart-quote-mini-cart').removeClass('refreshing');
                }
                
                // Trigger error event
                $(document).trigger('cartQuoteMiniCartRefreshError', [{xhr: xhr, status: status, error: error}]);
                
                // Call error callback if provided
                if (typeof options.onError === 'function') {
                    options.onError({xhr: xhr, status: status, error: error});
                }
            }
        });
    };
    
    // Click toggle functionality - only toggle on the toggle button, not dropdown content
    var miniCartOpen = false;
    var miniCartTimer = null;
    
    // Toggle dropdown when clicking the toggle button only
    $(document).on('click', '.cart-quote-mini-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $cart = $(this).closest('.cart-quote-mini-cart');
        var $dropdown = $cart.find('.cart-quote-mini-dropdown');
        
        miniCartOpen = !miniCartOpen;
        
        if (miniCartOpen) {
            $cart.addClass('active');
            $dropdown.addClass('active');
            
            clearTimeout(miniCartTimer);
            miniCartTimer = setTimeout(function() {
                if (miniCartOpen) {
                    closeMiniCart();
                }
            }, 5000);
        } else {
            closeMiniCart();
        }
    });
    
    // Prevent clicks inside dropdown from closing it (allow links to work)
    $(document).on('click', '.cart-quote-mini-dropdown', function(e) {
        e.stopPropagation();
    });
    
    // Close when clicking outside
    $(document).on('click.mini-cart', function(e) {
        if ($(e.target).closest('.cart-quote-mini-cart').length === 0) {
            closeMiniCart();
        }
    });
    
    // Close function
    function closeMiniCart() {
        clearTimeout(miniCartTimer);
        miniCartOpen = false;
        $('.cart-quote-mini-cart').removeClass('active');
        $('.cart-quote-mini-dropdown').removeClass('active');
    }
    
    // Close on ESC key
    $(document).on('keydown.mini-cart', function(e) {
        if (e.key === 'Escape' && miniCartOpen) {
            closeMiniCart();
        }
    });

})(jQuery);
