<?php
/**
 * Admin Settings Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cart-quote-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" class="cart-quote-settings-form">
        <?php wp_nonce_field('cart_quote_settings', 'cart_quote_settings_nonce'); ?>

        <!-- General Settings -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('General Settings', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="quote_prefix"><?php esc_html_e('Quote ID Prefix', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="quote_prefix" id="quote_prefix" value="<?php echo esc_attr(get_option('cart_quote_wc_quote_prefix', 'Q')); ?>" class="small-text">
                            <p class="description"><?php esc_html_e('Prefix for quote IDs (e.g., Q for Q1001)', 'cart-quote-woocommerce-email'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="quote_start_number"><?php esc_html_e('Quote Start Number', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="quote_start_number" id="quote_start_number" value="<?php echo esc_attr(get_option('cart_quote_wc_quote_start_number', '1001')); ?>" class="small-text">
                            <p class="description"><?php esc_html_e('Starting number for quote IDs', 'cart-quote-woocommerce-email'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Email Settings -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('Email Settings', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Send Emails To', 'cart-quote-woocommerce-email'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="send_to_admin" value="1" <?php checked(get_option('cart_quote_wc_send_to_admin', true)); ?>>
                                <?php esc_html_e('Admin', 'cart-quote-woocommerce-email'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="send_to_client" value="1" <?php checked(get_option('cart_quote_wc_send_to_client', true)); ?>>
                                <?php esc_html_e('Client', 'cart-quote-woocommerce-email'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="admin_email"><?php esc_html_e('Admin Email', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <input type="email" name="admin_email" id="admin_email" value="<?php echo esc_attr(get_option('cart_quote_wc_admin_email', get_option('admin_email'))); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email_subject_admin"><?php esc_html_e('Admin Email Subject', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="email_subject_admin" id="email_subject_admin" value="<?php echo esc_attr(get_option('cart_quote_wc_email_subject_admin', 'New Quote Submission #{quote_id}')); ?>" class="regular-text">
                            <p class="description"><?php esc_html_e('Use {quote_id} as placeholder', 'cart-quote-woocommerce-email'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="email_subject_client"><?php esc_html_e('Client Email Subject', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <input type="text" name="email_subject_client" id="email_subject_client" value="<?php echo esc_attr(get_option('cart_quote_wc_email_subject_client', 'Thank you for your quote request #{quote_id}')); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('PDF Attachment', 'cart-quote-woocommerce-email'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="enable_pdf" value="1" <?php checked(get_option('cart_quote_wc_enable_pdf', false)); ?>>
                                <?php esc_html_e('Enable PDF attachment (coming soon)', 'cart-quote-woocommerce-email'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Time Slot Settings -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('Meeting Settings', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="meeting_duration"><?php esc_html_e('Default Meeting Duration', 'cart-quote-woocommerce-email'); ?></label>
                        </th>
                        <td>
                            <select name="meeting_duration" id="meeting_duration">
                                <option value="30" <?php selected(get_option('cart_quote_wc_meeting_duration', '60'), '30'); ?>>
                                    <?php esc_html_e('30 minutes', 'cart-quote-woocommerce-email'); ?>
                                </option>
                                <option value="45" <?php selected(get_option('cart_quote_wc_meeting_duration', '60'), '45'); ?>>
                                    <?php esc_html_e('45 minutes', 'cart-quote-woocommerce-email'); ?>
                                </option>
                                <option value="60" <?php selected(get_option('cart_quote_wc_meeting_duration', '60'), '60'); ?>>
                                    <?php esc_html_e('60 minutes', 'cart-quote-woocommerce-email'); ?>
                                </option>
                                <option value="90" <?php selected(get_option('cart_quote_wc_meeting_duration', '60'), '90'); ?>>
                                    <?php esc_html_e('90 minutes', 'cart-quote-woocommerce-email'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Available Time Slots', 'cart-quote-woocommerce-email'); ?></th>
                        <td>
                            <div class="cart-quote-time-slots">
                                <?php
                                $time_slots = get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);
                                foreach ($time_slots as $slot) :
                                ?>
                                    <div class="cart-quote-time-slot">
                                        <input type="time" name="time_slots[]" value="<?php echo esc_attr($slot); ?>">
                                        <span class="cart-quote-remove-slot dashicons dashicons-no-alt"></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="button cart-quote-add-time-slot" style="margin-top: 10px;">
                                <?php esc_html_e('Add Time Slot', 'cart-quote-woocommerce-email'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Auto Create Event', 'cart-quote-woocommerce-email'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="auto_create_event" value="1" <?php checked(get_option('cart_quote_wc_auto_create_event', false)); ?>>
                                <?php esc_html_e('Automatically create Google Calendar event when status changes to Contacted', 'cart-quote-woocommerce-email'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Data Settings -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('Data Settings', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Delete on Uninstall', 'cart-quote-woocommerce-email'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="delete_on_uninstall" value="1" <?php checked(get_option('cart_quote_wc_delete_on_uninstall', false)); ?>>
                                <?php esc_html_e('Delete all plugin data when uninstalling', 'cart-quote-woocommerce-email'); ?>
                            </label>
                            <p class="description"><?php esc_html_e('Warning: This will permanently delete all quotes and settings.', 'cart-quote-woocommerce-email'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button(__('Save Settings', 'cart-quote-woocommerce-email')); ?>
    </form>
</div>
