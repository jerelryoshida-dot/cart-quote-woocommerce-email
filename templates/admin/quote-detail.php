<?php
/**
 * Admin Quote Detail Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cart-quote-detail">
    <div class="cart-quote-detail-header">
        <div>
            <h2>
                <?php echo esc_html(sprintf(__('Quote %s', 'cart-quote-woocommerce-email'), $quote->quote_id)); ?>
            </h2>
            <p class="cart-quote-meta">
                <?php echo esc_html(sprintf(__('Submitted on %s', 'cart-quote-woocommerce-email'), date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($quote->created_at)))); ?>
            </p>
        </div>
        <div>
            <select class="cart-quote-status-select" data-quote-id="<?php echo esc_attr($quote->id); ?>" data-old-status="<?php echo esc_attr($quote->status); ?>">
                <option value="pending" <?php selected($quote->status, 'pending'); ?>>
                    <?php esc_html_e('Pending', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="contacted" <?php selected($quote->status, 'contacted'); ?>>
                    <?php esc_html_e('Contacted', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="closed" <?php selected($quote->status, 'closed'); ?>>
                    <?php esc_html_e('Closed', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="canceled" <?php selected($quote->status, 'canceled'); ?>>
                    <?php esc_html_e('Canceled', 'cart-quote-woocommerce-email'); ?>
                </option>
            </select>

            <?php if ($quote->meeting_requested && !$quote->calendar_synced) : ?>
                <button type="button" class="button button-primary cart-quote-create-event" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                    <?php esc_html_e('Create Google Event', 'cart-quote-woocommerce-email'); ?>
                </button>
            <?php endif; ?>

            <button type="button" class="button cart-quote-resend-email" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                <?php esc_html_e('Resend Email', 'cart-quote-woocommerce-email'); ?>
            </button>

            <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-manager')); ?>" class="button">
                <?php esc_html_e('Back to List', 'cart-quote-woocommerce-email'); ?>
            </a>
        </div>
    </div>

    <div class="cart-quote-detail-grid">
        <div class="cart-quote-main">
            <!-- Customer Information -->
            <div class="cart-quote-detail-card">
                <h3><?php esc_html_e('Customer Information', 'cart-quote-woocommerce-email'); ?></h3>
                <div class="card-content">
                    <dl class="cart-quote-customer-info">
                        <dt><?php esc_html_e('Name', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd><?php echo esc_html($quote->customer_name); ?></dd>

                        <dt><?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd>
                            <a href="mailto:<?php echo esc_attr($quote->email); ?>">
                                <?php echo esc_html($quote->email); ?>
                            </a>
                        </dd>

                        <dt><?php esc_html_e('Phone', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd>
                            <?php if ($quote->phone) : ?>
                                <a href="tel:<?php echo esc_attr($quote->phone); ?>">
                                    <?php echo esc_html($quote->phone); ?>
                                </a>
                            <?php else : ?>
                                <em><?php esc_html_e('Not provided', 'cart-quote-woocommerce-email'); ?></em>
                            <?php endif; ?>
                        </dd>

                        <dt><?php esc_html_e('Company', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd><?php echo esc_html($quote->company_name); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Quote Details -->
            <div class="cart-quote-detail-card">
                <h3><?php esc_html_e('Quote Details', 'cart-quote-woocommerce-email'); ?></h3>
                <div class="card-content">
                    <dl class="cart-quote-customer-info">
                        <dt><?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd>
                            <?php if ($quote->preferred_date) : ?>
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($quote->preferred_date))); ?>
                            <?php else : ?>
                                <em><?php esc_html_e('Not specified', 'cart-quote-woocommerce-email'); ?></em>
                            <?php endif; ?>
                        </dd>

                        <dt><?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd>
                            <?php if ($quote->preferred_time) : ?>
                                <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($quote->preferred_time))); ?>
                            <?php else : ?>
                                <em><?php esc_html_e('Not specified', 'cart-quote-woocommerce-email'); ?></em>
                            <?php endif; ?>
                        </dd>

                        <dt><?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd><?php echo esc_html(\CartQuoteWooCommerce\Emails\Email_Service::format_duration($quote->contract_duration)); ?></dd>

                        <dt><?php esc_html_e('Meeting Requested', 'cart-quote-woocommerce-email'); ?></dt>
                        <dd>
                            <?php if ($quote->meeting_requested) : ?>
                                <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                <?php esc_html_e('Yes', 'cart-quote-woocommerce-email'); ?>
                                <?php if ($quote->calendar_synced) : ?>
                                    <br><small><?php esc_html_e('Google Event Created', 'cart-quote-woocommerce-email'); ?></small>
                                    <?php if ($quote->google_event_id) : ?>
                                        <code><?php echo esc_html($quote->google_event_id); ?></code>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php else : ?>
                                <span class="dashicons dashicons-no" style="color: #999;"></span>
                                <?php esc_html_e('No', 'cart-quote-woocommerce-email'); ?>
                            <?php endif; ?>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Cart Items -->
            <div class="cart-quote-detail-card">
                <h3><?php esc_html_e('Products / Services', 'cart-quote-woocommerce-email'); ?></h3>
                <div class="card-content">
                    <table class="cart-quote-items-table">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Product', 'cart-quote-woocommerce-email'); ?></th>
                                <th><?php esc_html_e('SKU', 'cart-quote-woocommerce-email'); ?></th>
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
                                        <td><?php echo esc_html($item['product_sku'] ?? '-'); ?></td>
                                        <td><?php echo esc_html($item['quantity']); ?></td>
                                        <td><?php echo wc_price($item['price']); ?></td>
                                        <td><?php echo wc_price($item['line_total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; font-weight: 600;">
                                    <?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?>
                                </td>
                                <td style="font-weight: 700;">
                                    <?php echo wc_price($quote->subtotal); ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="cart-quote-sidebar">
            <!-- Admin Notes -->
            <div class="cart-quote-detail-card">
                <h3><?php esc_html_e('Admin Notes', 'cart-quote-woocommerce-email'); ?></h3>
                <div class="card-content cart-quote-admin-notes">
                    <textarea id="admin_notes" rows="6"><?php echo esc_textarea($quote->admin_notes ?? ''); ?></textarea>
                    <button type="button" class="button cart-quote-save-notes" data-quote-id="<?php echo esc_attr($quote->id); ?>">
                        <?php esc_html_e('Save Notes', 'cart-quote-woocommerce-email'); ?>
                    </button>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="cart-quote-detail-card">
                <h3><?php esc_html_e('Activity Log', 'cart-quote-woocommerce-email'); ?></h3>
                <div class="card-content">
                    <?php if (empty($logs)) : ?>
                        <p><?php esc_html_e('No activity recorded yet.', 'cart-quote-woocommerce-email'); ?></p>
                    <?php else : ?>
                        <ul class="cart-quote-activity-log">
                            <?php foreach ($logs as $log) : ?>
                                <li>
                                    <strong><?php echo esc_html(ucwords(str_replace('_', ' ', $log->action))); ?></strong>
                                    <span class="log-time">
                                        <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($log->created_at))); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
