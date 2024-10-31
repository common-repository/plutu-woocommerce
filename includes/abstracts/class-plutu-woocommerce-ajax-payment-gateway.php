<?php
/**
 * AJAX payment abstract gateway
 * Handles Ajax requests for payment gateway functionality
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Abstracts
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Ajax_Payment_Gateway{

    use Plutu_Woocommerce_Api_Error_Codes, Plutu_Woocommerce_Payment_Gateway_Helper;

    /**
     * Payment method key
     *
     * @var string
     */
    protected $key;

    /**
     * Payment method name
     *
     * @var string
     */
    protected $payment;

    /**
     * Payment method nonce
     *
     * @var string
     */
    protected $nonce;

    /**
     * Create new instance
     *
     * @access public
     * @return void
     */
    public function __construct() {
        if( !empty( $this->key ) ) {
            add_action( 'wp_ajax_' . $this->key . '_verify', [$this, 'send_otp_request'] );
            add_action( 'wp_ajax_nopriv_' . $this->key . '_verify', [$this, 'send_otp_request'] );
        }
    }

    /**
     * Send otp request
     *
     * @access public
     * @return void print json
     */
    protected function verify_processing( $parameters ) {
        
        // Verifies that a correct security nonce was used
        if ( !wp_verify_nonce( $parameters['nonce'], $this->nonce ) ) {
            wp_send_json_error( ['error' => ['message' => __( 'Token mismatch, please refresh the page', 'plutu-woocommerce' )]] );
        }
        // Check if the amount is a number without any characters
        if( !is_numeric($parameters['amount']) ){
            wp_send_json_error( ['error' => ['message' => __( 'Amount must be integer', 'plutu-woocommerce' )]] );
        }
        // In some cases, the total changed if fees added or coupons applied
        // To prevent conflict, get the total from the order
        if( absint( $parameters['order_id'] ) > 0){
            $parameters['amount'] = $this->get_order_total_by_id( $parameters['order_id'] );
        }

        // Remove nonce
        unset( $parameters['nonce'] );
        // Send API request
        $api = new Plutu_Woocommerce_Api_Request;
        $api->set_payment_method( $this->payment );
        $api_response = $api->verify( $parameters );
        // Successful request
        if( $api->get_status_code() == 200 ) {
            $process_id = null;
            if( isset( $api_response->result->process_id ) ) {
                $process_id = sanitize_text_field( $api_response->result->process_id );
            }
            // Set Plutu Payment Process Identifier
            WC()->session->set( "{$this->payment}_process_id" , $process_id );
            wp_send_json_success( ['message' => __( 'An OTP has been sent to your mobile number', 'plutu-woocommerce' )] );
        }

        // Handle errors
        $error_code = isset($api_response->error->code)? $api_response->error->code : '';
        $error_message = $this->get_api_error_code( $error_code );
        wp_send_json_error( ['error' => ['message' => $error_message]] );

    }

}
