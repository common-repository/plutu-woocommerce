<?php
/**
 * Sadad API payment method
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Gateways
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Sadad_Payment_Gateway extends Plutu_Woocommerce_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'sadadapi';
        $this->gateway = 'sadadapi';
        $this->order_button_text  = __('Proceed to Sadad', 'plutu-woocommerce');
        $this->method_title = __('Sadad', 'plutu-woocommerce');
        $this->method_description = __('Sadad Payment Gateway', 'plutu-woocommerce');
        $this->has_fields = true;
        parent::__construct();
    }

    /**
     * Enqueue scripts
     *
     * @access public
     * @return void
     */
    public function scripts() {
        $url = apply_filters( 'plutu_woocommerce_js_' . $this->id . '_url', PLUTU_WOOCOMMERCE_PLUGIN_ASSETS_JS_URL . '/' . $this->id . '.payment.js' );
        wp_register_script( $this->id . '-scripts', $url, ['jquery'] );
        wp_localize_script ($this->id . '-scripts', 'sadad_vars', array(
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( $this->id . '_nonce' ),
        ));
        wp_enqueue_script( $this->id . '-scripts' );
    }

    /**
     * Process the payment and return the result.
     *
     * @access public
     * @param  int $order_id Order ID.
     * @return array
     */
    public function process_payment( $order_id ) {
        // Declare parameters
        $otp = $this->sanitize_text_post_field_or_null( 'otp' );
        $process_id = WC()->session->get( "{$this->id}_process_id" );

        // Check the payment process
        if( empty( $process_id ) ) {
            wc_add_notice( __('Payment is not verified' , 'plutu-woocommerce' ), 'error' );
            return;
        }
                
        // Prepare API request parameters
        $parameters = [
            'code' => $otp,
            'process_id' => $process_id,
        ];
        // Process
        return $this->process( $order_id, $parameters );
    }

}
