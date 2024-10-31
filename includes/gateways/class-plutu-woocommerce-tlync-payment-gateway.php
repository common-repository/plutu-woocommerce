<?php
/**
 * Tlync payment method
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Gateways
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Tlync_Payment_Gateway extends Plutu_Woocommerce_Payment_Gateway {

    /**
     * Constructor for the gateway.
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->id = 'tlync';
        $this->gateway = 'tlync';
        $this->order_button_text = __( 'Continue to payment', 'plutu-woocommerce' );
        $this->method_title = __( 'T-lync', 'plutu-woocommerce' );
        $this->method_description = __( 'T-Lync online payment platform', 'plutu-woocommerce' );
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
        $mobile_number = $this->sanitize_text_post_field_or_null( 'mobile_number' );
        $email = $this->sanitize_text_post_field_or_null( 'email' );

        // Check valid mobile number format
        $mobile_number = $this->is_valid_mobile_number( $mobile_number );
        if( is_null($mobile_number) ){
            wc_add_notice( __( 'Invalid mobile number format', 'plutu-woocommerce' ), 'error' );
            return;
        }

        // Check valid email format
        if( !empty($email) && !is_email($email) ){
            wc_add_notice( __( 'Invalid email format', 'plutu-woocommerce' ), 'error' );
            return;
        }

        // Prepare API request parameters
        $parameters = [
            'checkout_page' => $checkout_page,
            'mobile_number' => $mobile_number
        ];

        if( !empty($email) ){
            $parameters['email'] = $email;
        }

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
        $checkout_page = '';
        if( isset( $parameters['checkout_page'] ) ) {
            $args['checkout_page'] = $parameters['checkout_page'];
            unset( $parameters['checkout_page'] );
        }

        // API parameters
        $parameters['amount'] = $order['total'];
        $parameters['invoice_no'] = $order_id;
        $parameters['callback_url'] = $this->redirect_url( $args );
        $parameters['return_url'] = $this->return_url( $args );
        $parameters['customer_ip'] = $this->get_ip_address();

        // Send API request
        $api = new Plutu_Woocommerce_Api_Request;
        $api->set_payment_method( $this->gateway );
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

        $data = file_get_contents("php://input");
        $data = (array) json_decode($data, true);

        if ( ! isset( $data['hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error, '', null, false);
        }

        if ( !$this->is_valid_params( $data, ['gateway', 'approved', 'invoice_no', 'amount', 'transaction_id', 'payment_method', 'hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error, '', null, false);
        }
        // Define response data
        $approved = $this->sanitize_text_field_or_null( 'approved', $data );
        $transaction_id = $this->sanitize_text_field_or_null( 'transaction_id', $data );
        $order_id = $this->sanitize_text_field_or_null( 'invoice_no', $data );
        $amount = $this->sanitize_text_field_or_null( 'amount', $data );
        $payment_method = $this->sanitize_text_field_or_null( 'payment_method', $data );

        // Get order details
        $order = wc_get_order( $order_id );
        if( !$order || is_null( $order ) || empty( $order ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note( $error, '', null, false );
        }
        // Is the signature correct?
        if( $this->is_signature_valid( $data, ['gateway', 'approved', 'invoice_no', 'amount', 'transaction_id', 'payment_method'] ) ) {

            // Check order status is unpaid
            if( !in_array( $order->get_status(), ['pending', 'failed'] ) ) {
                $error = __( 'Order had already received payment', 'plutu-woocommerce' );
                return $this->set_error_note( $error, '', null, false );
            }

            if( !$approved ) {
                $error = __( 'Payment failed', 'plutu-woocommerce' );
                return $this->set_error_note( $error, '', $order, false );
            }else{
                // Order total
                $order_amount = $order->get_total();
                if($amount != $order_amount){
                    $error = sprintf( __( 'Order amount %s is not equal to transaction amount %s, partial payment is not allowed', 'plutu-woocommerce' ), $order_amount, $amount );
                    return $this->set_error_note( $error, '', $order, false );
                }

                // T-lync payment method
                $payment_method = $this->tlync_payment_method( $payment_method );

                // Add notes to the order.
                $order->add_order_note( sprintf( __( 'Payment completed via %s! - %s', 'plutu-woocommerce' ), $this->title, $payment_method ) );
                $order->add_order_note( "Transaction ID <strong>{$transaction_id}</strong>" );
                // Set as complete payment
                if( !$this->order_status ){
                    $order->payment_complete( $transaction_id );
                // Set as selected order status
                }else{
                    $order->set_transaction_id( $transaction_id );
                    $order->update_status( $this->order_status );
                }
            }

        }else{
            // Add order note to admin for this case
            $order->add_order_note( __( 'The request signature calculated does not match your store signature. Check your secret key', 'plutu-woocommerce' ) );
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note( $error, '', $order, false );
        }
        die;
    }

    /**
     * Return handler
     *
     * @access public
     * @return array
     */
    public function return_handler(){

        if ( ! isset( $_GET['hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error);
        }

        if ( !$this->is_valid_params( $_GET, ['approved', 'invoice_no', 'hashed'] ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note($error);
        }

        // Define response data
        $approved = $this->sanitize_text_get_field_or_null( 'approved' );
        $order_id = $this->sanitize_text_get_field_or_null( 'invoice_no' );
        $checkout_page = $this->sanitize_text_get_field_or_null( 'checkout_page' );

        // Get order details
        $order = wc_get_order( $order_id );
        if( !$order || is_null( $order ) || empty( $order ) ) {
            $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
            return $this->set_error_note( $error, $checkout_page );
        }

        // Is the signature correct? 
        if( $this->is_signature_valid( $_GET, ['approved', 'invoice_no'] ) ) {

            // Check order status is paid
            if( in_array( $order->get_status(), ['pending', 'failed'] ) ) {
                $error = __( 'Payment unsuccessful, Try again or contact the site administrator', 'plutu-woocommerce' );
                return $this->set_error_note( $error, $checkout_page );
            }

            if( $approved ) {
                return $this->success_redirect_return( $order );
            }

        }

        $error = __( 'Invalid redirect link', 'plutu-woocommerce' );
        return $this->set_error_note( $error, $checkout_page );

    }

}
