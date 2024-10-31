<?php
/**
 * Payment gateway helper
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Traits
 */

defined( 'ABSPATH' ) || exit;

trait Plutu_Woocommerce_Payment_Gateway_Helper {

    /**
     * Sanitizes a string from user input or from the database
     *
     * @access public
     * @param  string $field
     * @param  array  $data
     * @return string|null
     */
    public function sanitize_text_field_or_null( $field, $data = []) {
        return isset( $data[ $field ] )? trim( sanitize_text_field( $data[ $field ] ) ) : null;
    }

    /**
     * Sanitizes a string from user _GET input
     *
     * @access public
     * @param  string $field
     * @return string|null
     */
    public function sanitize_text_get_field_or_null( $field ) {
        return isset( $_GET[ $field ] )? trim( sanitize_text_field( $_GET[ $field ] ) ) : null;
    }

    /**
     * Sanitizes a string from user _POST input
     *
     * @access public
     * @param  string $field
     * @return string|null
     */
    public function sanitize_text_post_field_or_null( $field ) {
        return isset( $_POST[ $field ] )? trim( sanitize_text_field( $_POST[ $field ] ) ) : null;
    }

    /**
     * Check that the cart total is allowed for the payment method amount
     * Verify that the order currency is equal to the payment method currency
     *
     * @access public
     * @param  string $amount
     * @return boolean
     * @throws Exception
     */
    public function is_valid_amount( $amount ) {
        $currency_accepted = true;
        // Check the currency
        if( apply_filters( 'plutu_woocommerce_currency_check', true ) ) {
            if( !empty( $this->currency ) ) {
                if( get_woocommerce_currency() != $this->currency ) {
                    $currency_accepted = false;
                }
            }
        }
        // If the currency is accepted
        if( $currency_accepted ) {
            // Allowed amounts for the payment method
            $maximum_amount = apply_filters( 'plutu_woocommerce_maximum_amount', $this->maximum_amount, $this->id );
            $minimum_amount = apply_filters( 'plutu_woocommerce_minimum_amount', $this->minimum_amount, $this->id );
            if( $amount > $maximum_amount ) {
                throw new Exception( sprintf( __( 'Sorry! Total is %s LYD higher than maximum amount %s LYD per transaction' , 'plutu-woocommerce'), $amount, $maximum_amount ) );
            } elseif ( $amount < $minimum_amount ) {
                throw new Exception( sprintf( __('Sorry! Total is %s LYD less than minimum amount %s LYD per transaction' , 'plutu-woocommerce'), $amount, $minimum_amount) );
            }
        } else {
            throw new Exception( __('The currency is not supported by this payment method', 'plutu-woocommerce') );
        }
        return true;
    }

    /**
     * Is valid params
     *
     * @access protected
     * @param  array  $data
     * @param  array  $fields
     * @return boolean
     */
    protected function is_valid_params( $data, $fields = []) {
        $valid = true;
        if(!empty($fields)){
            foreach ($fields as $field) {
                if(!isset($data[$field]) || $data[$field] === ''){
                    $valid = false;
                    break;
                }
            }
        }
        return $valid;
    }

    /**
     * Is the signature correct?
     *
     * @access protected
     * @param  array  $data
     * @param  array  $fields
     * @return boolean
     */
    protected function is_signature_valid( $data = [], $fields = [] ) {
        $data = apply_filters( 'plutu_woocommerce_signature_valid_data', $data, $this->id );
        $fields = apply_filters( 'plutu_woocommerce_signature_valid_fields', $fields, $this->id );

        $hash = '';
        if( isset( $data['hashed'] ) ) {
            $hash = $data['hashed'];
        }

        $data = array_filter($data, function($field) use ($fields){
            return in_array($field, $fields);
        }, ARRAY_FILTER_USE_KEY);
        
        $api_secret_key = apply_filters( 'plutu_woocommerce_api_secret_key', trim( $this->api_secret ), $this->id );
        if( !empty( $hash ) && !empty( $data ) && !empty( $api_secret_key ) ) {
            $data = http_build_query( $data );
            return $hash == strtoupper( hash_hmac( 'sha256', $data, $api_secret_key ) );
        }
        return false;
    }

    /**
     * Call redirect
     *
     * @access protected
     * @param  array $parameters
     * @return string
     */
    protected function redirect_url( $parameters = [] ) {
        remove_query_arg( 'wc-api' );
        $redirect_url = WC()->api_request_url( $this->id );
        $url = add_query_arg( $parameters, $redirect_url );
        return apply_filters( 'plutu_woocommerce_redirect_url', $url, $redirect_url, $parameters );
    }

    /**
     * Return url
     *
     * @access protected
     * @param  array $parameters
     * @return string
     */
    protected function return_url( $parameters = [] ) {
        remove_query_arg( 'wc-api' );
        $return_url = WC()->api_request_url( $this->id . '_return' );
        $url = add_query_arg( $parameters, $return_url );
        return apply_filters( 'plutu_woocommerce_return_url', $url, $return_url, $parameters );
    }
    
    /**
     * Set error note
     *
     * @param string $error
     * @param string $checkout_page
     * @param WC_Order $order
     * @param boolean $redirect
     * @return void
     */
    protected function set_error_note( $error, $checkout_page = '', $order = null, $redirect = true ) {
        $url = wc_get_checkout_url();
        if( !is_null( $order ) ) {
            $order->update_status( 'failed', $error );
            // redirect to the pay order page, If the order is received not from the checkout page
            if( $checkout_page == 'N' ) {
                $url = $order->get_checkout_payment_url();
            }
        }
        if ( function_exists( 'wc_add_notice' ) ) {
            wc_add_notice( $error, 'error' );
        }
        if( $redirect ){
            wp_redirect( $url );
        }
        die;
    }

    /**
     * Success redirect
     *
     * @access protected
     * @param  WC_Order $order
     * @return void
     */
    protected function success_redirect_return($order){
        wp_redirect( $this->get_return_url( $order ) );
        die;
    }
    
    /**
     * Get the total order
     *
     * @access protected
     * @param  int $order_id
     * @return float
     */
    protected function get_order_total_by_id( $order_id ) {
        $total = null;
        if ( 0 < $order_id ) {
            $order = wc_get_order( $order_id );
            if ( $order ) {
                $total = (float) $order->get_total();
            }
        }
        return $total;
    }

    /**
     * T-lync payment method
     *
     * @access public
     * @param  string $payment_method
     * @return string
     */
    public function tlync_payment_method( $payment_method = '' ) {
        $payment_methods = apply_filters('plutu_woocommerce_tlync_payment_methods', [
            'mobicash' => __('Mobicash', 'plutu-woocommerce'),
            'edfaly' => __('Edfaly', 'plutu-woocommerce'),
            'tadawul' => __('Tadawul Card', 'plutu-woocommerce'),
            'sadad' => __('Sadad', 'plutu-woocommerce'),
            'moamalat' => __('Local Bank Card', 'plutu-woocommerce'),
        ]);
        return isset( $payment_methods[ $payment_method ] )? $payment_methods[ $payment_method ] : ucfirst( $payment_method );
    }

    /**
     * Validate mobile number format for Libyan number format and the prefix must be 09x
     * Return the formatted mobile number if successful, otherwise return null
     *
     * @access public
     * @param  int  $mobile
     * @param  boolean  $all
     * @return string|null
     */
    public function is_valid_mobile_number( $mobile, $all = true ) {
        $pattern = $all? '((\+|00)?218|0?)?(9[0-9]{8})' : '((\+|00)?218|0?)?(9[13][0-9]{7})';
        if( preg_match( '/^' . $pattern . '$/', $mobile, $match ) ) {
            return $match[sizeof($match)-1];
        }
        return null;
    }

    /**
     * Get IP address
     *
     * @access public
     * @return string
     */
    public function get_ip_address() {
        if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
        } elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = rest_is_ip_address( trim( current( preg_split( '/,/', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) ) ) );
        } else {
            $ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
        }
        return (string) $ip;
    }

    /**
     * Load payment method config file
     *
     * @access public
     * @param  string $file_name
     * @return array
     */
    public function load_config_file( $file_name ) {
        $config = [];
        $file = realpath( PLUTU_WOOCOMMERCE_PLUGIN_CONFIG . '/' . $file_name . '-fields.php' );
        if(file_exists($file)){
            $config = include realpath($file);
            $config = apply_filters( 'plutu_woocommerce_' . $file_name . '_settings', $config );
        }
        return $config;
    }
    
    /**
     * Load template
     *
     * @param  string $file_name
     * @param  array  $args
     * @return void print HTML
     */
    public function load_template( $file_name, $args = [] ) {
        if( !empty( $args ) ) {
            extract( $args );
        }

        // Returns trailing name component of path
        $file_name = basename( $file_name, '.php' ) . '.php';
        
        /**
         * Set or override plugin resource files
         * to overrride from child-theme it must be set in this path plutu-woocommerce/{payment_method}/
         *
         * @var string
         */
        $paths = apply_filters( 'plutu_woocommerce_resources_path', [
            get_stylesheet_directory() . '/plutu-woocommerce/' . $this->id .'/' . $file_name,
            PLUTU_WOOCOMMERCE_PLUGIN_RESOURCES . '/' . $this->id .'/' . $file_name,
        ], $this->id, $file_name );

        // Load template
        foreach( $paths as $file ) {
            $file = realpath( $file );
            if( file_exists( $file ) ) {
                include_once realpath( $file );
                break;
            }
        }

    }

    /**
     * Load error message block
     *
     * @access public
     * @param  string $message
     * @return void print HTML
     */
    public function load_error_message($message){
        $style = apply_filters( 'plutu_woocommerce_error_message_css_class', 'woocommerce-error' );
        ?>
        <div class="<?php echo esc_attr($style); ?>">
            <?php echo esc_html($message); ?>
        </div>
        <?php
    }

}
