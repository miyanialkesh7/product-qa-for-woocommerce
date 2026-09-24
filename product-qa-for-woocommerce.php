<?php
/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://profiles.wordpress.org/vishalkakadiya/
 * @since             1.0.0
 * @package           Product_Faq_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:          Product QA For Woocommerce
 * Plugin URI:           https://wordpress.org/plugins/product-qa-for-woocommerce/
 * Description:          Add a customer-friendly product Q&A experience to WooCommerce product pages with moderation, email notifications, and easy management.
 * Version:              2.1
 * Author:               Vishal Kakadiya
 * Author URI:           http://profiles.wordpress.org/vishalkakadiya/
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:          product-qa-for-woocommerce
 * Domain Path:          /languages
 * Requires at least:    6.4
 * Tested up to:         7.1
 * Requires PHP:         7.4
 * Requires Plugins:     woocommerce
 * WC requires at least: 8.0
 * WC tested up to:      11.1
 */

// Abort if this file is accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Current plugin version, used for asset cache busting.
 */
define( 'PRODUCT_QA_FOR_WOOCOMMERCE_VERSION', '2.1' );

/**
 * Absolute path to the main plugin file.
 */
define( 'PRODUCT_QA_FOR_WOOCOMMERCE_FILE', __FILE__ );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-product-faq-for-woocommerce-activator.php.
 *
 * @since 1.0.0
 */
function product_faq_for_woocommerce_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-faq-for-woocommerce-activator.php';
	Product_Faq_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-product-faq-for-woocommerce-deactivator.php.
 *
 * @since 1.0.0
 */
function product_faq_for_woocommerce_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-product-faq-for-woocommerce-deactivator.php';
	Product_Faq_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'product_faq_for_woocommerce_activate' );
register_deactivation_hook( __FILE__, 'product_faq_for_woocommerce_deactivate' );

/**
 * Declare compatibility with WooCommerce features.
 *
 * The plugin only reads orders through the WooCommerce CRUD API (wc_get_orders()
 * and wc_customer_bought_product()), so it is safe with High-Performance Order
 * Storage. It does not touch the cart or checkout, so it is also compatible with
 * the Cart and Checkout blocks.
 *
 * @since 2.1
 */
function product_faq_for_woocommerce_declare_wc_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'product_faq_for_woocommerce_declare_wc_compatibility' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-product-faq-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function product_faq_for_woocommerce_run() {
	new Product_Faq_For_Woocommerce();
}

product_faq_for_woocommerce_run();
