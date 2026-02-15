<?php
/**
 * Frontend Quote Form Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cart-quote-form-container">
    <form class="cart-quote-form" id="cart-quote-form" method="post">
        <input type="hidden" name="action" value="cart_quote_submit">
        <input type="hidden" name="nonce" value="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">

        <h3><?php esc_html_e('Your Information', 'cart-quote-woocommerce-email'); ?></h3>

        <div class="cart-quote-form-row">
            <div class="cart-quote-field">
                <label for="billing_first_name">
                    <?php esc_html_e('First Name', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text" id="billing_first_name" name="billing_first_name" required class="cart-quote-input">
            </div>

            <div class="cart-quote-field">
                <label for="billing_last_name">
                    <?php esc_html_e('Last Name', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="text" id="billing_last_name" name="billing_last_name" required class="cart-quote-input">
            </div>
        </div>

        <div class="cart-quote-form-row">
            <div class="cart-quote-field">
                <label for="billing_email">
                    <?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="email" id="billing_email" name="billing_email" required class="cart-quote-input">
            </div>

            <div class="cart-quote-field">
                <label for="billing_phone">
                    <?php esc_html_e('Phone', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="tel" id="billing_phone" name="billing_phone" required class="cart-quote-input">
            </div>
        </div>

        <div class="cart-quote-field">
            <label for="billing_company">
                <?php esc_html_e('Company Name', 'cart-quote-woocommerce-email'); ?>
                <span class="required">*</span>
            </label>
            <input type="text" id="billing_company" name="billing_company" required class="cart-quote-input">
        </div>

        <h3><?php esc_html_e('Quote Details', 'cart-quote-woocommerce-email'); ?></h3>

        <div class="cart-quote-form-row cart-quote-meeting-fields" 
             style="display: none;" 
             role="region"
             aria-labelledby="meeting_requested_label">
            
            <h4 id="meeting_requested_label">
                <?php esc_html_e('Meeting Details', 'cart-quote-woocommerce-email'); ?>
            </h4>
            
            <div class="cart-quote-field" aria-required="false">
                <label for="preferred_date">
                    <?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="date" id="preferred_date" name="preferred_date" min="<?php echo esc_attr(date('Y-m-d')); ?>" class="cart-quote-input" aria-required="true">
                <span class="sr-only"></span>
            </div>

            <div class="cart-quote-field" aria-required="false">
                <label for="preferred_time">
                    <?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?>
                </label>
                <select id="preferred_time" name="preferred_time" 
                        aria-required="true">
                    <option value=""><?php esc_html_e('Select a time slot', 'cart-quote-woocommerce-email'); ?></option>
                    <?php foreach ($time_slots as $slot) : ?>
                        <option value="<?php echo esc_attr($slot); ?>">
                            <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($slot))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="sr-only"></span>
            </div>
        </div>

        <div class="cart-quote-field">
            <label for="contract_duration">
                <?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?>
                <span class="required">*</span>
            </label>
            <select id="contract_duration" name="contract_duration" required class="cart-quote-select">
                <option value=""><?php esc_html_e('Select duration', 'cart-quote-woocommerce-email'); ?></option>
                <option value="1_month"><?php esc_html_e('1 Month', 'cart-quote-woocommerce-email'); ?></option>
                <option value="3_months"><?php esc_html_e('3 Months', 'cart-quote-woocommerce-email'); ?></option>
                <option value="6_months"><?php esc_html_e('6 Months', 'cart-quote-woocommerce-email'); ?></option>
                <option value="custom"><?php esc_html_e('Custom (please specify)', 'cart-quote-woocommerce-email'); ?></option>
            </select>
        </div>

        <div class="cart-quote-field cart-quote-custom-duration" style="display: none;">
            <label for="custom_duration">
                <?php esc_html_e('Custom Duration', 'cart-quote-woocommerce-email'); ?>
            </label>
            <input type="text" id="custom_duration" name="custom_duration" class="cart-quote-input" placeholder="<?php esc_attr_e('e.g., 2 months, 1 year', 'cart-quote-woocommerce-email'); ?>">
        </div>

        <div class="cart-quote-field cart-quote-field-checkbox" aria-required="false">
            <label class="cart-quote-checkbox-label" for="meeting_requested">
                <input type="checkbox" 
                       name="meeting_requested" 
                       id="meeting_requested" 
                       value="1"
                       aria-required="false">
                <span><?php esc_html_e('Request a meeting', 'cart-quote-woocommerce-email'); ?></span>
            </label>
            <span class="field-hint"><?php esc_html_e('Select this option to schedule a meeting', 'cart-quote-woocommerce-email'); ?></span>
        </div>

        <div class="cart-quote-field">
            <label for="additional_notes">
                <?php esc_html_e('Additional Notes', 'cart-quote-woocommerce-email'); ?>
            </label>
            <textarea id="additional_notes" name="additional_notes" rows="4" class="cart-quote-textarea" placeholder="<?php esc_attr_e('Any additional information you\'d like to share...', 'cart-quote-woocommerce-email'); ?>"></textarea>
        </div>

        <div class="cart-quote-form-actions">
            <button type="submit" class="cart-quote-submit-btn">
                <?php esc_html_e('Submit Quote Request', 'cart-quote-woocommerce-email'); ?>
            </button>
        </div>
    </form>
</div>
