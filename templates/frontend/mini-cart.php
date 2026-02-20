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

    if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) {
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

    if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) {
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

    if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) {
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
    <?php if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) : ?>
    <script type="text/javascript">window.cartQuoteDebugMiniCart = true;</script>
    <script src="<?php echo esc_url(CART_QUOTE_WC_PLUGIN_URL . 'assets/js/mini-cart-debug.js'); ?>"></script>
    <?php endif; ?>
    <?php if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) : ?>
    <div class="cart-quote-debug-panel" style="
        background: #f0f0f0;
        border: 2px solid #ff0000;
        padding: 20px;
        margin: 20px 0;
        font-family: monospace;
        font-size: 12px;
        color: #ffffff;
        white-space: pre-wrap;
        word-wrap: break-word;
    ">
        <h3 style="margin-top:0; color:#ff6600;">🔍 MINI-CART DEBUG DATA</h3>

        <pre style="background:#1a1a1a; padding:10px; border:1px solid #333;">
<?php
echo "========================================================================\n";
echo "STEP 1: RAW CART DATA (WC()->cart->get_cart())\n";
echo "========================================================================\n\n";

foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
    echo "CART ITEM: $cart_item_key\n";
    echo "----------------------------------------\n";

    echo "All keys: " . implode(', ', array_keys($cart_item)) . "\n\n";

    echo "PRODUCT INFO:\n";
    echo "  product_id: " . ($cart_item['product_id'] ?? 'N/A') . "\n";

    if (isset($cart_item['data']) && is_object($cart_item['data'])) {
        $product = $cart_item['data'];
        echo "  Product Name: " . $product->get_name() . "\n";
        echo "  Product Price: " . $product->get_price() . "\n";
    }

    echo "\nTIER INFO:\n";
    if (isset($cart_item['tier_data'])) {
        $td = $cart_item['tier_data'];
        echo "  tier_data EXISTS\n";
        echo "  tier_level: " . ($td['tier_level'] ?? 'NULL') . "\n";
        echo "  description: " . ($td['description'] ?? 'NULL') . "\n";
        echo "  tier_name: " . ($td['tier_name'] ?? 'NULL') . "\n";
        echo "  monthly_price: " . ($td['monthly_price'] ?? 'NULL') . "\n";
        echo "  hourly_price: " . ($td['hourly_price'] ?? 'NULL') . "\n";

        if (isset($td['_all_tiers'])) {
            echo "  _all_tiers (all available):\n";
            foreach ($td['_all_tiers'] as $i => $t) {
                echo "    [$i] tier_level=" . ($t['tier_level'] ?? 'N/A') .
                     ' description="' . ($t['description'] ?? 'N/A') . "\"\n";
            }
        }
    } else {
        echo "  tier_data NOT SET\n";
    }

    echo "\nSELECTED TIER:\n";
    echo "  selected_tier: " . (isset($cart_item['selected_tier'])
        ? $cart_item['selected_tier']
        : 'NULL (NOT SET)') . "\n";

    echo "  quantity: " . $cart_item['quantity'] . "\n";
    echo "  line_total: " . $cart_item['line_total'] . "\n";

    echo "\n";
}

echo "\n========================================================================\n";
echo "STEP 2: ITEMS GROUPED BY PRODUCT_ID\n";
echo "========================================================================\n\n";

echo "items_by_product structure:\n";
echo json_encode($items_by_product, JSON_PRETTY_PRINT) . "\n\n";

echo "tier_items_by_parent structure:\n";
echo json_encode($tier_items_by_parent, JSON_PRETTY_PRINT) . "\n\n";

echo "\n========================================================================\n";
echo "STEP 3: VIRTUAL PARENT ITEMS\n";
echo "========================================================================\n\n";

echo "parent_items structure:\n";
echo json_encode($parent_items, JSON_PRETTY_PRINT) . "\n\n";

echo "\n========================================================================\n";
echo "STEP 4: FILTERED TIER ITEMS (Will Be Displayed)\n";
echo "========================================================================\n\n";

foreach ($parent_items as $parent_key => $parent) {
    $product_id = $parent['product_id'];
    $tier_items = isset($tier_items_by_parent[$product_id])
        ? $tier_items_by_parent[$product_id]
        : [];

    echo "PARENT PRODUCT ID: $product_id\n";
    echo "----------------------------------------\n";
    echo "  Product Name: " . $parent['data']->get_name() . "\n";
    echo "  Aggregated Qty: " . $parent['quantity'] . "\n";
    echo "  Aggregated Total: $" . number_format($parent['line_total'], 2) . "\n\n";

    echo "  DETECTED SELECTED_TIER:\n";
    $selected_tier = null;
    if (!empty($tier_items)) {
        $selected_tier = isset($tier_items[0]['selected_tier'])
            ? (int) $tier_items[0]['selected_tier']
            : 1;
        echo "    Reading from: \$tier_items[0]['selected_tier']\n";
    }
    echo "    Value: " . ($selected_tier ?? 'NULL') . "\n\n";

    echo "  TIER ITEMS BEFORE FILTER:\n";
    echo "    Count: " . count($tier_items) . "\n";
    foreach ($tier_items as $i => $item) {
        $td = $item['tier_data'] ?? [];
        echo "    [$i] tier_level=" . ($td['tier_level'] ?? 'N/A') .
             ' description="' . ($td['description'] ?? 'N/A') . "\"\n";
    }

    echo "\n  APPLYING FILTER (tier_level === selected_tier):\n";
    if ($selected_tier && !empty($tier_items)) {
        $filtered = array_filter($tier_items, function($item) use ($selected_tier) {
            $td = $item['tier_data'] ?? [];
            $match = isset($td['tier_level'])
                && (int) $td['tier_level'] === $selected_tier;

            echo "    Checking item tier_level=" . ($td['tier_level'] ?? 'N/A') .
                 " vs selected_tier=$selected_tier -> " .
                 ($match ? 'MATCH ✓' : 'NO MATCH ✗') . "\n";

            return $match;
        });
        $tier_items = $filtered;
    } else {
        echo "    No filter applied (selected_tier is null or empty tier_items)\n";
    }

    echo "\n  TIER ITEMS AFTER FILTER:\n";
    echo "    Count: " . count($tier_items) . "\n";
    foreach ($tier_items as $i => $item) {
        $td = $item['tier_data'] ?? [];
        echo "    [$i] tier_level=" . ($td['tier_level'] ?? 'N/A') .
             ' description="' . ($td['description'] ?? 'N/A') . "\"\n";
    }

    echo "\n  WHAT WILL BE DISPLAYED:\n";
    foreach ($tier_items as $i => $item) {
        $td = $item['tier_data'] ?? [];
        $tier_label = '';

        if (!empty($td['tier_level'])) {
            $tier_label = 'Tier ' . $td['tier_level'];
            if (!empty($td['description'])) {
                $tier_label .= ': ' . $td['description'];
            } elseif (!empty($td['tier_name'])) {
                $tier_label .= ': ' . $td['tier_name'];
            }
        } elseif (!empty($td['description'])) {
            $tier_label = $td['description'];
        } elseif (!empty($td['tier_name'])) {
            $tier_label = $td['tier_name'];
        }

        echo "    [$i] \"$tier_label\" (Qty: X" . $item['quantity'] .
             ", Total: $" . number_format($item['line_total'], 2) . ")\n";
    }

    echo "\n    ========================================\n\n";
}
?>
        </pre>
    </div>
    <?php endif; ?>

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
                <?php
                echo '<pre style="background:#1a1a1a;color:#00ff00;padding:10px;margin:10px 0;font-size:10px;border:1px solid #00ff00;max-height:400px;overflow:auto;white-space:pre-wrap;word-wrap:break-word;">';
                echo "=== MINI-CART RAW DATA ===\n";
                echo "Time: " . date('Y-m-d H:i:s') . "\n\n";

                echo "=== STEP 1: RAW CART ITEMS (WC()->cart->get_cart()) ===\n";
                $raw_index = 0;
                foreach (WC()->cart->get_cart() as $raw_key => $raw_item) {
                    echo "[$raw_index] Cart Key: $raw_key\n";
                    echo "    product_id: " . ($raw_item['product_id'] ?? 'N/A') . "\n";
                    echo "    quantity: " . ($raw_item['quantity'] ?? 'N/A') . "\n";
                    echo "    line_total: " . ($raw_item['line_total'] ?? 'N/A') . "\n";
                    
                    if (isset($raw_item['tier_data'])) {
                        $td = $raw_item['tier_data'];
                        echo "    tier_data:\n";
                        echo "      tier_level: " . (isset($td['tier_level']) ? $td['tier_level'] : 'NULL') . "\n";
                        echo "      description: " . (isset($td['description']) ? $td['description'] : 'NULL') . "\n";
                        echo "      tier_name: " . (isset($td['tier_name']) ? $td['tier_name'] : 'NULL') . "\n";
                        echo "      monthly_price: " . (isset($td['monthly_price']) ? $td['monthly_price'] : 'NULL') . "\n";
                        echo "      hourly_price: " . (isset($td['hourly_price']) ? $td['hourly_price'] : 'NULL') . "\n";
                        
                        if (isset($td['_all_tiers'])) {
                            echo "      _all_tiers (" . count($td['_all_tiers']) . " available):\n";
                            foreach ($td['_all_tiers'] as $at) {
                                echo "        - Tier " . ($at['tier_level'] ?? '?') . ": " . ($at['description'] ?? 'N/A') . " ($" . ($at['monthly_price'] ?? 0) . "/mo)\n";
                            }
                        }
                    } else {
                        echo "    tier_data: NOT SET\n";
                    }
                    
                    echo "    selected_tier: " . (isset($raw_item['selected_tier']) ? $raw_item['selected_tier'] : 'NULL (NOT SET)') . "\n";
                    echo "\n";
                    $raw_index++;
                }

                echo "=== STEP 2: GROUPED BY PRODUCT_ID ===\n";
                echo "items_by_product (" . count($items_by_product) . " products):\n";
                foreach ($items_by_product as $gpid => $gitems) {
                    echo "  Product ID $gpid: " . count($gitems) . " item(s)\n";
                }
                echo "\n";

                echo "=== STEP 3: TIER ITEMS BY PARENT ===\n";
                echo "tier_items_by_parent (" . count($tier_items_by_parent) . " parents with tiers):\n";
                foreach ($tier_items_by_parent as $tpid => $titems) {
                    echo "  Product ID $tpid: " . count($titems) . " tier item(s)\n";
                    foreach ($titems as $ti => $titem) {
                        $ttd = $titem['tier_data'] ?? [];
                        echo "    [$ti] tier_level=" . ($ttd['tier_level'] ?? 'NULL') . " selected_tier=" . (isset($titem['selected_tier']) ? $titem['selected_tier'] : 'NULL') . "\n";
                    }
                }
                echo "\n";

                echo "=== STEP 4: VIRTUAL PARENT ITEMS ===\n";
                echo "parent_items (" . count($parent_items) . " items):\n";
                foreach ($parent_items as $pi => $pitem) {
                    echo "  [$pi] product_id=" . ($pitem['product_id'] ?? 'N/A') . " qty=" . ($pitem['quantity'] ?? 0) . " total=$" . number_format($pitem['line_total'] ?? 0, 2) . "\n";
                }
                echo "\n";

                echo "=== STEP 5: FILTER LOGIC ===\n";
                foreach ($parent_items as $fi => $fparent) {
                    $fpid = $fparent['product_id'];
                    $ftier_items = isset($tier_items_by_parent[$fpid]) ? $tier_items_by_parent[$fpid] : [];
                    $fselected = null;
                    if (!empty($ftier_items)) {
                        $fselected = isset($ftier_items[0]['selected_tier']) ? (int)$ftier_items[0]['selected_tier'] : 1;
                    }
                    $fbefore = count($ftier_items);
                    
                    echo "Parent [$fi] (Product ID $fpid):\n";
                    echo "  Reading selected_tier from \$tier_items[0]['selected_tier']\n";
                    echo "  selected_tier = " . ($fselected ?? 'NULL') . " (" . ($fselected ? 'EXPLICIT' : 'DEFAULTS TO 1') . ")\n";
                    echo "  Items before filter: $fbefore\n";
                    
                    if ($fselected && !empty($ftier_items)) {
                        $fafter = 0;
                        foreach ($ftier_items as $fitem) {
                            $ftd = $fitem['tier_data'] ?? [];
                            $fmatch = isset($ftd['tier_level']) && (int)$ftd['tier_level'] === $fselected;
                            echo "    Checking: tier_level=" . ($ftd['tier_level'] ?? 'NULL') . " vs selected_tier=$fselected -> " . ($fmatch ? 'MATCH' : 'NO MATCH') . "\n";
                            if ($fmatch) $fafter++;
                        }
                        echo "  Items after filter: $fafter\n";
                    } else {
                        echo "  No filter (selected_tier is null or no tier items)\n";
                    }
                    echo "\n";
                }
                echo "</pre>";
                ?>
                
                <?php if (!empty($parent_items)) : ?>
                    <?php
                    $parent_loop_index = 0;
                    foreach ($parent_items as $parent_key => $parent) :
                        $product = $parent['data'];
                        $parent_id = $parent['product_id'];
                        $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];

                        $selected_tier = null;
                        if (!empty($tier_items)) {
                            $selected_tier = isset($tier_items[0]['selected_tier'])
                                ? (int) $tier_items[0]['selected_tier']
                                : 1;
                        }

                        $tier_count_before = count($tier_items);

                        if ($selected_tier && !empty($tier_items)) {
                            $tier_items = array_filter($tier_items, function($item) use ($selected_tier) {
                                return isset($item['tier_data']['tier_level'])
                                    && (int) $item['tier_data']['tier_level'] === $selected_tier;
                            });
                        }

                        $tier_count_after = count($tier_items);

                        if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) {
                            echo '<script>if(typeof MiniCartLogger!=="undefined"){MiniCartLogger.logTierFiltering(' . $parent_id . ',' . ($selected_tier ?? 'null') . ',' . $tier_count_before . ',' . $tier_count_after . ');}</script>';
                        }

                        if ($debug_log) {
                            error_log('  PARENT ITEM [' . $parent_loop_index . ']');
                            error_log('  ----------------------------------------');
                            error_log('    Product: "' . $product->get_name() . '"');
                            error_log('    Product ID: ' . $parent_id);
                            error_log('    Aggregated qty: X' . $parent['quantity']);
                            error_log('    Aggregated price: $' . number_format($parent['line_total'], 2));
                            error_log('    Selected tier: ' . ($selected_tier ?? 'N/A'));
                            error_log('    Tier items before filter: ' . count($tier_items_by_parent[$parent_id] ?? []));
                            error_log('    Tier items after filter: ' . count($tier_items));
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

                            if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) {
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
                        <?php endforeach; ?>
                        
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
                    <?php if (isset($_GET['debug_mini_cart']) || isset($_GET['debug_cart'])) : ?>
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
                        
                        <?php if ($tier_label) : ?>
                            <div class="cart-quote-mini-item tier-item">
                                <span class="item-name">• <?php echo $tier_label; ?></span>
                                <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                            </div>
                        <?php else : ?>
                            <div class="cart-quote-mini-item">
                                <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                                <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>

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
