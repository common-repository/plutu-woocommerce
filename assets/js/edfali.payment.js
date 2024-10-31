/**
 * Initialization
 */
jQuery(document).ready(function(){
    edfali_container();
    jQuery('body').on( 'updated_checkout', function() {
        edfali_container();
    });
});
/**
 * Edfali container
 */
function edfali_container(){
    jQuery("#send_confirmation_code").click(sendEdfaliOTP);
    jQuery('#edfali_mobile_number').keypress(function(e){
        var keyCode = e.keyCode || e.which;
        if (keyCode == 13){
            e.preventDefault();
            sendEdfaliOTP();
            return false;
        }
    });
    jQuery("#resend_code").click(function(e){
        e.preventDefault();
        jQuery('#confirmation_code_area').hide();
        jQuery('#edfali_success').hide();
        jQuery('#edfali_error').hide();
        jQuery('#send_confirmation_code_area').fadeIn();
    });
}
/**
 * API Request
 */
function sendEdfaliOTP() {
    jQuery('#edfali_mobile_number').prop("disabled", true);
    jQuery('#send_confirmation_code').prop("disabled", true);
    jQuery('#send_confirmation_code i').removeClass("fa-envelope");
    jQuery('#send_confirmation_code i').addClass("fa-spinner fa-spin");

    jQuery('#edfali_success').hide();
    jQuery('#edfali_error').hide();

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: edfali_vars.url,
        data: {
            'order_id': _edfali_order_id,
            'amount': _edfali_amount,
            'mobile_number': jQuery('#edfali_mobile_number').val(),
            'nonce': edfali_vars.nonce,
            'action': 'edfali_verify' 
        }, success: function (result) {
            
            jQuery('#edfali_mobile_number').prop("disabled", false);
            jQuery('#send_confirmation_code').prop("disabled", false);
            jQuery('#send_confirmation_code i').addClass("fa-envelope");
            jQuery('#send_confirmation_code i').removeClass("fa-spinner fa-spin");

            if(result.success){
                jQuery('#edfali_success').show();
                jQuery('#edfali_success').text(result.data.message);

                jQuery('#confirmation_code_area').show();
                jQuery('#send_confirmation_code_area').hide();

            }else{
                jQuery('#edfali_error').show();
                errorMessage = result.data.error.message;
                jQuery('#edfali_error').text(errorMessage);
            }
        },
        error: function () {

            jQuery('#edfali_mobile_number').prop("disabled", false);
            jQuery('#send_confirmation_code').prop("disabled", false);
            jQuery('#send_confirmation_code i').addClass("fa-envelope");
            jQuery('#send_confirmation_code i').removeClass("fa-spinner fa-spin");

        }
    });
}