<?php 
/**
 * Mpgs checkout form file
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Resources
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="plutu_container mpgs_payment_method">

    <div class="plutu_area">

        <div class="form-row">

            <?php if( !empty( $this->instructions ) ) { ?>

                <?php echo esc_html( $this->instructions ); ?>

            <?php }else{ ?>

                <?php esc_html_e('Pay by credit and debit card (Visa, Mastercard), after clicking "continue payment" you will be redirected to the payment service to complete your purchase securely.', 'plutu-woocommerce'); ?>

            <?php } ?>

        </div>
        
        <input type="hidden" name="checkout_page" value="<?php echo !is_checkout_pay_page()? 'Y' : 'N'; ?>">

    </div>

</div>
