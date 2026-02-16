/**
 * Admin JavaScript for Cart Quote WooCommerce & Email
 *
 * @package CartQuoteWooCommerce
 * @author Jerel Yoshida
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Cart Quote Admin
    const CartQuoteAdmin = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
            this.initDatepicker();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            // Status change
            $(document).on('change', '.cart-quote-status-select', this.updateStatus);

            // Create Google Event
            $(document).on('click', '.cart-quote-create-event', this.createGoogleEvent);

            // Resend email
            $(document).on('click', '.cart-quote-resend-email', this.resendEmail);

            // Save notes
            $(document).on('click', '.cart-quote-save-notes', this.saveNotes);

            // Update meeting
            $(document).on('click', '.cart-quote-update-meeting', this.updateMeeting);

            // Create Google Meet
            $(document).on('click', '.cart-quote-create-meet', this.createGoogleMeet);

            // Export CSV
            $(document).on('click', '.cart-quote-export-csv', this.exportCSV);

            // Disconnect Google
            $(document).on('click', '.cart-quote-google-disconnect', this.disconnectGoogle);

            // Add time slot
            $(document).on('click', '.cart-quote-add-time-slot', this.addTimeSlot);

            // Remove time slot
            $(document).on('click', '.cart-quote-remove-slot', this.removeTimeSlot);

            // View quote details
            $(document).on('click', '.cart-quote-view-details', this.viewQuoteDetails);

            // Modal close
            $(document).on('click', '.cart-quote-modal-close', this.closeModal);
            $(document).on('click', '.cart-quote-modal', function(e) {
                if ($(e.target).hasClass('cart-quote-modal')) {
                    CartQuoteAdmin.closeModal();
                }
            });
        },

        /**
         * Initialize datepicker
         */
        initDatepicker: function() {
            $('.cart-quote-datepicker').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        },

        /**
         * Update quote status
         */
        updateStatus: function(e) {
            e.preventDefault();

            var $select = $(this);
            var quoteId = $select.data('quote-id');
            var newStatus = $select.val();
            var oldStatus = $select.data('old-status');

            if (!confirm(cartQuoteAdmin.i18n.confirmStatusChange)) {
                $select.val(oldStatus);
                return;
            }

            CartQuoteAdmin.showLoading($select);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_update_status',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId,
                    status: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        $select.data('old-status', newStatus);
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                        location.reload();
                    } else {
                        $select.val(oldStatus);
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    $select.val(oldStatus);
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                },
                complete: function() {
                    CartQuoteAdmin.hideLoading($select);
                }
            });
        },

        /**
         * Create Google Calendar event
         */
        createGoogleEvent: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var quoteId = $btn.data('quote-id');

            if (!confirm('Create a Google Calendar event for this quote?')) {
                return;
            }

            $btn.prop('disabled', true).text(cartQuoteAdmin.i18n.creatingEvent);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_create_event',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                        location.reload();
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                        $btn.prop('disabled', false).text('Create Google Event');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                    $btn.prop('disabled', false).text('Create Google Event');
                }
            });
        },

        /**
         * Resend email
         */
        resendEmail: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var quoteId = $btn.data('quote-id');
            var emailType = $btn.data('email-type') || 'both';

            $btn.prop('disabled', true).text(cartQuoteAdmin.i18n.resendingEmail);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_resend_email',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId,
                    email_type: emailType
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Resend Email');
                }
            });
        },

        /**
         * Save admin notes
         */
        saveNotes: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var quoteId = $btn.data('quote-id');
            var notes = $('#admin_notes').val();

            if (!confirm(cartQuoteAdmin.i18n.confirmSaveNotes)) {
                return;
            }

            $btn.prop('disabled', true).text(cartQuoteAdmin.i18n.saving);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_save_notes',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId,
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Save Notes');
                }
            });
        },

        /**
         * Update meeting date/time
         */
        updateMeeting: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var quoteId = $btn.data('quote-id');
            var preferredDate = $('#meeting_date').val();
            var preferredTime = $('#meeting_time').val();

            if (!confirm(cartQuoteAdmin.i18n.confirmUpdateMeeting)) {
                return;
            }

            $btn.prop('disabled', true).text(cartQuoteAdmin.i18n.saving);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_update_meeting',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId,
                    preferred_date: preferredDate,
                    preferred_time: preferredTime
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Update Meeting');
                }
            });
        },

        /**
         * Create Google Meet
         */
        createGoogleMeet: function(e) {
            e.preventDefault();

            var $btn = $(this);
            var quoteId = $btn.data('quote-id');

            if (!confirm(cartQuoteAdmin.i18n.confirmCreateMeet)) {
                return;
            }

            $btn.prop('disabled', true).text(cartQuoteAdmin.i18n.creatingEvent);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_admin_create_meet',
                    nonce: cartQuoteAdmin.nonce,
                    id: quoteId
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                        if (response.data.meet_link) {
                            CartQuoteAdmin.showToast('Meet Link: ' + response.data.meet_link, 'success');
                        }
                        location.reload();
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                        $btn.prop('disabled', false).text('Create Google Meet');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                    $btn.prop('disabled', false).text('Create Google Meet');
                }
            });
        },

        /**
         * Export quotes to CSV
         */
        exportCSV: function(e) {
            e.preventDefault();

            var params = {
                action: 'cart_quote_admin_export_csv',
                nonce: cartQuoteAdmin.nonce,
                status: $('#filter-status').val(),
                date_from: $('#filter-date-from').val(),
                date_to: $('#filter-date-to').val()
            };

            var url = cartQuoteAdmin.ajaxUrl + '?' + $.param(params);
            window.location.href = url;
        },

        /**
         * Disconnect Google Calendar
         */
        disconnectGoogle: function(e) {
            e.preventDefault();

            if (!confirm('Are you sure you want to disconnect Google Calendar?')) {
                return;
            }

            var $btn = $(this);

            $btn.prop('disabled', true);

            $.ajax({
                url: cartQuoteAdmin.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cart_quote_google_disconnect',
                    nonce: cartQuoteAdmin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        CartQuoteAdmin.showToast(response.data.message, 'success');
                        location.reload();
                    } else {
                        CartQuoteAdmin.showToast(response.data.message, 'error');
                    }
                },
                error: function() {
                    CartQuoteAdmin.showToast(cartQuoteAdmin.i18n.error, 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false);
                }
            });
        },

        /**
         * Add time slot
         */
        addTimeSlot: function(e) {
            e.preventDefault();

            var template = '<div class="cart-quote-time-slot">' +
                '<input type="time" name="time_slots[]" value="">' +
                '<span class="cart-quote-remove-slot dashicons dashicons-no-alt"></span>' +
                '</div>';

            $('.cart-quote-time-slots').append(template);
        },

        /**
         * Remove time slot
         */
        removeTimeSlot: function(e) {
            e.preventDefault();
            $(this).closest('.cart-quote-time-slot').remove();
        },

        /**
         * View quote details in modal
         */
        viewQuoteDetails: function(e) {
            e.preventDefault();

            var quoteId = $(this).data('quote-id');

            // For now, redirect to detail page
            window.location.href = 'admin.php?page=cart-quote-manager&action=view&id=' + quoteId;
        },

        /**
         * Show modal
         */
        showModal: function(content) {
            var modal = '<div class="cart-quote-modal">' +
                '<div class="cart-quote-modal-content">' +
                '<div class="cart-quote-modal-header">' +
                '<h3>Quote Details</h3>' +
                '<button type="button" class="cart-quote-modal-close">&times;</button>' +
                '</div>' +
                '<div class="cart-quote-modal-body">' + content + '</div>' +
                '</div>' +
                '</div>';

            $('body').append(modal);
        },

        /**
         * Close modal
         */
        closeModal: function() {
            $('.cart-quote-modal').remove();
        },

        /**
         * Show loading state
         */
        showLoading: function($element) {
            $element.addClass('cart-quote-loading');
        },

        /**
         * Hide loading state
         */
        hideLoading: function($element) {
            $element.removeClass('cart-quote-loading');
        },

        /**
         * Show toast notification
         */
        showToast: function(message, type) {
            type = type || 'success';
            var $toast = $('<div class="cart-quote-toast ' + type + '">' + message + '</div>');
            $('body').append($toast);
            setTimeout(function() {
                $toast.fadeOut(function() {
                    $(this).remove();
                });
            }, 4000);
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        CartQuoteAdmin.init();
    });

})(jQuery);
