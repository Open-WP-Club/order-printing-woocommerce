<?php

/**
 * Plugin Name: Order Printing for WooCommerce
 * Plugin URI: https://github.com/Open-WP-Club/order-printing-woocommerce
 * Description: Print WooCommerce orders as PDF directly from the order edit screen.
 * Version: 1.0.0
 * Author: OpenWPClub.com
 * Author URI: https://github.com/Open-WP-Club/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: order-printing-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 9.6
 */

defined('ABSPATH') || exit;

define('OPW_VERSION', '1.0.0');
define('OPW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPW_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Declare HPOS compatibility.
 */
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

/**
 * Check if WooCommerce is active before loading.
 */
add_action('init', function () {
    load_plugin_textdomain('order-printing-woocommerce', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

function opw_init() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            esc_html_e('Order Printing for WooCommerce requires WooCommerce to be installed and active.', 'order-printing-woocommerce');
            echo '</p></div>';
        });
        return;
    }

    $autoload = OPW_PLUGIN_DIR . 'vendor/autoload.php';
    if (!file_exists($autoload)) {
        add_action('admin_notices', function () {
            echo '<div class="notice notice-error"><p>';
            esc_html_e('Order Printing for WooCommerce: Please run "composer install" in the plugin directory.', 'order-printing-woocommerce');
            echo '</p></div>';
        });
        return;
    }

    require_once $autoload;
    require_once OPW_PLUGIN_DIR . 'includes/class-order-pdf-generator.php';
    require_once OPW_PLUGIN_DIR . 'includes/class-order-printing.php';

    Screeen\OrderPrinting\Order_Printing::instance();
}
add_action('plugins_loaded', 'opw_init');
