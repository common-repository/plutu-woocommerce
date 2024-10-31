<?php 
/**
 * Sadad checkout form file
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Resources
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="plutu_container sadadapi_payment_method">
    <div class="plutu_area sadadapi_pay_container">
        <div id="send_otp_code_area">
            <div class="form-row">
                <?php esc_html_e('Please enter the mobile phone number subscribed to the Sadad service', 'plutu-woocommerce'); ?>
            </div>
            <div class="form-row">
                <input type="text" class="input-text plutu-text-center" id="sadad_mobile_number" name="sadad_mobile_number" placeholder="<?php esc_html_e('Mobile Number', 'plutu-woocommerce'); ?>" required="">
            </div>
            <br />
            <div class="form-row">
                <input type="text" class="input-text plutu-text-center" id="sadad_birth_year" name="sadad_birth_year" placeholder="<?php esc_html_e('Birth Year xxxx', 'plutu-woocommerce'); ?>" required="">
            </div>
            <br />
            <div class="form-row">
                <button type="button" class="button alt" id="send_otp_code"><i class="fa fa-envelope"></i> <?php esc_html_e('Verify' ,'plutu-woocommerce'); ?></button>
            </div>
        </div>
        <div class="plutu-success" id="sadad_success"></div>
        <div class="plutu-error" id="sadad_error"></div>
        <div id="otp_code_area" style="display:none;">
            <div class="form-row">
                <?php esc_html_e('Enter Confirmation Code', 'plutu-woocommerce'); ?>: 
            </div>
            <div class="form-row">
                <input type="text" class="input-text plutu-text-center" name="otp" placeholder="<?php esc_html_e('Confirmation Code', 'plutu-woocommerce'); ?>">
            </div>
            <div class="form-row">
                <?php esc_html_e("didn't receive a code?", 'plutu-woocommerce'); ?> <a href="#send_otp_code_area" id="resend_otp_code"><?php esc_html_e('Resend', 'plutu-woocommerce'); ?></a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
var _sadad_amount = <?php echo wp_json_encode($amount); ?>;
var _sadad_order_id = <?php echo wp_json_encode($orderId); ?>;
</script>
