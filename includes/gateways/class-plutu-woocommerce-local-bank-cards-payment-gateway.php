<?php
/**
 * Local bank cards payment method
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Gateways
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Local_Bank_Cards_Payment_Gateway extends Plutu_Woocommerce_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'moamalat';
        $this->gateway = 'localbankcards';
        $this->order_button_text = __( 'Continue to payment', 'plutu-woocommerce' );
        $this->method_title = __( 'Local bank card', 'plutu-woocommerce' );
        $this->method_description = __( 'Local Bank Card Payment Gateway', 'plutu-woocommerce' );
        $this->api_secret = $this->get_option('api_secret');
        $this->has_fields = true;
        parent::__construct();
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
        $checkout_page = $this->sanitize_text_post_field_or_null( 'checkout_page' );

        // Prepare API request parameters
        $parameters = [
            'checkout_page' => $checkout_page
        ];
        // Process
        return $this->process( $order_id, $parameters );
    }

    /**
     * Process
     *
     * @access protected
     * @param  int $order_id
     * @param  array $parameters
     * @return array
     */
    protected function process( $order_id, $parameters ) {
        // Fetch order
        $wc_order = new WC_Order( $order_id );
        $order = $wc_order->get_data();
        $order_status = $order['status'];
        // Check order status
        if( ! in_array( $order_status, ['pending', 'failed'] ) ) {
            wc_add_notice(__( 'Order had already received payment' , 'plutu-woocommerce' ), 'error' );
            return;
        }

        // Passing parameters to return URL
        $args = [];
        if( isset( $parameters['checkout_page'] ) ) {
            $args['checkout_page'] = $parameters['checkout_page'];
            unset( $parameters['checkout_page'] );
        }

        // API parameters
        $parameters['amount'] = $order['total'];
        $parameters['invoice_no'] = $order_id;
        $parameters['return_url'] = $this->redirect_url( $args );
        $parameters['customer_ip'] = $this->get_ip_address();

        // Send API request
        $api = new Plutu_Woocommerce_Api_Request;
        $api->set_payment_method( $this->gateway, $this->id );
        $api_response = $api->process( $parameters );
        // Successful request
        if( $api->get_status_code() == 200 ) {
            $response = [
                'result' => 'success',
                'redirect' => $api_response->result->redirect_url
            ];
        // Otherwise, return an error
        }else{
            // Errors
            $error_code = isset( $api_response->error->code )? $api_response->error->code : '';
            $error_message = sanitize_text_field( $this->get_api_error_code( $error_code ) );
            // Add and store a notice
            wc_add_notice( $error_message, 'error' );
            $wc_order->update_status( 'failed', $error_message );

            $response = [
                'result' => 'failure',
            ];
        }

        return $response;
    }

    /**
     * Callback handler
     *
     * @access public
     * @return void
     */
    public function callback_handler() {

        if ( ! isset( $_GET['hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error);
        }

        if ( !$this->is_valid_params( $_GET, ['transaction_id', 'invoice_no', 'amount', 'gateway', 'hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error);
        }
        // Define response data
        $approved = $this->sanitize_text_get_field_or_null( 'approved' );
        $canceled = $this->sanitize_text_get_field_or_null( 'canceled' );
        $transaction_id = $this->sanitize_text_get_field_or_null( 'transaction_id' );
        $order_id = $this->sanitize_text_get_field_or_null( 'invoice_no' );
        $amount = $this->sanitize_text_get_field_or_null( 'amount' );
        $checkout_page = $this->sanitize_text_get_field_or_null( 'checkout_page' );

        // Get order details
        $order = wc_get_order( $order_id );
        if( !$order || is_null( $order ) || empty( $order ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note( $error, $checkout_page );
        }
        // Is the signature correct?
        if( $this->is_signature_valid( $_GET, ['gateway', 'approved', 'canceled', 'invoice_no', 'amount', 'transaction_id'] ) ) {
            if( $canceled ) {
                $error = __( 'Payment canceled by the customer', 'plutu-woocommerce' );
                return $this->set_error_note( $error, $checkout_page, $order );
            }

            if( $approved ) {
                // Check order status is unpaid
                if( !in_array( $order->get_status(), ['pending', 'failed'] ) ) {
                    $error = __( 'Order had already received payment', 'plutu-woocommerce' );
                    return $this->set_error_note( $error, $checkout_page, $order );
                }
                // Order total
                $order_amount = $order->get_total();
                if($amount != $order_amount){
                    $error = sprintf( __( 'Order amount %s is not equal to transaction amount %s, partial payment is not allowed', 'plutu-woocommerce' ), $order_amount, $amount );
                    return $this->set_error_note( $error, $checkout_page, $order );
                }
                // Add notes to the order.
                $order->add_order_note( sprintf( __( 'Payment completed via %s!', 'plutu-woocommerce' ), $this->title ) );
                $order->add_order_note( "Transaction ID <strong>{$transaction_id}</strong>" );
                // Set as complete payment
                if( !$this->order_status ){
                    $order->payment_complete( $transaction_id );
                // Set as selected order status
                }else{
                    $order->set_transaction_id( $transaction_id );
                    $order->update_status( $this->order_status );
                }
                return $this->success_redirect_return( $order );
            }

        }else{
            // Add order note to admin for this case
            $order->add_order_note( __( 'The request signature calculated does not match your store signature. Check your secret key', 'plutu-woocommerce' ) );
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note( $error, $checkout_page, $order );
        }

    }

}
