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

$debug_enabled = Settings::is_debug_mini_cart_enabled();
$debug_log = $debug_enabled && defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;

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
            
            // Log all available keys in cart item
            error_log('    AVAILABLE KEYS: ' . implode(', ', array_keys($cart_item)));
            error_log('');
            error_log('    PRODUCT DATA:');
            error_log('      product_id: ' . ($cart_item['product_id'] ?? 'N/A'));
            error_log('      variation_id: ' . ($cart_item['variation_id'] ?? 'N/A'));
            
            if (isset($cart_item['data']) && is_object($cart_item['data'])) {
                $product = $cart_item['data'];
                error_log('      Product Name: ' . $product->get_name());
                error_log('      Product SKU: ' . $product->get_sku());
                error_log('      Product Price: ' . $product->get_price());
                error_log('      Product ID (from object): ' . $product->get_id());
            }
            
            error_log('');
            error_log('    QUANTITY & PRICING:');
            error_log('      quantity: ' . ($cart_item['quantity'] ?? 'N/A'));
            error_log('      line_subtotal: ' . ($cart_item['line_subtotal'] ?? 'N/A'));
            error_log('      line_subtotal_tax: ' . ($cart_item['line_subtotal_tax'] ?? 'N/A'));
            error_log('      line_total: ' . ($cart_item['line_total'] ?? 'N/A'));
            error_log('      line_tax: ' . ($cart_item['line_tax'] ?? 'N/A'));
            error_log('      line_tax_data: ' . json_encode($cart_item['line_tax_data'] ?? []));
            
            error_log('');
            error_log('    TIER DATA:');
            $has_tier = isset($cart_item['tier_data']);
            error_log('      isset($cart_item[\'tier_data\']): ' . ($has_tier ? 'TRUE (This is a TIER ITEM)' : 'FALSE (This is a PARENT ITEM)'));
            
            if ($has_tier) {
                $td = $cart_item['tier_data'];
                error_log('');
                error_log('      TIER DATA FIELDS:');
                error_log('        tier_level: "' . ($td['tier_level'] ?? 'NULL') . '"');
                error_log('        description: "' . ($td['description'] ?? 'NULL') . '"');
                error_log('        tier_name: "' . ($td['tier_name'] ?? 'NULL') . '"');
                error_log('        monthly_price: ' . ($td['monthly_price'] ?? 'NULL'));
                error_log('        hourly_price: ' . ($td['hourly_price'] ?? 'NULL'));
                
                if (isset($td['_debug_all_tiers'])) {
                    error_log('');
                    error_log('        _debug_all_tiers (All tiers available for this product):');
                    error_log('          Count: ' . count($td['_debug_all_tiers']));
                    foreach ($td['_debug_all_tiers'] as $ti => $tier_row) {
                        error_log('          [' . $ti . '] tier_level="' . ($tier_row['tier_level'] ?? 'N/A') . '" description="' . ($tier_row['description'] ?? 'N/A') . '" tier_name="' . ($tier_row['tier_name'] ?? 'N/A') . '"');
                    }
                }
            }
            
            error_log('');
            error_log('    SEPARATION LOGIC:');
            if ($has_tier) {
                error_log('      -> This item HAS tier_data');
                error_log('      -> Will be added to: $tier_items_by_parent[' . $cart_item['product_id'] . ']');
                error_log('      -> Grouped with parent product ID: ' . $cart_item['product_id']);
            } else {
                error_log('      -> This item has NO tier_data');
                error_log('      -> Will be added to: $parent_items[]');
                error_log('      -> Will display as PARENT (bold, no bullet)');
            }
            error_log('');
        }
        
        // Perform separation
        if (isset($cart_item['tier_data'])) {
            $parent_id = $cart_item['product_id'];
            $tier_items_by_parent[$parent_id][] = $cart_item;
        } else {
            $parent_items[] = $cart_item;
        }
        
        $item_index++;
    }
    
    if ($debug_log) {
        error_log('STEP 3: SEPARATION RESULTS');
        error_log('============================================================');
        error_log('  After separating items by tier_data presence:');
        error_log('');
        error_log('  $parent_items array (Items WITHOUT tier_data):');
        error_log('    Count: ' . count($parent_items));
        
        if (!empty($parent_items)) {
            foreach ($parent_items as $i => $p) {
                $pid = isset($p['data']) ? $p['data']->get_id() : ($p['product_id'] ?? 'N/A');
                $related_tiers = isset($tier_items_by_parent[$pid]) ? count($tier_items_by_parent[$pid]) : 0;
                $pname = isset($p['data']) ? $p['data']->get_name() : 'Unknown';
                error_log('    [' . $i . '] "' . $pname . '" (Product ID: ' . $pid . ')');
                error_log('        Related tier items: ' . $related_tiers);
            }
        } else {
            error_log('    (empty - no parent items found)');
        }
        
        error_log('');
        error_log('  $tier_items_by_parent array (Items WITH tier_data, grouped by product_id):');
        error_log('    Unique parent IDs: ' . count($tier_items_by_parent));
        
        if (!empty($tier_items_by_parent)) {
            foreach ($tier_items_by_parent as $pid => $tiers) {
                error_log('    Product ID ' . $pid . ' has ' . count($tiers) . ' tier item(s):');
                foreach ($tiers as $j => $t) {
                    $td = $t['tier_data'] ?? [];
                    $tname = isset($t['data']) ? $t['data']->get_name() : 'Unknown';
                    error_log('      [' . $j . '] "' . $tname . '"');
                    error_log('          tier_level: "' . ($td['tier_level'] ?? 'N/A') . '"');
                    error_log('          description: "' . ($td['description'] ?? 'N/A') . '"');
                    error_log('          quantity: ' . ($t['quantity'] ?? 'N/A'));
                    error_log('          line_total: ' . ($t['line_total'] ?? 'N/A'));
                }
            }
        } else {
            error_log('    (empty - no tier items found)');
        }
        error_log('');
    }
}

if ($debug_log) {
    error_log('STEP 4: RENDERING LOOP (Building display output)');
    error_log('============================================================');
    error_log('  Processing each parent item and its related tiers...');
    error_log('');
}
?>
<div class="cart-quote-mini-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
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
                <?php if (!empty($parent_items)) : ?>
                    <?php 
                    $parent_loop_index = 0;
                    foreach ($parent_items as $parent_key => $parent) : 
                        $product = $parent['data'];
                        $parent_id = $product->get_id();
                        $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];
                        
                        // Calculate sum of tier prices and quantities
                        $tier_total = 0;
                        $tier_qty_sum = 0;
                        foreach ($tier_items as $tier) {
                            $tier_total += $tier['line_total'];
                            $tier_qty_sum += $tier['quantity'];
                        }
                        
                        // Parent price = tier sum if tiers exist, otherwise parent's own price
                        $parent_price = !empty($tier_items) ? $tier_total : $parent['line_total'];
                        
                        // Parent quantity = sum of tier quantities if tiers exist, otherwise parent's own quantity
                        $parent_qty = !empty($tier_items) ? $tier_qty_sum : $parent['quantity'];
                        
                        if ($debug_log) {
                            error_log('  PARENT ITEM [' . $parent_loop_index . ']');
                            error_log('  ----------------------------------------');
                            error_log('    Product: "' . $product->get_name() . '"');
                            error_log('    Product ID: ' . $parent_id);
                            error_log('');
                            error_log('    CALCULATIONS:');
                            error_log('      Has tier items: ' . (!empty($tier_items) ? 'YES' : 'NO'));
                            error_log('      Tier items count: ' . count($tier_items));
                            
                            if (!empty($tier_items)) {
                                error_log('      Tier price breakdown:');
                                $tier_sum_breakdown = [];
                                foreach ($tier_items as $ti => $t) {
                                    $td = $t['tier_data'] ?? [];
                                    $tier_sum_breakdown[] = '$' . $t['line_total'] . ' (Tier ' . ($td['tier_level'] ?? '?') . ')';
                                }
                                error_log('        ' . implode(' + ', $tier_sum_breakdown) . ' = $' . $tier_total);
                                error_log('      Tier qty breakdown:');
                                $qty_sum_breakdown = [];
                                foreach ($tier_items as $ti => $t) {
                                    $td = $t['tier_data'] ?? [];
                                    $qty_sum_breakdown[] = $t['quantity'] . ' (Tier ' . ($td['tier_level'] ?? '?') . ')';
                                }
                                error_log('        ' . implode(' + ', $qty_sum_breakdown) . ' = ' . $tier_qty_sum);
                            }
                            
                            error_log('');
                            error_log('    DISPLAY VALUES:');
                            error_log('      Parent price source: ' . (!empty($tier_items) ? '$tier_total (sum of tiers)' : '$parent[\'line_total\'] (own price)'));
                            error_log('      Parent price: $' . $parent_price);
                            error_log('      Parent qty source: ' . (!empty($tier_items) ? '$tier_qty_sum (sum of tier quantities)' : '$parent[\'quantity\'] (own qty)'));
                            error_log('      Parent qty: X' . $parent_qty);
                            error_log('');
                            error_log('    HTML OUTPUT:');
                            error_log('      <div class="cart-quote-mini-item parent-item">');
                            error_log('        <span class="item-name">' . $product->get_name() . '</span>');
                            error_log('        <span class="item-qty">X' . $parent_qty . '</span>');
                            error_log('        <span class="item-price">$' . number_format($parent_price, 2) . '</span>');
                            error_log('      </div>');
                            error_log('');
                        }
                        
                        $parent_loop_index++;
                        ?>
                        
                        <div class="cart-quote-mini-item parent-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($parent_qty); ?></span>
                            <span class="item-price"><?php echo wc_price($parent_price); ?></span>
                        </div>
                        
                        <?php 
                        $tier_loop_index = 0;
                        foreach ($tier_items as $tier) : 
                            $tier_data = $tier['tier_data'];
                            $tier_label = '';
                            
                            // Build tier label
                            if (!empty($tier_data['tier_level'])) {
                                $tier_label .= esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                $tier_label .= ': ';
                            }
                            if (!empty($tier_data['description'])) {
                                $tier_label .= esc_html($tier_data['description']);
                            } elseif (!empty($tier_data['tier_name'])) {
                                $tier_label .= esc_html($tier_data['tier_name']);
                            }
                            
                            if ($debug_log) {
                                error_log('    TIER ITEM [' . $tier_loop_index . ']');
                                error_log('    ----------------------------------------');
                                error_log('      Raw tier_data:');
                                error_log('        tier_level: "' . ($tier_data['tier_level'] ?? 'NULL') . '"');
                                error_log('        description: "' . ($tier_data['description'] ?? 'NULL') . '"');
                                error_log('        tier_name: "' . ($tier_data['tier_name'] ?? 'NULL') . '"');
                                error_log('');
                                error_log('      LABEL CONSTRUCTION:');
                                error_log('        Step 1: Check tier_level');
                                error_log('          !empty($tier_data[\'tier_level\']): ' . (!empty($tier_data['tier_level']) ? 'TRUE' : 'FALSE'));
                                if (!empty($tier_data['tier_level'])) {
                                    error_log('          -> Add "Tier " + tier_level + ": "');
                                    error_log('          -> Current label: "Tier ' . $tier_data['tier_level'] . ': "');
                                }
                                error_log('        Step 2: Check description');
                                error_log('          !empty($tier_data[\'description\']): ' . (!empty($tier_data['description']) ? 'TRUE' : 'FALSE'));
                                if (!empty($tier_data['description'])) {
                                    error_log('          -> Add description to label');
                                    error_log('          -> Current label: "' . $tier_label . '"');
                                } else {
                                    error_log('          -> Check tier_name as fallback');
                                    error_log('          !empty($tier_data[\'tier_name\']): ' . (!empty($tier_data['tier_name']) ? 'TRUE' : 'FALSE'));
                                }
                                error_log('');
                                error_log('      FINAL DISPLAY VALUES:');
                                error_log('        Display label: "' . $tier_label . '"');
                                error_log('        Display qty: X' . $tier['quantity']);
                                error_log('        Display price: $' . number_format($tier['line_total'], 2));
                                error_log('');
                                error_log('      HTML OUTPUT:');
                                error_log('        <div class="cart-quote-mini-item tier-item">');
                                error_log('          <span class="item-name">• ' . $tier_label . '</span>');
                                error_log('          <span class="item-qty">X' . $tier['quantity'] . '</span>');
                                error_log('          <span class="item-price">$' . number_format($tier['line_total'], 2) . '</span>');
                                error_log('        </div>');
                                error_log('');
                            }
                            
                            $tier_loop_index++;
                            ?>
                            
                            <div class="cart-quote-mini-item tier-item">
                                <span class="item-name">• <?php echo $tier_label; ?></span>
                                <span class="item-qty">X<?php echo esc_html($tier['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($tier['line_total']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php 
                        $show_separator = $parent_key < count($parent_items) - 1;
                        if ($debug_log && $show_separator) {
                            error_log('    SEPARATOR:');
                            error_log('      <div class="cart-quote-item-separator"></div>');
                            error_log('      (Gradient line between product groups)');
                            error_log('');
                        }
                        ?>
                        
                        <?php if ($show_separator) : ?>
                            <div class="cart-quote-item-separator"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php 
                    if ($debug_log) {
                        error_log('  FALLBACK: No parent items found');
                        error_log('  ----------------------------------------');
                        error_log('    Displaying all cart items as flat list');
                        error_log('');
                    }
                    
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : 
                        $product = $cart_item['data'];
                        
                        if ($debug_log) {
                            error_log('    FALLBACK ITEM:');
                            error_log('      Product: "' . $product->get_name() . '"');
                            error_log('      Qty: X' . $cart_item['quantity']);
                            error_log('      Price: $' . number_format($cart_item['line_total'], 2));
                            error_log('');
                        }
                        ?>
                        <div class="cart-quote-mini-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                            <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php 
                if ($debug_log) {
                    error_log('STEP 5: SUBTOTAL & ACTIONS');
                    error_log('============================================================');
                    error_log('  Subtotal: ' . $cart_subtotal);
                    error_log('  Actions: View Cart | Get Quote');
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
