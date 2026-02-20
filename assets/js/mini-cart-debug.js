/**
 * Mini-Cart Console Logger
 *
 * Sends structured debug data to browser console for troubleshooting tier display issues.
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.54
 */

(function() {
    'use strict';

    const MiniCartLogger = {
        enabled: false,

        init: function() {
            this.enabled = window.cartQuoteDebugMiniCart === true ||
                         window.location.search.indexOf('debug_mini_cart=1') !== -1 ||
                         window.location.search.indexOf('debug_cart=1') !== -1;

            if (this.enabled) {
                this.log('LOGGER_INIT', {
                    message: 'Mini-Cart Console Logger initialized',
                    url: window.location.href,
                    userAgent: navigator.userAgent
                });
            }
        },

        log: function(category, data) {
            if (!this.enabled) return;

            var timestamp = new Date().toISOString();
            console.log('[Mini-Cart ' + timestamp + '] [' + category + ']', data);
        },

        logRawCart: function(cartItems) {
            if (!this.enabled) return;

            var items = [];
            for (var i = 0; i < cartItems.length; i++) {
                var item = cartItems[i];
                items.push({
                    key: item.key || 'N/A',
                    product_id: item.product_id || 'N/A',
                    has_tier_data: !!item.tier_data,
                    tier_level: item.tier_data && item.tier_data.tier_level ? item.tier_data.tier_level : null,
                    tier_description: item.tier_data && item.tier_data.description ? item.tier_data.description : null,
                    tier_name: item.tier_data && item.tier_data.tier_name ? item.tier_data.tier_name : null,
                    selected_tier: item.selected_tier || null,
                    quantity: item.quantity || 0,
                    line_total: item.line_total || 0
                });
            }

            this.log('RAW_CART', {
                count: cartItems.length,
                items: items
            });
        },

        logGroupedData: function(groupedData, tierItemsByParent) {
            if (!this.enabled) return;

            this.log('GROUPED_DATA', {
                items_by_product: {
                    count: Object.keys(groupedData).length,
                    data: groupedData
                },
                tier_items_by_parent: {
                    parent_ids: Object.keys(tierItemsByParent),
                    data: tierItemsByParent
                }
            });
        },

        logParentItems: function(parentItems) {
            if (!this.enabled) return;

            var items = [];
            for (var i = 0; i < parentItems.length; i++) {
                var p = parentItems[i];
                items.push({
                    product_id: p.product_id || 'N/A',
                    quantity: p.quantity || 0,
                    line_total: p.line_total || 0
                });
            }

            this.log('PARENT_ITEMS', {
                count: parentItems.length,
                items: items
            });
        },

        logTierFiltering: function(parentId, selectedTier, beforeCount, afterCount) {
            if (!this.enabled) return;

            var filterResult = 'UNKNOWN';
            if (afterCount === 0) {
                filterResult = 'EMPTY_RESULT';
            } else if (afterCount === beforeCount) {
                filterResult = 'ALL_MATCH';
            } else if (afterCount < beforeCount) {
                filterResult = 'FILTERED';
            }

            this.log('TIER_FILTER', {
                parent_id: parentId,
                selected_tier: selectedTier,
                items_before_filter: beforeCount,
                items_after_filter: afterCount,
                items_removed: beforeCount - afterCount,
                filter_result: filterResult
            });
        },

        logTierDisplay: function(tierItem, label) {
            if (!this.enabled) return;

            this.log('TIER_DISPLAY', {
                tier_level: tierItem.tier_data && tierItem.tier_data.tier_level ? tierItem.tier_data.tier_level : 'N/A',
                tier_description: tierItem.tier_data && tierItem.tier_data.description ? tierItem.tier_data.description : 'N/A',
                tier_name: tierItem.tier_data && tierItem.tier_data.tier_name ? tierItem.tier_data.tier_name : 'N/A',
                selected_tier: tierItem.selected_tier || 'N/A',
                display_label: label || 'N/A',
                quantity: tierItem.quantity || 0,
                line_total: tierItem.line_total || 0
            });
        },

        logRenderComplete: function(parentCount, totalTierItems) {
            if (!this.enabled) return;

            this.log('RENDER_COMPLETE', {
                parent_count: parentCount,
                total_tier_items: totalTierItems,
                timestamp: new Date().toISOString()
            });
        },

        error: function(message, context) {
            var timestamp = new Date().toISOString();
            console.error('[Mini-Cart ' + timestamp + '] [ERROR]', message, context || {});
        },

        warn: function(message, context) {
            var timestamp = new Date().toISOString();
            console.warn('[Mini-Cart ' + timestamp + '] [WARN]', message, context || {});
        }
    };

    window.MiniCartLogger = MiniCartLogger;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            MiniCartLogger.init();
        });
    } else {
        MiniCartLogger.init();
    }

})();
