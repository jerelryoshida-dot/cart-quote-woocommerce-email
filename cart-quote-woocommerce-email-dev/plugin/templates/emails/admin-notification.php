<?php
/**
 * Admin Notification Email Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<h2><?php esc_html_e('New Quote Submission', 'cart-quote-woocommerce-email'); ?></h2>

<p>
    <?php esc_html_e('A new quote has been submitted on your website.', 'cart-quote-woocommerce-email'); ?>
</p>

<div class="info-box">
    <h3><?php esc_html_e('Quote Information', 'cart-quote-woocommerce-email'); ?></h3>
    <dl>
        <dt><?php esc_html_e('Quote ID', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><strong><?php echo esc_html($quote->quote_id); ?></strong></dd>

        <dt><?php esc_html_e('Status', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html(ucfirst($quote->status)); ?></dd>

        <dt><?php esc_html_e('Submitted', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quote->created_at))); ?></dd>
    </dl>
</div>

<div class="info-box">
    <h3><?php esc_html_e('Customer Information', 'cart-quote-woocommerce-email'); ?></h3>
    <dl>
        <dt><?php esc_html_e('Name', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html($quote->customer_name); ?></dd>

        <dt><?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><a href="mailto:<?php echo esc_attr($quote->email); ?>"><?php echo esc_html($quote->email); ?></a></dd>

        <dt><?php esc_html_e('Phone', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->phone) : ?>
                <a href="tel:<?php echo esc_attr($quote->phone); ?>"><?php echo esc_html($quote->phone); ?></a>
            <?php else : ?>
                <?php esc_html_e('Not provided', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>

        <dt><?php esc_html_e('Company', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html($quote->company_name); ?></dd>
    </dl>
</div>

<div class="info-box">
    <h3><?php esc_html_e('Quote Details', 'cart-quote-woocommerce-email'); ?></h3>
    <dl>
        <dt><?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->preferred_date) : ?>
                <?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_date($quote->preferred_date)); ?>
            <?php else : ?>
                <?php esc_html_e('Not specified', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>

        <dt><?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->preferred_time) : ?>
                <?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_time($quote->preferred_time)); ?>
            <?php else : ?>
                <?php esc_html_e('Not specified', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>

        <dt><?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?></dt>
        <dd><?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_duration($quote->contract_duration)); ?></dd>

        <dt><?php esc_html_e('Meeting Requested', 'cart-quote-woocommerce-email'); ?></dt>
        <dd>
            <?php if ($quote->meeting_requested) : ?>
                <strong style="color: #46b450;"><?php esc_html_e('Yes', 'cart-quote-woocommerce-email'); ?></strong>
            <?php else : ?>
                <?php esc_html_e('No', 'cart-quote-woocommerce-email'); ?>
            <?php endif; ?>
        </dd>
    </dl>
</div>

<h3><?php esc_html_e('Products / Services Requested', 'cart-quote-woocommerce-email'); ?></h3>

<table>
    <thead>
        <tr>
            <th><?php esc_html_e('Product', 'cart-quote-woocommerce-email'); ?></th>
            <th><?php esc_html_e('Qty', 'cart-quote-woocommerce-email'); ?></th>
            <th><?php esc_html_e('Price', 'cart-quote-woocommerce-email'); ?></th>
            <th><?php esc_html_e('Total', 'cart-quote-woocommerce-email'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($quote->cart_data) && is_array($quote->cart_data)) : ?>
            <?php foreach ($quote->cart_data as $item) : ?>
                <tr>
                    <td><?php echo esc_html($item['product_name']); ?></td>
                    <td><?php echo esc_html($item['quantity']); ?></td>
                    <td><?php echo wp_kses_post(wc_price($item['price'])); ?></td>
                    <td><?php echo wp_kses_post(wc_price($item['line_total'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <tr class="total-row">
            <td colspan="3"><?php esc_html_e('Subtotal', 'cart-quote-woocommerce-email'); ?></td>
            <td><?php echo wp_kses_post(wc_price($quote->subtotal)); ?></td>
        </tr>
    </tfoot>
</table>

<p style="text-align: center;">
    <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-manager&action=view&id=' . $quote->id)); ?>" class="button">
        <?php esc_html_e('View Quote in Dashboard', 'cart-quote-woocommerce-email'); ?>
    </a>
</p>
