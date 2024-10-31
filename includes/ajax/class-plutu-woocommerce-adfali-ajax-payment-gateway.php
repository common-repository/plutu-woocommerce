<?php
/**
 * Adfali ajax
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Ajax
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Adfali_Ajax_Payment_Gateway extends Plutu_Woocommerce_Ajax_Payment_Gateway {

    /**
     * Payment method key
     *
     * @var string
     */
    protected $key = 'edfali';

    /**
     * Payment method name
     *
     * @var string
     */
    protected $payment = 'edfali';

    /**
     * Payment method nonce
     *
     * @var string
     */
    protected $nonce = 'edfali_nonce';

    /**
     * Send otp request
     *
     * @access public
     * @return void print json
     */
    public function send_otp_request() {

        $order_id = $this->sanitize_text_post_field_or_null( 'order_id' );
        $amount = $this->sanitize_text_post_field_or_null( 'amount' );
        $mobile_number = $this->sanitize_text_post_field_or_null( 'mobile_number' );
        $nonce = $this->sanitize_text_post_field_or_null( 'nonce' );

        // Verify that the mobile phone number field is not empty
        if( empty($mobile_number) ){
            wp_send_json_error( ['error' => ['message' => __( 'Mobile number is required', 'plutu-woocommerce' )]] );
        }
        // Check valid mobile number format
        $mobile_number = $this->is_valid_mobile_number( $mobile_number );
        if( is_null($mobile_number) ){
            wp_send_json_error( ['error' => ['message' => __( 'Invalid mobile number format', 'plutu-woocommerce' )]] );
        }

        return $this->verify_processing([
            'order_id' => $order_id,
            'amount' => $amount,
            'mobile_number' => $mobile_number,
            'nonce' => $nonce
        ]);
    }

}
