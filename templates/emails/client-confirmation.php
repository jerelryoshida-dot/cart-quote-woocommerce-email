<?php
/**
 * Client Confirmation Email Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h2><?php esc_html_e('Thank You for Your Quote Request', 'cart-quote-woocommerce-email'); ?></h2>

<p>
    <?php echo esc_html(sprintf(__('Dear %s,', 'cart-quote-woocommerce-email'), $quote->customer_name)); ?>
</p>

<p>
    <?php esc_html_e('Thank you for submitting your quote request. We have received your inquiry and will get back to you shortly.', 'cart-quote-woocommerce-email'); ?>
</p>

<div class="info-box">
    <h3><?php esc_html_e('Your Quote Details', 'cart-quote-woocommerce-email'); ?></h3>
    <dl>
        <dt><?php esc_html_e('Quote Reference', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><strong><?php echo esc_html($quote->quote_id); ?></strong></dd>

        <dt><?php esc_html_e('Submission Date', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($quote->created_at))); ?></dd>

        <dt><?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->preferred_date) : ?>
                <?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_date($quote->preferred_date)); ?>
            <?php else : ?>
                <?php esc_html_e('Not specified', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>

        <dt><?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_duration($quote->contract_duration)); ?></dd>

        <dt><?php esc_html_e('Meeting Request', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->meeting_requested) : ?>
                <?php esc_html_e('Yes - We will contact you to schedule a meeting', 'cart-quote-woocommerce-email'); ?>
            <?php else : ?>
                <?php esc_html_e('No', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>
    </dl>
</div>

<h3><?php esc_html_e('Your Requested Services', 'cart-quote-woocommerce-email'); ?></h3>

<table>
    <thead>
        <tr>
            <th><?php esc_html_e('Product / Service', 'cart-quote-woocommerce-email'); ?></th>
            <th><?php esc_html_e('Qty', 'cart-quote-woocommerce-email'); ?></th>
            <th><?php esc_html_e('Total', 'cart-quote-woocommerce-email'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($quote->cart_data) && is_array($quote->cart_data)) : ?>
            <?php foreach ($quote->cart_data as $item) : ?>
                <tr>
                    <td><?php echo esc_html($item['product_name']); ?></td>
                    <td><?php echo esc_html($item['quantity']); ?></td>
                    <td><?php echo wp_kses_post(wc_price($item['line_total'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="2"><?php esc_html_e('Estimated Total', 'cart-quote-woocommerce-email'); ?></td>
            <td><?php echo wp_kses_post(wc_price($quote->subtotal)); ?></td>
        </tr>
    </tfoot>
</table>

<p>
    <em>
        <?php esc_html_e('Note: This is an estimated total. Final pricing may vary based on contract terms and additional requirements.', 'cart-quote-woocommerce-email'); ?>
    </em>
</p>

<p>
    <?php esc_html_e('If you have any questions or need to make changes to your request, please reply to this email or contact us directly.', 'cart-quote-woocommerce-email'); ?>
</p>

<p>
    <?php esc_html_e('We look forward to working with you!', 'cart-quote-woocommerce-email'); ?>
</p>

<p>
    <strong><?php echo esc_html(get_bloginfo('name')); ?></strong><br>
    <?php echo esc_html(get_option('admin_email')); ?>
</p>
