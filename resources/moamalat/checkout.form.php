<?php 
/**
 * Local bank cards checkout form file
 *
 * @version 1.0.7
 * @package PlutuWoocommerce\Resources
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="plutu_container localbankcards_payment_method">

    <div class="plutu_area">

        <div class="form-row">

            <?php if( !empty( $this->instructions ) ) { ?>

                <?php echo esc_html( $this->instructions ); ?>

            <?php }else{ ?>

                <?php esc_html_e('Pay by Local bank card', 'plutu-woocommerce'); ?>

            <?php } ?>

        </div>
        
        <input type="hidden" name="checkout_page" value="<?php echo !is_checkout_pay_page()? 'Y' : 'N'; ?>">

    </div>

</div>
