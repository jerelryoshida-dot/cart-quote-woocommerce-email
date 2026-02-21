<?php
/**
 * Frontend Mini Cart Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use CartQuoteWooCommerce\Admin\Settings;

$cart_count = WC()->cart->get_cart_contents_count();
$cart_subtotal = WC()->cart->get_cart_subtotal();
$is_empty = WC()->cart->is_empty();

// Unified debug condition with all requirements:
// 1. Setting enabled
// 2. WordPress DEBUG mode active
// 3. User has admin capabilities
$debug_enabled = Settings::is_debug_mode_active();

// File logging requires WP_DEBUG_LOG as well
$debug_log = Settings::is_debug_logging_active();

if ($debug_log) {
    error_log('');
    error_log('############################################################');
    error_log('# MINI-CART DEBUG: COMPLETE DATA FLOW TRACE');
    error_log('############################################################');
    error_log('');
    error_log('STEP 1: CART OVERVIEW');
    error_log('============================================================');
    error_log('  Cart Contents Count: ' . $cart_count);
    error_log('  Cart Subtotal: ' . $cart_subtotal);
    error_log('  Is Empty: ' . ($is_empty ? 'YES' : 'NO'));
    error_log('  Total Unique Items: ' . count(WC()->cart->get_cart()));
    error_log('');
}

$items_by_product = [];
$parent_items = [];
$tier_items_by_parent = [];

if (!$is_empty) {
    
    if ($debug_log) {
        error_log('STEP 2: RAW CART DATA (WC()->cart->get_cart())');
        error_log('============================================================');
        error_log('  This is the raw data from WooCommerce cart session');
        error_log('');
    }
    
    $item_index = 0;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        
        if ($debug_log) {
            error_log('  CART ITEM [' . $item_index . ']');
            error_log('  ----------------------------------------');
            error_log('    cart_item_key: ' . $cart_item_key);
            error_log('    AVAILABLE KEYS: ' . implode(', ', array_keys($cart_item)));
            error_log('');
            error_log('    PRODUCT DATA:');
            error_log('      product_id: ' . ($cart_item['product_id'] ?? 'N/A'));
            
            if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                $product = $cart_item['data'];
                error_log('      Product Name: ' . $product->get_name());
                error_log('      Product Price: ' . $product->get_price());
            }
            
            error_log('');
            error_log('    TIER DATA:');
            $has_tier = isset($cart_item['tier_data']);
            
            if ($has_tier) {
                $td = $cart_item['tier_data'];
                error_log('      tier_level: "' . ($td['tier_level'] ?? 'NULL') . '"');
                error_log('      description: "' . ($td['description'] ?? 'NULL') . '"');
                error_log('      tier_name: "' . ($td['tier_name'] ?? 'NULL') . '"');
            } else {
                error_log('      (no tier_data)');
            }
            error_log('');
        }
        
        $product_id = $cart_item['product_id'];
        $items_by_product[$product_id][] = $cart_item;
        
        $item_index++;
    }

    if ($debug_enabled) {
        $raw_cart_items = [];
        foreach (WC()->cart->get_cart() as $ck => $ci) {
            $raw_cart_items[] = [
                'key' => $ck,
                'product_id' => $ci['product_id'] ?? null,
                'tier_data' => $ci['tier_data'] ?? null,
                'selected_tier' => $ci['selected_tier'] ?? null,
                'quantity' => $ci['quantity'] ?? 0,
                'line_total' => $ci['line_total'] ?? 0,
            ];
        }
        echo '<script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logRawCart(' . json_encode($raw_cart_items, JSON_HEX_TAG) . ');}</script>';
    }
    
    if ($debug_log) {
        error_log('STEP 3: GROUPING ITEMS BY PRODUCT_ID');
        error_log('============================================================');
        error_log('  Items grouped by product_id:');
        
        foreach ($items_by_product as $pid => $items) {
            error_log('    Product ID ' . $pid . ': ' . count($items) . ' item(s)');
            foreach ($items as $idx => $item) {
                $td = $item['tier_data'] ?? [];
                error_log('      [' . $idx . '] tier_level=' . ($td['tier_level'] ?? 'N/A') . ' description="' . ($td['description'] ?? 'N/A') . '"');
            }
        }
        error_log('');
    }
    
    foreach ($items_by_product as $product_id => $items) {
        $first_item = $items[0];
        $product = $first_item['data'];
        
        $parent_item = [
            'data'        => $product,
            'product_id'  => $product_id,
            'quantity'    => 0,
            'line_total'  => 0,
        ];
        
        foreach ($items as $item) {
            $parent_item['quantity'] += $item['quantity'];
            $parent_item['line_total'] += $item['line_total'];
            
            if (isset($item['tier_data'])) {
                $tier_items_by_parent[$product_id][] = $item;
            }
        }
        
        $parent_items[] = $parent_item;
    }

    if ($debug_enabled) {
        $console_grouped = [];
        foreach ($items_by_product as $pid => $items) {
            $console_grouped[$pid] = count($items);
        }
        $console_tier_grouped = [];
        foreach ($tier_items_by_parent as $pid => $tiers) {
            $console_tier_grouped[$pid] = count($tiers);
        }
        echo '<script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logGroupedData(' . json_encode($console_grouped, JSON_HEX_TAG) . ',' . json_encode($console_tier_grouped, JSON_HEX_TAG) . ');}</script>';
    }

    if ($debug_enabled) {
        $console_parent = [];
        foreach ($parent_items as $p) {
            $console_parent[] = [
                'product_id' => $p['product_id'],
                'quantity' => $p['quantity'],
                'line_total' => $p['line_total'],
            ];
        }
        echo '<script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logParentItems(' . json_encode($console_parent, JSON_HEX_TAG) . ');}</script>';
    }
    
    if ($debug_log) {
        error_log('STEP 4: VIRTUAL PARENT ITEMS CREATED');
        error_log('============================================================');
        error_log('  $parent_items array:');
        error_log('    Count: ' . count($parent_items));
        
        foreach ($parent_items as $i => $p) {
            $pid = $p['product_id'];
            $pname = $p['data']->get_name();
            $related_tiers = isset($tier_items_by_parent[$pid]) ? count($tier_items_by_parent[$pid]) : 0;
            error_log('    [' . $i . '] "' . $pname . '" (Product ID: ' . $pid . ')');
            error_log('        Aggregated qty: ' . $p['quantity']);
            error_log('        Aggregated total: $' . number_format($p['line_total'], 2));
            error_log('        Related tier items: ' . $related_tiers);
        }
        
        error_log('');
        error_log('  $tier_items_by_parent array:');
        error_log('    Unique parent IDs: ' . count($tier_items_by_parent));
        
        if (!empty($tier_items_by_parent)) {
            foreach ($tier_items_by_parent as $pid => $tiers) {
                error_log('    Product ID ' . $pid . ' has ' . count($tiers) . ' tier item(s):');
                foreach ($tiers as $j => $t) {
                    $td = $t['tier_data'] ?? [];
                    error_log('      [' . $j . '] tier_level="' . ($td['tier_level'] ?? 'N/A') . '" description="' . ($td['description'] ?? 'N/A') . '"');
                }
            }
        }
        error_log('');
    }
}

if ($debug_log) {
    error_log('STEP 5: RENDERING LOOP (Building display output)');
    error_log('============================================================');
    error_log('  Processing each parent item and its related tiers...');
    error_log('');
}
?>
<div class="cart-quote-mini-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
            <?php if ($debug_enabled) : ?>
            <script type="text/javascript">window.cartQuoteDebugMiniCart = true;</script>
            <script src="<?php echo esc_url(CART_QUOTE_WC_PLUGIN_URL . 'assets/js/mini-cart-debug.js'); ?>"></script>
            <?php endif; ?>
            <button class="cart-quote-close-btn" aria-label="Close cart" tabindex="0">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="18" y1="6" x2="18" y2="6"/>
                </svg>
            </button>
        </div>

    <div class="cart-quote-mini-cart">
        <div class="cart-quote-mini-toggle">
            <span class="dashicons dashicons-cart cart-quote-toggle-icon"></span>
            
            <span class="cart-quote-label">
                <?php esc_html_e('Cart', 'cart-quote-woocommerce-email'); ?>
                <span class="cart-count-badge">(<?php echo esc_html($cart_count); ?>)</span>
            </span>
            
            <?php if ($atts['show_subtotal'] === 'true') : ?>
                <span class="cart-quote-mini-subtotal">
                    <?php echo wp_kses_post($cart_subtotal); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$is_empty) : ?>
            <div class="cart-quote-mini-dropdown">
                <div class="cart-quote-mini-items">
                <?php if (!empty($parent_items)) : ?>
                    <?php
                    $parent_loop_index = 0;
                    foreach ($parent_items as $parent_key => $parent) :
                        $product = $parent['data'];
                        $parent_id = $parent['product_id'];
                        $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];

                        if ($debug_log) {
                            error_log('  PARENT ITEM [' . $parent_loop_index . ']');
                            error_log('  ----------------------------------------');
                            error_log('    Product: "' . $product->get_name() . '"');
                            error_log('    Product ID: ' . $parent_id);
                            error_log('    Aggregated qty: X' . $parent['quantity']);
                            error_log('    Aggregated price: $' . number_format($parent['line_total'], 2));
                            error_log('    Tier items: ' . count($tier_items));
                            error_log('    Has tier items: ' . (!empty($tier_items) ? 'YES (' . count($tier_items) . ')' : 'NO'));
                            error_log('');
                        }

                        $parent_loop_index++;
                        ?>
                        
                        <div class="cart-quote-mini-item parent-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($parent['quantity']); ?></span>
                            <span class="item-price"><?php echo wc_price($parent['line_total']); ?></span>
                        </div>

                        <?php
                        if (isset($atts['show_tier_items']) && ($atts['show_tier_items'] === 'true' || $atts['show_tier_items'] === 'yes')) :
                            $tier_loop_index = 0;
                            foreach ($tier_items as $tier) :
                                $tier_data = $tier['tier_data'];
                                $tier_label = '';

                                if (!empty($tier_data['tier_level'])) {
                                    $tier_label = esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                    if (!empty($tier_data['description'])) {
                                        $tier_label .= ': ' . esc_html($tier_data['description']);
                                    } elseif (!empty($tier_data['tier_name'])) {
                                        $tier_label .= ': ' . esc_html($tier_data['tier_name']);
                                    }
                                } elseif (!empty($tier_data['description'])) {
                                    $tier_label = esc_html($tier_data['description']);
                                } elseif (!empty($tier_data['tier_name'])) {
                                    $tier_label = esc_html($tier_data['tier_name']);
                                }

                                if ($debug_log) {
                                    error_log('    TIER ITEM [' . $tier_loop_index . ']');
                                    error_log('      tier_level: "' . ($tier_data['tier_level'] ?? 'NULL') . '"');
                                    error_log('      description: "' . ($tier_data['description'] ?? 'NULL') . '"');
                                    error_log('      Display label: "' . $tier_label . '"');
                                    error_log('');
                                }

                                if ($debug_enabled) {
                                    $console_tier = [
                                        'tier_data' => $tier_data,
                                        'selected_tier' => $tier['selected_tier'] ?? null,
                                        'quantity' => $tier['quantity'] ?? 0,
                                        'line_total' => $tier['line_total'] ?? 0,
                                    ];
                                    echo '<script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logTierDisplay(' . json_encode($console_tier, JSON_HEX_TAG) . ',' . json_encode($tier_label, JSON_HEX_TAG) . ');}</script>';
                                }

                                $tier_loop_index++;
                                ?>

                                <div class="cart-quote-mini-item tier-item">
                                    <span class="item-name">• <?php echo $tier_label; ?></span>
                                    <span class="item-qty">X<?php echo esc_html($tier['quantity']); ?></span>
                                    <span class="item-price"><?php echo wc_price($tier['line_total']); ?></span>
                                </div>
                            <?php endforeach;
                        endif; ?>
                        
                        <?php 
                        $show_separator = $parent_key < count($parent_items) - 1;
                        if ($debug_log && $show_separator) {
                            error_log('    SEPARATOR: <div class="cart-quote-item-separator"></div>');
                            error_log('');
                        }
                        ?>
                        
                        <?php if ($show_separator) : ?>
                            <div class="cart-quote-item-separator"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($debug_enabled) : ?>
                        <script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logRenderComplete(<?php echo count($parent_items); ?>,<?php echo isset($tier_items) ? count($tier_items) : 0; ?>);}</script>
                    <?php endif; ?>
                <?php else : ?>
                    <?php 
                    if ($debug_log) {
                        error_log('  FALLBACK: No parent items found');
                        error_log('  ----------------------------------------');
                        error_log('    Displaying all cart items as flat list with tier info');
                        error_log('');
                    }
                    
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                        $product = $cart_item['data'];
                        $tier_data = isset($cart_item['tier_data']) ? $cart_item['tier_data'] : null;

                        $tier_label = '';
                        if ($tier_data) {
                            if (!empty($tier_data['tier_level'])) {
                                $tier_label = esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                if (!empty($tier_data['description'])) {
                                    $tier_label .= ': ' . esc_html($tier_data['description']);
                                } elseif (!empty($tier_data['tier_name'])) {
                                    $tier_label .= ': ' . esc_html($tier_data['tier_name']);
                                }
                            } elseif (!empty($tier_data['description'])) {
                                $tier_label = esc_html($tier_data['description']);
                            } elseif (!empty($tier_data['tier_name'])) {
                                $tier_label = esc_html($tier_data['tier_name']);
                            }
                        }

                        if ($debug_log) {
                            error_log('    FALLBACK ITEM:');
                            error_log('      Product: "' . $product->get_name() . '"');
                            error_log('      tier_level: "' . ($tier_data['tier_level'] ?? 'NULL') . '"');
                            error_log('      description: "' . ($tier_data['description'] ?? 'NULL') . '"');
                            error_log('      Display label: "' . ($tier_label ?: $product->get_name()) . '"');
                            error_log('');
                        }
                        ?>

                        <?php
                        $show_tier_items = isset($atts['show_tier_items']) && ($atts['show_tier_items'] === 'true' || $atts['show_tier_items'] === 'yes');
                        if ($tier_label && $show_tier_items) : ?>
                            <div class="cart-quote-mini-item tier-item">
                                <span class="item-name">• <?php echo $tier_label; ?></span>
                                <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                            </div>
                        <?php elseif (!$tier_data) : ?>
                            <div class="cart-quote-mini-item">
                                <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                                <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
                </div>

                <?php 
                if ($debug_log) {
                    error_log('STEP 6: SUBTOTAL & ACTIONS');
                    error_log('============================================================');
                    error_log('  Subtotal: ' . $cart_subtotal);
                    error_log('');
                }
                ?>
                
                <div class="cart-quote-mini-total">
                    <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                    <span class="subtotal-amount"><?php echo wp_kses_post($cart_subtotal); ?></span>
                </div>

                <div class="cart-quote-mini-actions">
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-quote-mini-btn view-cart">
                        <?php esc_html_e('View Cart', 'cart-quote-woocommerce-email'); ?>
                    </a>
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart-quote-mini-btn get-quote">
                        <?php esc_html_e('Get Quote', 'cart-quote-woocommerce-email'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php
if ($debug_log) {
    error_log('############################################################');
    error_log('# MINI-CART DEBUG: END OF TRACE');
    error_log('############################################################');
    error_log('');
}
?>
