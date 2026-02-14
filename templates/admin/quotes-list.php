<?php
/**
 * Admin Quotes List Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap cart-quote-manager">
    <div class="cart-quote-dashboard-header">
        <h1>
            <?php echo esc_html(get_admin_page_title()); ?>
            <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-settings')); ?>" class="page-title-action">
                <?php esc_html_e('Settings', 'cart-quote-woocommerce-email'); ?>
            </a>
        </h1>
    </div>

    <!-- Statistics -->
    <div class="cart-quote-stats">
        <div class="cart-quote-stat-card">
            <div class="stat-number"><?php echo esc_html($stats['total']); ?></div>
            <div class="stat-label"><?php esc_html_e('Total Quotes', 'cart-quote-woocommerce-email'); ?></div>
        </div>
        <div class="cart-quote-stat-card stat-pending">
            <div class="stat-number"><?php echo esc_html($stats['pending']); ?></div>
            <div class="stat-label"><?php esc_html_e('Pending', 'cart-quote-woocommerce-email'); ?></div>
        </div>
        <div class="cart-quote-stat-card stat-contacted">
            <div class="stat-number"><?php echo esc_html($stats['contacted']); ?></div>
            <div class="stat-label"><?php esc_html_e('Contacted', 'cart-quote-woocommerce-email'); ?></div>
        </div>
        <div class="cart-quote-stat-card stat-closed">
            <div class="stat-number"><?php echo esc_html($stats['closed']); ?></div>
            <div class="stat-label"><?php esc_html_e('Closed', 'cart-quote-woocommerce-email'); ?></div>
        </div>
        <div class="cart-quote-stat-card">
            <div class="stat-number"><?php echo esc_html($stats['meetings_requested']); ?></div>
            <div class="stat-label"><?php esc_html_e('Meetings Requested', 'cart-quote-woocommerce-email'); ?></div>
        </div>
    </div>

    <!-- Filters -->
    <div class="cart-quote-filters">
        <form method="get">
            <input type="hidden" name="page" value="cart-quote-manager">

            <select name="status" id="filter-status">
                <option value=""><?php esc_html_e('All Statuses', 'cart-quote-woocommerce-email'); ?></option>
                <option value="pending" <?php selected($status, 'pending'); ?>>
                    <?php esc_html_e('Pending', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="contacted" <?php selected($status, 'contacted'); ?>>
                    <?php esc_html_e('Contacted', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="closed" <?php selected($status, 'closed'); ?>>
                    <?php esc_html_e('Closed', 'cart-quote-woocommerce-email'); ?>
                </option>
                <option value="canceled" <?php selected($status, 'canceled'); ?>>
                    <?php esc_html_e('Canceled', 'cart-quote-woocommerce-email'); ?>
                </option>
            </select>

            <input type="text" name="s" placeholder="<?php esc_attr_e('Search quotes...', 'cart-quote-woocommerce-email'); ?>" value="<?php echo esc_attr($search); ?>">

            <button type="button" class="button cart-quote-export-csv">
                <?php esc_html_e('Export CSV', 'cart-quote-woocommerce-email'); ?>
            </button>
        </form>
    </div>

    <!-- Quotes Table -->
    <div class="cart-quote-list-table">
        <table>
            <thead>
                <tr>
                    <th><?php esc_html_e('Quote ID', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Customer', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Company', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Subtotal', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Meeting', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Status', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Date', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Actions', 'cart-quote-woocommerce-email'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($quotes)) : ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px;">
                            <?php esc_html_e('No quotes found.', 'cart-quote-woocommerce-email'); ?>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($quotes as $quote) : ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-manager&action=view&id=' . $quote->id)); ?>">
                                        <?php echo esc_html($quote->quote_id); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo esc_html($quote->customer_name); ?></td>
                            <td><?php echo esc_html($quote->company_name); ?></td>
                            <td>
                                <a href="mailto:<?php echo esc_attr($quote->email); ?>">
                                    <?php echo esc_html($quote->email); ?>
                                </a>
                            </td>
                            <td><?php echo wc_price($quote->subtotal); ?></td>
                            <td>
                                <?php if ($quote->meeting_requested) : ?>
                                    <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    <?php esc_html_e('Yes', 'cart-quote-woocommerce-email'); ?>
                                    <?php if ($quote->calendar_synced) : ?>
                                        <span class="dashicons dashicons-calendar-alt" title="<?php esc_attr_e('Event created', 'cart-quote-woocommerce-email'); ?>"></span>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <span class="dashicons dashicons-no" style="color: #ccc;"></span>
                                <?php endif; ?>
                            </td>
                            <td>
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
                            </td>
                            <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($quote->created_at))); ?></td>
                            <td>
                                <div class="cart-quote-row-actions">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-manager&action=view&id=' . $quote->id)); ?>" class="button button-small">
                                        <?php esc_html_e('View', 'cart-quote-woocommerce-email'); ?>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php
    $total_pages = ceil($total / $per_page);
    if ($total_pages > 1) :
    ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'total' => $total_pages,
                'current' => $paged,
            ]);
            ?>
        </div>
    </div>
    <?php endif; ?>
</div>
