<?php
/**
 * Payment gateway abstract class
 * Abstract Plutu payment gateway
 * Hanldes generic payment gateway functionality which is extended by idividual payment gateways.
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Abstracts
 */

defined( 'ABSPATH' ) || exit;

class Plutu_Woocommerce_Payment_Gateway extends WC_Payment_Gateway {

    use Plutu_Woocommerce_Api_Error_Codes, Plutu_Woocommerce_Payment_Gateway_Helper;

    /**
     * Payment gateway order status
     *
     * @var string
     */
    protected $order_status;

    /**
     * Payment gateway maximum amount
     *
     * @var string
     */
    protected $maximum_amount;

    /**
     * Payment gateway minimum amount
     *
     * @var string
     */
    protected $minimum_amount;

    /**
     * Payment gateway currency
     *
     * @var string
     */
    protected $currency;
    
    /**
     * Payment gateway slug
     *
     * @var string
     */
    protected $gateway;

    /**
     * Create new instance
     *
     * @access public
     * @return void
     */
    public function __construct() {
        $this->icon = apply_filters( 'woocommerce_' . $this->id . '_icon', '' );
        $this->init_config_settings();
        $this->init_settings();
        $this->title = $this->get_option( 'title' );
        $this->description = $this->get_option( 'description' );
        $this->instructions = $this->get_option( 'instructions', $this->description );
        $this->order_status = $this->get_option( 'order_status' );
        $this->maximum_amount = $this->get_option( 'maximum_amount' );
        $this->minimum_amount = $this->get_option( 'minimum_amount' );
        $this->currency = $this->get_option( 'currency' );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options'] );
        add_action( 'woocommerce_api_' . $this->id, [$this, 'callback_handler'] );
        add_action( 'woocommerce_api_' . $this->id . '_return', [$this, 'return_handler'] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_filter( 'woocommerce_payment_gateways',[$this, 'register_payment_methods'] );
        add_filter( 'plugin_action_links_' . plugin_basename( PLUTU_WOOCOMMERCE_PLUGIN_FILE ), [$this, 'register_configure_link'] );
    }
    
    /**
     * Field settings
     *
     * @access public
     * @return void
     */
    public function init_config_settings() {
        $this->form_fields = $this->load_config_file( $this->id );
    }

    /**
     * Register payment gateways
     *
     * @access public
     * @return array
     */
    public function register_payment_methods( $gateways ) {
        return array_merge( $gateways, [get_class($this)] );
    }

    /**
     * Register payment gateway configuration link
     *
     * @access public
     * @param array
     */
    public function register_configure_link( $links ) {
        $label = sprintf( __( 'Configure %s', 'plutu-woocommerce' ), ( new $this )->method_title ) ;
        $url = admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $this->id );
        $link = "<a href=\"{$url}\">{$label}</a>";
        return in_array( $link, $links )? $links : array_merge( $links, [$link] );
    }

    /**
     * Enqueue scripts
     *
     * @access public
     * @return void
     */
    public function enqueue_scripts() {
        if('yes' === $this->enabled){
            if(is_cart() || is_checkout() || is_checkout_pay_page()){
                // Enqueue Plutu CSS stylesheet.
                if( !apply_filters( 'plutu_woocommerce_skip_include_css_file', false ) && !wp_style_is( 'plutu-woocommerce-css' ) ) {
                    wp_enqueue_style( 'plutu-woocommerce-css', PLUTU_WOOCOMMERCE_PLUGIN_ASSETS_CSS_URL . '/style.css' );
                }
                if( method_exists( $this, 'scripts' ) && !wp_script_is( $this->id . '-scripts' ) ) {
                    return $this->scripts();
                }
            }
        }
    }

    /**
     * Payment fields
     *
     * @access public
     * @return void
     */
    public function payment_fields() {

        // Gets order total from "pay for order" page.
        $order_id = absint( get_query_var( 'order-pay' ) );
        $amount = $this->get_order_total();

        try {
            // Check the cart total allowed for the payment method amount
            if( $this->is_valid_amount( $amount ) ) {
                $this->load_template( 'checkout.form', [
                    'amount' => $amount, 
                    'orderId' => $order_id
                ]);
            }
        } catch ( Exception $e ) {
            $message = apply_filters( 'plutu_woocommerce_error_alert_message', $e->getMessage() );
            $this->load_error_message( $message );
        }

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
        if(!in_array( $order_status, ['pending', 'failed'] ) ) {
            wc_add_notice( __( 'Order had already received payment' , 'plutu-woocommerce' ), 'error' );
            return;
        }
        // API parameters
        $parameters['amount'] = $order['total'];
        $parameters['invoice_no'] = $order_id;
        $parameters['customer_ip'] = $this->get_ip_address();

        // Send API request
        $api = new Plutu_Woocommerce_Api_Request;
        $api->set_payment_method( $this->gateway );
        $api_response = $api->process( $parameters );
        // If success?
        if( $api->get_status_code() == 200 ) {
            // Transaction id
            $transaction_id = sanitize_text_field( $api_response->result->transaction_id );
            // Add notes to the order.
            $wc_order->add_order_note( sprintf( __( 'Payment completed via %s!', 'plutu-woocommerce' ), $this->title ) );
            $wc_order->add_order_note( "Transaction ID <strong>{$transaction_id}</strong>" );

            // Set as complete payment
            if( !$this->order_status ) {
                $wc_order->payment_complete( $transaction_id );
            // Set as selected order status
            } else {
                $wc_order->set_transaction_id( $transaction_id );
                $wc_order->update_status( $this->order_status );
            }

            $response = [
                'result' => 'success',
                'redirect' => $this->get_return_url( $wc_order )
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

}