<?php
/**
 * Email Wrapper Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($title); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .email-header {
            text-align: center;
            padding: 30px 0;
            border-bottom: 3px solid #0073aa;
        }
        .email-header h1 {
            margin: 0;
            color: #0073aa;
            font-size: 24px;
        }
        .email-body {
            background: #fff;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .email-footer {
            text-align: center;
            padding: 20px;
            color: #666;
            font-size: 12px;
        }
        .email-footer a {
            color: #0073aa;
            text-decoration: none;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f9f9f9;
            font-weight: 600;
        }
        .total-row td {
            font-weight: 700;
            font-size: 16px;
            border-top: 2px solid #ddd;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #0073aa;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .info-box {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-box dt {
            font-weight: 600;
            margin-top: 10px;
        }
        .info-box dt:first-child {
            margin-top: 0;
        }
        .info-box dd {
            margin: 5px 0 0;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
        </div>
        <div class="email-body">
            <?php echo wp_kses_post($content); ?>
        </div>
        <div class="email-footer">
            <p>
                <?php echo esc_html__('This email was sent from', 'cart-quote-woocommerce-email'); ?>
                <a href="<?php echo esc_url(home_url()); ?>"><?php echo esc_html(get_bloginfo('name')); ?></a>
            </p>
            <p>
                <?php echo esc_html(get_bloginfo('admin_email')); ?>
            </p>
        </div>
    </div>
</body>
</html>
