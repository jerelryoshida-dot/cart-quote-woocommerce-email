<?php
/**
 * Admin Google Calendar Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$google_service = new \CartQuoteWooCommerce\Google\Google_Calendar_Service();
$is_connected = $google_service->is_connected();
$is_configured = $google_service->is_configured();
?>
<div class="wrap cart-quote-google">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['connected']) && $_GET['connected'] === '1') : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php esc_html_e('Google Calendar connected successfully!', 'cart-quote-woocommerce-email'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])) : ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                $error = sanitize_text_field($_GET['error']);
                if ($error === 'access_denied') {
                    esc_html_e('Google authorization was denied. Please try again.', 'cart-quote-woocommerce-email');
                } else {
                    esc_html_e('An error occurred during Google authorization. Please try again.', 'cart-quote-woocommerce-email');
                }
                ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="cart-quote-settings-form">
        <!-- Configuration -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('Google API Configuration', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <form method="post" action="options.php">
                    <?php settings_fields('cart_quote_google_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="google_client_id"><?php esc_html_e('Client ID', 'cart-quote-woocommerce-email'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="cart_quote_wc_google_client_id" id="google_client_id" value="<?php echo esc_attr(get_option('cart_quote_wc_google_client_id')); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Get your Client ID from Google Cloud Console', 'cart-quote-woocommerce-email'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="google_client_secret"><?php esc_html_e('Client Secret', 'cart-quote-woocommerce-email'); ?></label>
                            </th>
                            <td>
                                <input type="password" name="cart_quote_wc_google_client_secret" id="google_client_secret" value="<?php echo esc_attr(get_option('cart_quote_wc_google_client_secret')); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Your OAuth 2.0 Client Secret', 'cart-quote-woocommerce-email'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="google_calendar_id"><?php esc_html_e('Calendar ID', 'cart-quote-woocommerce-email'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="cart_quote_wc_google_calendar_id" id="google_calendar_id" value="<?php echo esc_attr(get_option('cart_quote_wc_google_calendar_id', 'primary')); ?>" class="regular-text">
                                <p class="description">
                                    <?php esc_html_e('Use "primary" for your main calendar, or enter a specific calendar ID', 'cart-quote-woocommerce-email'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>

                    <?php submit_button(__('Save Configuration', 'cart-quote-woocommerce-email')); ?>
                </form>
            </div>
        </div>

        <!-- Connection Status -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('Connection Status', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <?php if (!$is_configured) : ?>
                    <div class="cart-quote-google-connect">
                        <h3><?php esc_html_e('Configuration Required', 'cart-quote-woocommerce-email'); ?></h3>
                        <p>
                            <?php esc_html_e('Please enter your Google API credentials above and save to connect your Google Calendar.', 'cart-quote-woocommerce-email'); ?>
                        </p>
                        <h4><?php esc_html_e('Setup Instructions:', 'cart-quote-woocommerce-email'); ?></h4>
                        <ol>
                            <li><?php esc_html_e('Go to Google Cloud Console (console.cloud.google.com)', 'cart-quote-woocommerce-email'); ?></li>
                            <li><?php esc_html_e('Create a new project or select existing', 'cart-quote-woocommerce-email'); ?></li>
                            <li><?php esc_html_e('Enable Google Calendar API', 'cart-quote-woocommerce-email'); ?></li>
                            <li><?php esc_html_e('Configure OAuth consent screen', 'cart-quote-woocommerce-email'); ?></li>
                            <li><?php esc_html_e('Create OAuth 2.0 Client ID credentials', 'cart-quote-woocommerce-email'); ?></li>
                            <li><?php esc_html_e('Add this redirect URI:', 'cart-quote-woocommerce-email'); ?>
                                <br><code><?php echo esc_html($google_service->get_redirect_uri()); ?></code>
                            </li>
                            <li><?php esc_html_e('Copy Client ID and Secret to the fields above', 'cart-quote-woocommerce-email'); ?></li>
                        </ol>
                    </div>
                <?php elseif ($is_connected) : ?>
                    <div class="cart-quote-google-status">
                        <span class="status-indicator connected"></span>
                        <span><?php esc_html_e('Connected to Google Calendar', 'cart-quote-woocommerce-email'); ?></span>
                    </div>
                    <p>
                        <?php
                        printf(
                            esc_html__('Calendar ID: %s', 'cart-quote-woocommerce-email'),
                            '<code>' . esc_html(get_option('cart_quote_wc_google_calendar_id', 'primary')) . '</code>'
                        );
                        ?>
                    </p>
                    <p>
                        <button type="button" class="button cart-quote-google-disconnect">
                            <?php esc_html_e('Disconnect Google Calendar', 'cart-quote-woocommerce-email'); ?>
                        </button>
                    </p>
                <?php else : ?>
                    <div class="cart-quote-google-status">
                        <span class="status-indicator disconnected"></span>
                        <span><?php esc_html_e('Not connected', 'cart-quote-woocommerce-email'); ?></span>
                    </div>
                    <p>
                        <a href="<?php echo esc_url($google_service->get_auth_url()); ?>" class="button button-primary">
                            <?php esc_html_e('Connect Google Calendar', 'cart-quote-woocommerce-email'); ?>
                        </a>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- How it works -->
        <div class="cart-quote-settings-section">
            <h3><?php esc_html_e('How It Works', 'cart-quote-woocommerce-email'); ?></h3>
            <div class="inside">
                <ul style="list-style: disc; padding-left: 20px;">
                    <li><?php esc_html_e('When a customer submits a quote with "Request Meeting" checked, the quote is saved with meeting_requested = Yes', 'cart-quote-woocommerce-email'); ?></li>
                    <li><?php esc_html_e('The quote status is set to "Pending"', 'cart-quote-woocommerce-email'); ?></li>
                    <li><?php esc_html_e('When you change the status to "Contacted" (or click "Create Google Event"), a calendar event is created', 'cart-quote-woocommerce-email'); ?></li>
                    <li><?php esc_html_e('The customer is added as an attendee and receives a meeting invite', 'cart-quote-woocommerce-email'); ?></li>
                    <li><?php esc_html_e('The event status is set to "Tentative" by default', 'cart-quote-woocommerce-email'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
