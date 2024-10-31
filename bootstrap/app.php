<?php
/**
 * App File
 * It will be executed inside the plugins loaded
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Bootstrap
 */

defined( 'ABSPATH' ) || exit;

// Create a simple autoloader for the plugin
$autoload = [
    PLUTU_WOOCOMMERCE_INCLUDES_API => true,
    PLUTU_WOOCOMMERCE_INCLUDES_TRAITS => true,
    PLUTU_WOOCOMMERCE_INCLUDES_ABSTRACTS => false,
    PLUTU_WOOCOMMERCE_INCLUDES_GATEWAYS => true,
    PLUTU_WOOCOMMERCE_INCLUDES_AJAX => true,
];
foreach( $autoload as $folder => $declare ){
    if( is_dir( $folder ) ) {
        foreach( glob( $folder . "/*.php" ) as $file ) {
            if( file_exists( $file ) ) {
                include realpath( $file );
                $class = ucwords( str_replace( '-', '_', substr( basename( $file, '.php' ), 6) ), '_' );
                if( $declare && class_exists( $class ) ) {
                    new $class;
                }
            }
        }
    }
}
// Loads a plugin's translated strings.
load_plugin_textdomain( 'plutu-woocommerce', false, PLUTU_WOOCOMMERCE_PLUGIN_LANGUAGE );
