<?php
/**
 * Plugin Name: Plutu WooCommerce
 * Plugin URI: https://plutu.ly.
 * Description: Plutu Payment Gateways for WooCommerce.
 * Author: Plutu
 * Author URI: https://plutu.ly
 * Version: 1.0.7
 * Text Domain: plutu-woocommerce
 * Domain Path: /languages
 * Requires at least: 5.7
 * Requires PHP: 7.4
 *
 * @package PlutuWoocommerce
 * @version 1.0.7
 * @link https://plutu.ly (Plutu)
 * @author Mohamed Endisha
 * @copyright Copyright (c) 2022 LibyanSpider.
 */
 
defined( 'ABSPATH' ) || exit;

// Define constants
if ( ! defined( 'PLUTU_WOOCOMMERCE_PLUGIN_DIR' ) ) {
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_DIR', __DIR__ );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_FILE', __FILE__ );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_CONFIG', __DIR__ . '/config' );
    define( 'PLUTU_WOOCOMMERCE_INCLUDES_ABSTRACTS', __DIR__ . '/includes/abstracts' );
    define( 'PLUTU_WOOCOMMERCE_INCLUDES_API', __DIR__ . '/includes/api' );
    define( 'PLUTU_WOOCOMMERCE_INCLUDES_GATEWAYS', __DIR__ . '/includes/gateways' );
    define( 'PLUTU_WOOCOMMERCE_INCLUDES_AJAX', __DIR__ . '/includes/ajax' );
    define( 'PLUTU_WOOCOMMERCE_INCLUDES_TRAITS', __DIR__ . '/includes/traits' );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_RESOURCES', __DIR__ . '/resources' );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_LANGUAGE', dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_ASSETS_JS_URL', plugin_dir_url( __FILE__ ) . 'assets/js' );
    define( 'PLUTU_WOOCOMMERCE_PLUGIN_ASSETS_CSS_URL', plugin_dir_url( __FILE__ ) . 'assets/css' );
}

/**
 * Begins execution
 *
 * Application
 */
add_action( 'plugins_loaded', function() {
    if( class_exists( 'WooCommerce' ) ) {
        include PLUTU_WOOCOMMERCE_PLUGIN_DIR . '/bootstrap/app.php';
    }
}, 10 );
