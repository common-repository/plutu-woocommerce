<?php
/**
 * API error codes
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Traits
 */

defined( 'ABSPATH' ) || exit;

trait Plutu_Woocommerce_Api_Error_Codes {

    /**
     * Get error code
     *
     * @access public
     * @param  string $code
     * @return string
     */
    public function get_api_error_code( $code ) {

        $errors = [
            'FORBIDDEN_IP_ADDRESS' => __( 'Oops! IP address restrictions, Please contact the site administrator', 'plutu-woocommerce' ),
            'SECRET_KEY_DOES_NOT_EXIST' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'INVALID_MERCHANT_SECRET_KEY' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'AUTH_ERROR' => __( 'Something went wrong', 'plutu-woocommerce' ),
            'MISSING_PARAMETER' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'NOT_SUBSCRIBED' => __( 'The mobile number is not subscribed to Adfali service', 'plutu-woocommerce' ),
            'INVALID_AMOUNT' => __( 'Invalid amount', 'plutu-woocommerce' ),
            'CONFIRMATION_ERROR' => __( 'Something went wrong, please check the OTP code', 'plutu-woocommerce' ),
            'BACKEND_ERROR' => __( 'Backend error, Please contact the site administrator', 'plutu-woocommerce' ),
            'INVALID_GATEWAY' => __( 'Something went wrong', 'plutu-woocommerce' ),
            'INVALID_INPUTS' => __( 'Please make sure all payment fields are filled out correctly', 'plutu-woocommerce' ),
            'MISSING_QR_PARAMETER' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'REFERENCE_NUMBER_DOES_NOT_EXIST' => __( 'Reference number does not exist', 'plutu-woocommerce' ),
            'DENIED_ACCESS_GATEWAY' => __( 'Something went wrong', 'plutu-woocommerce' ),
            'UNAUTHORIZED' => __( 'Something went wrong', 'plutu-woocommerce' ),
            'INVALID_MOBILE_NUMBER_OR_BIRTH_YEAR' => __( 'Incorrect mobile number/year of birth', 'plutu-woocommerce' ),
            'INVALID_MOBILE_NUMBER' => __( 'Incorrect mobile number', 'plutu-woocommerce' ),
            'CHECK_BANK_ACCOUNT' => __( 'There is a problem with your account, please check with your bank', 'plutu-woocommerce' ),
            'ADD_PAYMENT_ERROR' => __( 'Something went wrong', 'plutu-woocommerce' ),
            'BACKEND_SERVER_ERROR' => __( 'There is a problem connecting to the bank server, please try again later', 'plutu-woocommerce' ),
            'INVLIAD_OTP' => __( 'Invalid OTP. please check your code and try again', 'plutu-woocommerce' ),
            'INVALID_INVOICE_AMOUNT_OR_NUMBER' => __( 'Incorrect invoice ID or amount', 'plutu-woocommerce' ),
            'DUPLICATED_INVOICE_NUMBER' => __( 'Invoice number already exists', 'plutu-woocommerce' ),
            'EMPTY_MOBILE_NUMBER' => __( 'The mobile number is empty', 'plutu-woocommerce' ),
            'EMPTY_BIRTH_YEAR' => __( 'Birth year is empty', 'plutu-woocommerce' ),
            'OTP_EXPIRED' => __( 'The OTP has exceeded the time allowed for its use', 'plutu-woocommerce' ),
            'OTP_WAIT_BEFORE_RESNED' => __( 'Please wait a while before requesting an OTP resend again', 'plutu-woocommerce' ),
            'INVALID_MERCHANT_CATEGORY' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'UNAUTHORIZED_MERCHANT_ACCOUNT' => __( 'Oops! configuration missing, Please contact the site administrator', 'plutu-woocommerce' ),
            'NOT_ALLOWED_AMOUNT' => __( 'Transaction amount is not allowed', 'plutu-woocommerce' ),
            'AMOUNT_EXCEEDED_MAXIMUM' => __( 'The order amount exceeded the maximum amount allowed for the transaction', 'plutu-woocommerce' ),
            'INSUFFICIENT_BALANCE' => __( 'Insufficient balance for the transaction', 'plutu-woocommerce' ),
            'CURRENCY_NOT_SUPPORTED' => __( 'Currency is not supported', 'plutu-woocommerce' ),
        ];

        if( array_key_exists( $code, $errors ) ) {
            return $errors[$code];
        }
        return __( 'Something went wrong', 'plutu-woocommerce' );
        
    }

}
