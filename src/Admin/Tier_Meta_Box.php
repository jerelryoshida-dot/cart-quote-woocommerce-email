<?php
/**
 * Tier Meta Box
 *
 * Adds a meta box to WooCommerce product edit page for configuring tier pricing.
 *
 * @package CartQuoteWooCommerce\Admin
 * @author Jerel Yoshida
 * @since 1.0.44
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Admin;

use CartQuoteWooCommerce\Services\Tier_Service;

class Tier_Meta_Box
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
        add_action('save_post_product', [$this, 'save_tier_data'], 10, 2);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function add_meta_box(): void
    {
        add_meta_box(
            'cart_quote_tier_pricing',
            __('Tier Pricing', 'cart-quote-woocommerce-email'),
            [$this, 'render_meta_box'],
            'product',
            'normal',
            'default'
        );
    }

    public function enqueue_scripts(string $hook): void
    {
        global $post_type;

        if (!in_array($hook, ['post-new.php', 'post.php']) || $post_type !== 'product') {
            return;
        }

        wp_enqueue_script(
            'cart-quote-tier-meta-box',
            plugins_url('assets/js/tier-meta-box.js', dirname(__FILE__, 2) . '/cart-quote-woocommerce-email.php'),
            ['jquery'],
            CART_QUOTE_WC_VERSION,
            true
        );

        wp_localize_script('cart-quote-tier-meta-box', 'cartQuoteTier', [
            'maxTiers'     => Tier_Service::MAX_TIERS,
            'confirmRemove' => __('Are you sure you want to remove this tier?', 'cart-quote-woocommerce-email'),
        ]);
    }

    public function render_meta_box(\WP_Post $post): void
    {
        wp_nonce_field('cart_quote_tier_save', 'cart_quote_tier_nonce');

        $tiers = [];

        for ($i = 1; $i <= Tier_Service::MAX_TIERS; $i++) {
            $level = get_post_meta($post->ID, "_cart_quote_tier_{$i}_level", true);

            if ($level === '' || $level === false) {
                continue;
            }

            $tiers[] = [
                'level'         => $level,
                'description'   => get_post_meta($post->ID, "_cart_quote_tier_{$i}_description", true),
                'tier_name'     => get_post_meta($post->ID, "_cart_quote_tier_{$i}_tier_name", true),
                'monthly_price' => get_post_meta($post->ID, "_cart_quote_tier_{$i}_monthly_price", true),
                'hourly_price'  => get_post_meta($post->ID, "_cart_quote_tier_{$i}_hourly_price", true),
                'is_active'     => get_post_meta($post->ID, "_cart_quote_tier_{$i}_is_active", true),
            ];
        }

        include dirname(__FILE__, 3) . '/templates/admin/tier-meta-box.php';
    }

    public function save_tier_data(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['cart_quote_tier_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['cart_quote_tier_nonce'], 'cart_quote_tier_save')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $tiers = isset($_POST['cart_quote_tiers']) && is_array($_POST['cart_quote_tiers'])
            ? $_POST['cart_quote_tiers']
            : [];

        Tier_Service::save_tiers($post_id, $tiers);
    }
}
