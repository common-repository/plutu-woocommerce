<?php 
/**
 * Adfali checkout form file
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Resources
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="plutu_container edfali_payment_method">
    <div class="plutu_area edfali_pay_container">
        <div id="send_confirmation_code_area">
            <div class="form-row">
                <?php esc_html_e('Please enter the mobile phone number subscribed to the Adfali service', 'plutu-woocommerce'); ?>
            </div>
            <div class="form-row">
                <input type="text" class="input-text plutu-text-center" id="edfali_mobile_number" name="edfali_mobile_number" placeholder="<?php esc_html_e('Mobile Number', 'plutu-woocommerce'); ?>">
            </div>
            <br />
            <div class="form-row">
                <button type="button" class="button alt" id="send_confirmation_code"><i class="fa fa-envelope"></i> <?php esc_html_e('Verify', 'plutu-woocommerce'); ?></button>
            </div>
        </div>
        <div class="plutu-success" id="edfali_success"></div>
        <div class="plutu-error" id="edfali_error"></div>
        <div id="confirmation_code_area" style="display:none;">
            <div class="form-row">
                <?php esc_html_e('Enter Confirmation Code', 'plutu-woocommerce'); ?>: 
            </div>
            <div class="form-row">
                <input type="text" class="input-text plutu-text-center" name="confirmation_code" placeholder="<?php esc_html_e('Confirmation Code', 'plutu-woocommerce'); ?>">
            </div>
            <div class="form-row">
                <?php esc_html_e("didn't receive a code?", 'plutu-woocommerce'); ?> <a href="#send_otp_code_area" id="resend_code"><?php esc_html_e('Resend', 'plutu-woocommerce'); ?></a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var _edfali_amount = <?php echo wp_json_encode($amount); ?>;
var _edfali_order_id = <?php echo wp_json_encode($orderId); ?>;
</script>
