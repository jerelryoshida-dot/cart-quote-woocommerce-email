<?php
/**
 * Checkout Replacement
 *
 * Replaces WooCommerce checkout with quote submission functionality.
 * Disables payment gateways and order creation, implements custom
 * quote submission handler.
 *
 * @package CartQuoteWooCommerce\WooCommerce
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\WooCommerce;

use CartQuoteWooCommerce\Core\Debug_Logger;

class Checkout_Replacement
{
    private $repository;

    private $email_service;

    private $logger;

    public function __construct()
    {
        $this->repository = new \CartQuoteWooCommerce\Database\Quote_Repository();
        $this->email_service = new \CartQuoteWooCommerce\Emails\Email_Service();
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    public function init()
    {
        // Disable payment gateways
        add_filter('woocommerce_payment_gateways', [$this, 'disable_payment_gateways']);
        
        // Disable checkout fields that aren't needed
        add_filter('woocommerce_checkout_fields', [$this, 'custom_checkout_fields']);
        
        // Replace checkout button
        add_filter('woocommerce_order_button_text', [$this, 'change_checkout_button_text']);
        
        // Intercept checkout process
        add_action('woocommerce_checkout_process', [$this, 'intercept_checkout'], 1);
        
        // Redirect checkout page to quote form
        add_action('template_redirect', [$this, 'redirect_checkout']);
        
        // Disable WooCommerce order creation on our quote checkout
        add_filter('woocommerce_create_order', [$this, 'prevent_order_creation'], 10, 2);
        
        // Remove place order action
        add_action('woocommerce_before_checkout_form', [$this, 'override_checkout_form']);
        
        // Add quote form to checkout page
        add_action('woocommerce_checkout_before_customer_details', [$this, 'add_quote_fields']);
        
        // Clear cart after submission
        add_action('cart_quote_after_submission', [$this, 'clear_cart_after_submission']);
    }

    /**
     * Disable all payment gateways
     *
     * @param array $gateways Payment gateways
     * @return array
     */
    public function disable_payment_gateways($gateways)
    {
        if ($this->is_quote_checkout()) {
            return [];
        }
        return $gateways;
    }

    /**
     * Check if this is a quote checkout context
     *
     * @return bool
     */
    private function is_quote_checkout()
    {
        if (!function_exists('is_checkout') || !function_exists('is_wc_endpoint_url')) {
            return false;
        }
        return is_checkout() && !is_wc_endpoint_url('order-received');
    }

    /**
     * Customize checkout fields
     *
     * @param array $fields Checkout fields
     * @return array
     */
    public function custom_checkout_fields($fields)
    {
        if (!$this->is_quote_checkout()) {
            return $fields;
        }

        // Remove billing fields we don't need
        unset($fields['billing']['billing_address_1']);
        unset($fields['billing']['billing_address_2']);
        unset($fields['billing']['billing_city']);
        unset($fields['billing']['billing_postcode']);
        unset($fields['billing']['billing_country']);
        unset($fields['billing']['billing_state']);
        
        // Remove shipping fields entirely
        unset($fields['shipping']);
        
        // Remove account fields
        unset($fields['account']);
        
        // Remove order comments
        unset($fields['order']['order_comments']);

        // Add quote-specific fields
        $fields['billing']['billing_company'] = [
            'type' => 'text',
            'label' => __('Company Name', 'cart-quote-woocommerce-email'),
            'required' => true,
            'class' => ['form-row-wide'],
            'priority' => 30,
        ];

        $fields['billing']['billing_phone'] = [
            'type' => 'tel',
            'label' => __('Phone', 'cart-quote-woocommerce-email'),
            'required' => true,
            'class' => ['form-row-wide'],
            'priority' => 40,
            'validate' => ['phone'],
        ];

        return $fields;
    }

    /**
     * Change checkout button text
     *
     * @return string
     */
    public function change_checkout_button_text()
    {
        return __('Submit Quote Request', 'cart-quote-woocommerce-email');
    }

    /**
     * Intercept checkout process
     *
     * @return void
     */
    public function intercept_checkout()
    {
        // This runs during WooCommerce's checkout process
        // We'll use our AJAX handler instead for better control
    }

    /**
     * Redirect checkout to quote form if needed
     *
     * @return void
     */
    public function redirect_checkout()
    {
        // Only on checkout page, not order received
        if (!function_exists('is_checkout') || !function_exists('is_wc_endpoint_url')) {
            return;
        }
        
        if (!is_checkout() || is_wc_endpoint_url('order-received')) {
            return;
        }

        // Check if cart is empty
        if (function_exists('WC') && WC()->cart && WC()->cart->is_empty()) {
            return;
        }
    }

    /**
     * Prevent WooCommerce order creation
     *
     * @param int|null $order_id Order ID
     * @param \WC_Checkout $checkout Checkout instance
     * @return int|null
     */
    public function prevent_order_creation($order_id, $checkout)
    {
        if ($this->is_quote_checkout()) {
            return null; // Prevent order creation
        }
        return $order_id;
    }

    /**
     * Override checkout form behavior
     *
     * @return void
     */
    public function override_checkout_form()
    {
        if (!$this->is_quote_checkout()) {
            return;
        }
        ?>
        <input type="hidden" name="cart_quote_checkout" value="1">
        <?php
    }

    /**
     * Add quote-specific fields to checkout
     *
     * @return void
     */
    public function add_quote_fields()
    {
        if (!$this->is_quote_checkout()) {
            return;
        }

        $time_slots = get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);
        ?>
        <div class="cart-quote-additional-fields">
            <h3><?php esc_html_e('Quote Details', 'cart-quote-woocommerce-email'); ?></h3>
            
            <p class="form-row form-row-wide">
                <label for="preferred_date">
                    <?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <input type="date" 
                       name="preferred_date" 
                       id="preferred_date" 
                       class="input-text" 
                       required
                       min="<?php echo esc_attr(date('Y-m-d')); ?>">
            </p>
            
            <p class="form-row form-row-wide">
                <label for="preferred_time">
                    <?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?>
                </label>
                <select name="preferred_time" id="preferred_time" class="select">
                    <option value=""><?php esc_html_e('Select a time slot', 'cart-quote-woocommerce-email'); ?></option>
                    <?php foreach ($time_slots as $slot) : ?>
                        <option value="<?php echo esc_attr($slot); ?>">
                            <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($slot))); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            
            <p class="form-row form-row-wide">
                <label for="contract_duration">
                    <?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?>
                    <span class="required">*</span>
                </label>
                <select name="contract_duration" id="contract_duration" class="select" required>
                    <option value=""><?php esc_html_e('Select duration', 'cart-quote-woocommerce-email'); ?></option>
                    <option value="1_month"><?php esc_html_e('1 Month', 'cart-quote-woocommerce-email'); ?></option>
                    <option value="3_months"><?php esc_html_e('3 Months', 'cart-quote-woocommerce-email'); ?></option>
                    <option value="6_months"><?php esc_html_e('6 Months', 'cart-quote-woocommerce-email'); ?></option>
                    <option value="custom"><?php esc_html_e('Custom (please specify)', 'cart-quote-woocommerce-email'); ?></option>
                </select>
            </p>
            
            <p class="form-row form-row-wide" id="custom_duration_field" style="display: none;">
                <label for="custom_duration">
                    <?php esc_html_e('Custom Duration', 'cart-quote-woocommerce-email'); ?>
                </label>
                <input type="text" name="custom_duration" id="custom_duration" class="input-text" placeholder="<?php esc_attr_e('e.g., 2 months, 1 year', 'cart-quote-woocommerce-email'); ?>">
            </p>
            
            <p class="form-row form-row-wide">
                <label>
                    <input type="checkbox" name="meeting_requested" id="meeting_requested" value="1">
                    <?php esc_html_e('Request a meeting', 'cart-quote-woocommerce-email'); ?>
                </label>
            </p>
            
            <p class="form-row form-row-wide">
                <label for="additional_notes">
                    <?php esc_html_e('Additional Notes', 'cart-quote-woocommerce-email'); ?>
                </label>
                <textarea name="additional_notes" id="additional_notes" class="input-text" rows="4" placeholder="<?php esc_attr_e('Any additional information you\'d like to share...', 'cart-quote-woocommerce-email'); ?>"></textarea>
            </p>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var contractDuration = document.getElementById('contract_duration');
            var customField = document.getElementById('custom_duration_field');
            
            if (contractDuration && customField) {
                contractDuration.addEventListener('change', function() {
                    customField.style.display = this.value === 'custom' ? 'block' : 'none';
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Handle quote submission via AJAX
     *
     * @return void
     */
    public function handle_quote_submission()
    {
        try {
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cart_quote_frontend_nonce')) {
                $this->logger->warning('Security verification failed', ['ip' => $this->get_client_ip()]);
                wp_send_json_error(['message' => __('Security verification failed.', 'cart-quote-woocommerce-email')]);
                return;
            }

            if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
                $this->logger->error('Quote submission failed: Cart is empty');
                wp_send_json_error(['message' => __('Your cart is empty.', 'cart-quote-woocommerce-email')]);
                return;
            }

            $form_data = $this->sanitize_form_data($_POST);

            $validation = $this->validate_form_data($form_data);
            if (is_wp_error($validation)) {
                $this->logger->warning('Quote validation failed', [
                    'errors' => $validation->get_error_messages(),
                ]);
                wp_send_json_error(['message' => $validation->get_error_message()]);
                return;
            }

            $cart_data = $this->prepare_cart_data();

            $quote_id = $this->repository->generate_quote_id();

            $insert_data = [
                'quote_id' => $quote_id,
                'customer_name' => $form_data['billing_first_name'] . ' ' . $form_data['billing_last_name'],
                'email' => $form_data['billing_email'],
                'phone' => isset($form_data['billing_phone']) ? $form_data['billing_phone'] : '',
                'company_name' => isset($form_data['billing_company']) ? $form_data['billing_company'] : '',
                'preferred_date' => isset($form_data['preferred_date']) ? $form_data['preferred_date'] : '',
                'preferred_time' => isset($form_data['preferred_time']) ? $form_data['preferred_time'] : '',
                'contract_duration' => isset($form_data['contract_duration_final']) ? $form_data['contract_duration_final'] : '',
                'meeting_requested' => !empty($form_data['meeting_requested']) ? 1 : 0,
                'additional_notes' => isset($form_data['additional_notes']) ? $form_data['additional_notes'] : '',
                'cart_data' => $cart_data,
                'subtotal' => WC()->cart->get_subtotal(),
            ];

            $insert_id = $this->repository->insert($insert_data);

            if (!$insert_id) {
                $this->logger->error('Failed to save quote to database', [
                    'quote_id' => $quote_id,
                    'form_data' => $form_data,
                ]);
                wp_send_json_error(['message' => __('Failed to save quote. Please try again.', 'cart-quote-woocommerce-email')]);
                return;
            }

            $this->logger->info('Quote submitted successfully', [
                'quote_id' => $quote_id,
                'quote_id' => $quote_id,
                'customer_name' => $form_data['billing_first_name'] . ' ' . $form_data['billing_last_name'],
                'email' => $form_data['billing_email'],
            ]);

            $this->email_service->send_quote_emails($insert_id);

            do_action('cart_quote_after_submission', $insert_id, $quote_id, $insert_data);

            $redirect_url = add_query_arg([
                'quote_submitted' => '1',
                'quote_id' => $quote_id,
            ], function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url());

            wp_send_json_success([
                'message' => __('Quote submitted successfully!', 'cart-quote-woocommerce-email'),
                'quote_id' => $quote_id,
                'redirect_url' => $redirect_url,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_quote_submission', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_data' => $_POST,
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    private function get_client_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }

    /**
     * Sanitize form data
     *
     * @param array $data Raw form data
     * @return array
     */
    private function sanitize_form_data($data)
    {
        $sanitized = [];

        // Billing fields
        $sanitized['billing_first_name'] = isset($data['billing_first_name']) ? sanitize_text_field($data['billing_first_name']) : '';
        $sanitized['billing_last_name'] = isset($data['billing_last_name']) ? sanitize_text_field($data['billing_last_name']) : '';
        $sanitized['billing_email'] = isset($data['billing_email']) ? sanitize_email($data['billing_email']) : '';
        $sanitized['billing_phone'] = isset($data['billing_phone']) ? sanitize_text_field($data['billing_phone']) : '';
        $sanitized['billing_company'] = isset($data['billing_company']) ? sanitize_text_field($data['billing_company']) : '';

        // Quote-specific fields
        $sanitized['preferred_date'] = isset($data['preferred_date']) ? sanitize_text_field($data['preferred_date']) : '';
        $sanitized['preferred_time'] = isset($data['preferred_time']) ? sanitize_text_field($data['preferred_time']) : '';
        
        $contract_duration = isset($data['contract_duration']) ? sanitize_text_field($data['contract_duration']) : '';
        if ($contract_duration === 'custom') {
            $sanitized['contract_duration_final'] = isset($data['custom_duration']) ? sanitize_text_field($data['custom_duration']) : '';
        } else {
            $sanitized['contract_duration_final'] = $contract_duration;
        }
        $sanitized['contract_duration'] = $contract_duration;
        
        $sanitized['meeting_requested'] = !empty($data['meeting_requested']) ? 1 : 0;
        $sanitized['additional_notes'] = isset($data['additional_notes']) ? sanitize_textarea_field($data['additional_notes']) : '';

        return $sanitized;
    }

    /**
     * Validate form data
     *
     * @param array $data Sanitized form data
     * @return true|\WP_Error
     */
    private function validate_form_data($data)
    {
        $errors = new \WP_Error();

        if (empty($data['billing_first_name'])) {
            $errors->add('required', __('First name is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['billing_last_name'])) {
            $errors->add('required', __('Last name is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['billing_email']) || !is_email($data['billing_email'])) {
            $errors->add('required', __('Valid email is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['billing_phone'])) {
            $errors->add('required', __('Phone number is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['billing_company'])) {
            $errors->add('required', __('Company name is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['preferred_date'])) {
            $errors->add('required', __('Preferred start date is required.', 'cart-quote-woocommerce-email'));
        }

        if (empty($data['contract_duration'])) {
            $errors->add('required', __('Contract duration is required.', 'cart-quote-woocommerce-email'));
        }

        if ($data['contract_duration'] === 'custom' && empty($data['contract_duration_final'])) {
            $errors->add('required', __('Please specify custom contract duration.', 'cart-quote-woocommerce-email'));
        }

        if ($errors->has_errors()) {
            return $errors;
        }

        return true;
    }

    /**
     * Prepare cart data for storage
     *
     * @return array
     */
    private function prepare_cart_data()
    {
        $cart_data = [];

        if (!function_exists('WC') || !WC()->cart) {
            return $cart_data;
        }

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            
            $cart_data[] = [
                'product_id' => $cart_item['product_id'],
                'variation_id' => isset($cart_item['variation_id']) ? $cart_item['variation_id'] : 0,
                'product_name' => $product->get_name(),
                'product_sku' => $product->get_sku(),
                'quantity' => $cart_item['quantity'],
                'price' => (float) $product->get_price(),
                'line_total' => (float) $cart_item['line_total'],
                'meta_data' => $this->get_item_meta($cart_item),
            ];
        }

        return $cart_data;
    }

    /**
     * Get item meta data
     *
     * @param array $cart_item Cart item
     * @return array
     */
    private function get_item_meta($cart_item)
    {
        $meta = [];
        
        if (!empty($cart_item['variation'])) {
            foreach ($cart_item['variation'] as $key => $value) {
                $meta[$key] = $value;
            }
        }

        return $meta;
    }

    /**
     * Clear cart after submission
     *
     * @param int $insert_id Quote insert ID
     * @return void
     */
    public function clear_cart_after_submission($insert_id)
    {
        if (function_exists('WC') && WC()->cart) {
            WC()->cart->empty_cart();
        }
        if (function_exists('WC') && WC()->session) {
            WC()->session->set('cart', []);
        }
    }
}
