<?php
/**
 * Admin Settings Page Template
 *
 * Template for rendering plugin settings page.
 *
 * @package PLUGIN_NAMESPACE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap plugin-slug-settings-wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['welcome'])) : ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e('Welcome to PLUGIN_NAME!', 'TEXT_DOMAIN'); ?></strong>
                <?php esc_html_e('Configure your settings below to get started.', 'TEXT_DOMAIN'); ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="plugin-slug-settings-container">
        <form method="post" action="options.php">
            <?php
            // Output nonce, action, and option_page fields
            settings_fields('plugin_slug_settings');
            
            // Output settings sections and fields
            do_settings_sections('plugin_slug_settings');
            ?>

            <table class="form-table">
                <!-- Enable/Disable -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_option_enabled">
                            <?php esc_html_e('Enable Plugin', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="plugin-slug-toggle">
                            <input type="checkbox" 
                                   name="plugin_slug_option_enabled" 
                                   id="plugin_slug_option_enabled" 
                                   value="1"
                                   <?php checked(get_option('plugin_slug_option_enabled', true)); ?>>
                            <span class="plugin-slug-toggle-label">
                                <?php esc_html_e('Enable plugin functionality', 'TEXT_DOMAIN'); ?>
                            </span>
                        </label>
                    </td>
                </tr>

                <!-- Mode Selection -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_option_mode">
                            <?php esc_html_e('Mode', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <select name="plugin_slug_option_mode" id="plugin_slug_option_mode">
                            <option value="standard" <?php selected(get_option('plugin_slug_option_mode', 'standard'), 'standard'); ?>>
                                <?php esc_html_e('Standard', 'TEXT_DOMAIN'); ?>
                            </option>
                            <option value="advanced" <?php selected(get_option('plugin_slug_option_mode'), 'advanced'); ?>>
                                <?php esc_html_e('Advanced', 'TEXT_DOMAIN'); ?>
                            </option>
                        </select>
                        <p class="description">
                            <?php esc_html_e('Select the operation mode for the plugin.', 'TEXT_DOMAIN'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Items Per Page -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_items_per_page">
                            <?php esc_html_e('Items Per Page', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="number" 
                               name="plugin_slug_items_per_page" 
                               id="plugin_slug_items_per_page" 
                               value="<?php echo esc_attr(get_option('plugin_slug_items_per_page', 20)); ?>"
                               min="1"
                               max="100"
                               class="small-text">
                        <p class="description">
                            <?php esc_html_e('Number of items to display per page in list views.', 'TEXT_DOMAIN'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Notification Email -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_notification_email">
                            <?php esc_html_e('Notification Email', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="email" 
                               name="plugin_slug_notification_email" 
                               id="plugin_slug_notification_email" 
                               value="<?php echo esc_attr(get_option('plugin_slug_notification_email', get_option('admin_email'))); ?>"
                               class="regular-text">
                        <p class="description">
                            <?php esc_html_e('Email address to receive notifications.', 'TEXT_DOMAIN'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Debug Mode -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_debug_mode">
                            <?php esc_html_e('Debug Mode', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="plugin-slug-toggle">
                            <input type="checkbox" 
                                   name="plugin_slug_debug_mode" 
                                   id="plugin_slug_debug_mode" 
                                   value="1"
                                   <?php checked(get_option('plugin_slug_debug_mode', false)); ?>>
                            <span class="plugin-slug-toggle-label">
                                <?php esc_html_e('Enable debug logging', 'TEXT_DOMAIN'); ?>
                            </span>
                        </label>
                        <p class="description">
                            <?php esc_html_e('When enabled, detailed logs will be written to the WordPress debug log.', 'TEXT_DOMAIN'); ?>
                        </p>
                    </td>
                </tr>

                <!-- Delete on Uninstall -->
                <tr>
                    <th scope="row">
                        <label for="plugin_slug_delete_on_uninstall">
                            <?php esc_html_e('Delete Data on Uninstall', 'TEXT_DOMAIN'); ?>
                        </label>
                    </th>
                    <td>
                        <label class="plugin-slug-toggle plugin-slug-toggle-danger">
                            <input type="checkbox" 
                                   name="plugin_slug_delete_on_uninstall" 
                                   id="plugin_slug_delete_on_uninstall" 
                                   value="1"
                                   <?php checked(get_option('plugin_slug_delete_on_uninstall', false)); ?>>
                            <span class="plugin-slug-toggle-label">
                                <?php esc_html_e('Remove all data when plugin is deleted', 'TEXT_DOMAIN'); ?>
                            </span>
                        </label>
                        <p class="description">
                            <strong class="plugin-slug-warning">
                                <?php esc_html_e('Warning: This will permanently delete all plugin data when you delete the plugin.', 'TEXT_DOMAIN'); ?>
                            </strong>
                        </p>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Save Settings', 'TEXT_DOMAIN')); ?>
        </form>
    </div>
</div>

<style>
.plugin-slug-settings-container {
    max-width: 800px;
    margin-top: 20px;
    background: #fff;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.plugin-slug-toggle {
    display: flex;
    align-items: center;
    gap: 10px;
}

.plugin-slug-toggle-label {
    font-weight: normal;
}

.plugin-slug-warning {
    color: #d63638;
}

.plugin-slug-toggle-danger input[type="checkbox"] {
    accent-color: #d63638;
}
</style>
