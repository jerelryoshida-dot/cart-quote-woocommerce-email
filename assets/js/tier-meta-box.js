/**
 * Tier Meta Box JavaScript
 *
 * Handles dynamic tier row management in product edit page.
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.44
 */

(function($) {
    'use strict';

    var TierMetaBox = {
        init: function() {
            this.container = $('#cart-quote-tiers-container');
            this.template = $('#cart-quote-tier-template').html();
            this.noTiersMessage = this.container.find('.cart-quote-no-tiers');

            this.bindEvents();
            this.updateTierNumbers();
        },

        bindEvents: function() {
            $('#cart-quote-add-tier').on('click', this.addTier.bind(this));
            this.container.on('click', '.cart-quote-remove-tier', this.removeTier.bind(this));
        },

        addTier: function(e) {
            e.preventDefault();

            if (this.getTierCount() >= cartQuoteTier.maxTiers) {
                alert('Maximum number of tiers (' + cartQuoteTier.maxTiers + ') reached.');
                return;
            }

            this.noTiersMessage.remove();

            var index = this.getNextIndex();
            var tierNum = this.getTierCount() + 1;

            var html = this.template
                .replace(/\{\{index\}\}/g, index)
                .replace(/\{\{tier_num\}\}/g, tierNum);

            this.container.append(html);
            this.updateTierNumbers();
        },

        removeTier: function(e) {
            e.preventDefault();

            if (!confirm(cartQuoteTier.confirmRemove)) {
                return;
            }

            var $button = $(e.currentTarget);
            var $row = $button.closest('.cart-quote-tier-row');

            $row.fadeOut(300, function() {
                $(this).remove();
                this.updateTierNumbers();

                if (this.getTierCount() === 0) {
                    this.showNoTiersMessage();
                }
            }.bind(this));
        },

        getNextIndex: function() {
            var maxIndex = -1;

            this.container.find('.cart-quote-tier-row').each(function() {
                var index = parseInt($(this).data('tier-index'), 10);
                if (index > maxIndex) {
                    maxIndex = index;
                }
            });

            return maxIndex + 1;
        },

        getTierCount: function() {
            return this.container.find('.cart-quote-tier-row').length;
        },

        updateTierNumbers: function() {
            this.container.find('.cart-quote-tier-row').each(function(index) {
                var tierNum = index + 1;
                $(this).find('.cart-quote-tier-number').text('Tier ' + tierNum);
            });
        },

        showNoTiersMessage: function() {
            var message = '<p class="cart-quote-no-tiers"><em>No tiers configured yet. Click "Add Tier" to add tier pricing.</em></p>';
            this.container.html(message);
            this.noTiersMessage = this.container.find('.cart-quote-no-tiers');
        }
    };

    $(document).ready(function() {
        TierMetaBox.init();
    });

})(jQuery);
