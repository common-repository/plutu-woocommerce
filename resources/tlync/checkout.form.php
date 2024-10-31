<?php 
/**
 * Tlync checkout form file
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Resources
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="plutu_container tlync_payment_method">
    <div class="plutu_area">

        <div class="form-row">
            <?php echo esc_html( $this->instructions ); ?>
        </div>

        <div class="form-row">
            <input type="text" class="input-text plutu-text-center" name="mobile_number" placeholder="<?php esc_html_e('Mobile Number', 'plutu-woocommerce'); ?>" required="">
        </div>
        <br />
        <div class="form-row">
            <input type="text" class="input-text plutu-text-center" name="email" placeholder="<?php esc_html_e('Email (Optional)', 'plutu-woocommerce'); ?>">
        </div>

        <input type="hidden" name="checkout_page" value="<?php echo !is_checkout_pay_page()? 'Y' : 'N'; ?>">

    </div>
    
</div>
