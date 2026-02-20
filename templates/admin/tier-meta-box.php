<?php
/**
 * Tier Meta Box Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.44
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="cart-quote-tier-meta-box">
    <p class="description" style="margin-bottom: 15px;">
        <?php esc_html_e('Configure tier pricing for this product. Tiers will be displayed in the mini-cart and quote forms.', 'cart-quote-woocommerce-email'); ?>
    </p>

    <div id="cart-quote-tiers-container">
        <?php if (empty($tiers)) : ?>
            <p class="cart-quote-no-tiers">
                <em><?php esc_html_e('No tiers configured yet. Click "Add Tier" to add tier pricing.', 'cart-quote-woocommerce-email'); ?></em>
            </p>
        <?php else : ?>
            <?php foreach ($tiers as $index => $tier) : ?>
                <?php
                $tier_num = $index + 1;
                $tier_level = $tier['level'] ?? $tier_num;
                $description = $tier['description'] ?? '';
                $tier_name = $tier['tier_name'] ?? '';
                $monthly_price = $tier['monthly_price'] ?? '';
                $hourly_price = $tier['hourly_price'] ?? '';
                $is_active = !empty($tier['is_active']);
                ?>
                <div class="cart-quote-tier-row" data-tier-index="<?php echo esc_attr($index); ?>">
                    <div class="cart-quote-tier-header">
                        <span class="cart-quote-tier-number">
                            <?php printf(__('Tier %d', 'cart-quote-woocommerce-email'), $tier_num); ?>
                        </span>
                        <button type="button" class="cart-quote-remove-tier button button-small" data-tier-index="<?php echo esc_attr($index); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </div>
                    <div class="cart-quote-tier-fields">
                        <div class="cart-quote-field-row">
                            <div class="cart-quote-field">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_level">
                                    <?php esc_html_e('Level', 'cart-quote-woocommerce-email'); ?>
                                </label>
                                <input type="number"
                                       id="cart_quote_tiers_<?php echo esc_attr($index); ?>_level"
                                       name="cart_quote_tiers[<?php echo esc_attr($index); ?>][level]"
                                       value="<?php echo esc_attr($tier_level); ?>"
                                       min="1"
                                       class="small-text">
                            </div>
                            <div class="cart-quote-field">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_description">
                                    <?php esc_html_e('Description', 'cart-quote-woocommerce-email'); ?>
                                </label>
                                <input type="text"
                                       id="cart_quote_tiers_<?php echo esc_attr($index); ?>_description"
                                       name="cart_quote_tiers[<?php echo esc_attr($index); ?>][description]"
                                       value="<?php echo esc_attr($description); ?>"
                                       placeholder="<?php esc_attr_e('e.g., Basic, Premium', 'cart-quote-woocommerce-email'); ?>"
                                       class="regular-text">
                            </div>
                            <div class="cart-quote-field">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_tier_name">
                                    <?php esc_html_e('Tier Name', 'cart-quote-woocommerce-email'); ?>
                                </label>
                                <input type="text"
                                       id="cart_quote_tiers_<?php echo esc_attr($index); ?>_tier_name"
                                       name="cart_quote_tiers[<?php echo esc_attr($index); ?>][tier_name]"
                                       value="<?php echo esc_attr($tier_name); ?>"
                                       placeholder="<?php esc_attr_e('e.g., Enterprise License', 'cart-quote-woocommerce-email'); ?>"
                                       class="regular-text">
                            </div>
                        </div>
                        <div class="cart-quote-field-row">
                            <div class="cart-quote-field">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_monthly_price">
                                    <?php esc_html_e('Monthly Price', 'cart-quote-woocommerce-email'); ?>
                                </label>
                                <input type="number"
                                       id="cart_quote_tiers_<?php echo esc_attr($index); ?>_monthly_price"
                                       name="cart_quote_tiers[<?php echo esc_attr($index); ?>][monthly_price]"
                                       value="<?php echo esc_attr($monthly_price); ?>"
                                       step="0.01"
                                       min="0"
                                       class="small-text">
                            </div>
                            <div class="cart-quote-field">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_hourly_price">
                                    <?php esc_html_e('Hourly Price', 'cart-quote-woocommerce-email'); ?>
                                </label>
                                <input type="number"
                                       id="cart_quote_tiers_<?php echo esc_attr($index); ?>_hourly_price"
                                       name="cart_quote_tiers[<?php echo esc_attr($index); ?>][hourly_price]"
                                       value="<?php echo esc_attr($hourly_price); ?>"
                                       step="0.01"
                                       min="0"
                                       class="small-text">
                            </div>
                            <div class="cart-quote-field cart-quote-field-checkbox">
                                <label for="cart_quote_tiers_<?php echo esc_attr($index); ?>_is_active">
                                    <input type="checkbox"
                                           id="cart_quote_tiers_<?php echo esc_attr($index); ?>_is_active"
                                           name="cart_quote_tiers[<?php echo esc_attr($index); ?>][is_active]"
                                           value="1"
                                           <?php checked($is_active); ?>>
                                    <?php esc_html_e('Active', 'cart-quote-woocommerce-email'); ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <button type="button" class="button" id="cart-quote-add-tier">
        <span class="dashicons dashicons-plus-alt2"></span>
        <?php esc_html_e('Add Tier', 'cart-quote-woocommerce-email'); ?>
    </button>

    <script type="text/template" id="cart-quote-tier-template">
        <div class="cart-quote-tier-row" data-tier-index="{{index}}">
            <div class="cart-quote-tier-header">
                <span class="cart-quote-tier-number">
                    <?php printf(__('Tier %d', 'cart-quote-woocommerce-email'), '{{tier_num}}'); ?>
                </span>
                <button type="button" class="cart-quote-remove-tier button button-small" data-tier-index="{{index}}">
                    <span class="dashicons dashicons-trash"></span>
                </button>
            </div>
            <div class="cart-quote-tier-fields">
                <div class="cart-quote-field-row">
                    <div class="cart-quote-field">
                        <label for="cart_quote_tiers_{{index}}_level">
                            <?php esc_html_e('Level', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="number"
                               id="cart_quote_tiers_{{index}}_level"
                               name="cart_quote_tiers[{{index}}][level]"
                               value="{{tier_num}}"
                               min="1"
                               class="small-text">
                    </div>
                    <div class="cart-quote-field">
                        <label for="cart_quote_tiers_{{index}}_description">
                            <?php esc_html_e('Description', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="text"
                               id="cart_quote_tiers_{{index}}_description"
                               name="cart_quote_tiers[{{index}}][description]"
                               value=""
                               placeholder="<?php esc_attr_e('e.g., Basic, Premium', 'cart-quote-woocommerce-email'); ?>"
                               class="regular-text">
                    </div>
                    <div class="cart-quote-field">
                        <label for="cart_quote_tiers_{{index}}_tier_name">
                            <?php esc_html_e('Tier Name', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="text"
                               id="cart_quote_tiers_{{index}}_tier_name"
                               name="cart_quote_tiers[{{index}}][tier_name]"
                               value=""
                               placeholder="<?php esc_attr_e('e.g., Enterprise License', 'cart-quote-woocommerce-email'); ?>"
                               class="regular-text">
                    </div>
                </div>
                <div class="cart-quote-field-row">
                    <div class="cart-quote-field">
                        <label for="cart_quote_tiers_{{index}}_monthly_price">
                            <?php esc_html_e('Monthly Price', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="number"
                               id="cart_quote_tiers_{{index}}_monthly_price"
                               name="cart_quote_tiers[{{index}}][monthly_price]"
                               value=""
                               step="0.01"
                               min="0"
                               class="small-text">
                    </div>
                    <div class="cart-quote-field">
                        <label for="cart_quote_tiers_{{index}}_hourly_price">
                            <?php esc_html_e('Hourly Price', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="number"
                               id="cart_quote_tiers_{{index}}_hourly_price"
                               name="cart_quote_tiers[{{index}}][hourly_price]"
                               value=""
                               step="0.01"
                               min="0"
                               class="small-text">
                    </div>
                    <div class="cart-quote-field cart-quote-field-checkbox">
                        <label for="cart_quote_tiers_{{index}}_is_active">
                            <input type="checkbox"
                                   id="cart_quote_tiers_{{index}}_is_active"
                                   name="cart_quote_tiers[{{index}}][is_active]"
                                   value="1"
                                   checked>
                            <?php esc_html_e('Active', 'cart-quote-woocommerce-email'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </script>
</div>
