<?php
/**
 * Local Bank Cards Gateway Configs
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Config
 */

defined( 'ABSPATH' ) || exit;

return [
    'enabled' => [
        'title' => __( 'Enable', 'plutu-woocommerce' ),
        'type' => 'checkbox',
        'label' => __( 'Enable/Disable', 'plutu-woocommerce' ),
        'default' => 'no'
    ],
    'title' => [
        'title' => __( 'Title', 'plutu-woocommerce' ),
        'type' => 'text',
        'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'plutu-woocommerce' ),
        'default' => __( 'Local Bank Card', 'plutu-woocommerce' ),
        'desc_tip' => false,
    ],
    'description' => [
        'title' => __( 'Description', 'plutu-woocommerce' ),
        'type' => 'textarea',
        'description' => __( 'This controls the description for the payment method the customer sees during checkout.', 'plutu-woocommerce' ),
        'default' => __( 'Pay by Local Bank Card', 'plutu-woocommerce' ),
        'desc_tip' => false,
    ],
    'maximum_amount' => [
        'title' => __( 'Maximum amount (LYD)', 'plutu-woocommerce' ),
        'type' => 'number',
        'description' => __( 'Maximum limit per transaction.', 'plutu-woocommerce' ),
        'default' => '5000',
        'desc_tip' => false,
    ],
    'minimum_amount' => [
        'title' => __( 'Minimum amount (LYD)', 'plutu-woocommerce' ),
        'type' => 'number',
        'description' => __( 'Minimum limit per transaction.', 'plutu-woocommerce' ),
        'default' => '5',
        'desc_tip' => false,
    ],
    'currency' => [
        'title' => __( 'Currency', 'plutu-woocommerce' ),
        'description' => __( 'The supported currency for the payment method. Leave it blank to ignore.', 'plutu-woocommerce' ),
        'type' => 'select',
        'class' => 'wc-enhanced-select',
        'options' => array_merge(['' => ''], get_woocommerce_currencies()),
        'default' => 'LYD',
        'desc_tip' => false,
    ],
    'order_status' => [
        'title' => __( 'Next Order Status', 'plutu-woocommerce' ),
        'description' => __( 'This controls the Order status when payment is completed.', 'plutu-woocommerce' ),
        'type' => 'select',
        'default' => 'processing',
        'options' => array_merge( [''], array_combine(
                          array_map(
                            function( $status ) {
                                return str_replace('wc-', '', $status);
                            }, array_keys( wc_get_order_statuses() ) ), array_values( wc_get_order_statuses() ) ) ),
        'desc_tip' => false,
    ],
    'api_key' => [
        'title' => __( 'Api Key', 'plutu-woocommerce' ),
        'type' => 'text',
        'description' => __( 'Plutu Api Key.', 'plutu-woocommerce' ),
        'desc_tip' => false,
    ],
    'api_secret' => [
        'title' => __( 'Api Secret', 'plutu-woocommerce' ),
        'type' => 'text',
        'description' => __( 'Plutu Api Secret Key.', 'plutu-woocommerce' ),
        'desc_tip' => false,
    ],
    'access_token' => [
        'title' => __( 'Access Token', 'plutu-woocommerce' ),
        'type' => 'textarea',
        'description' => __( 'Plutu Access Token.', 'plutu-woocommerce' ),
        'desc_tip' => false,
    ],
];
