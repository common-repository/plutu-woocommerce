/**
 * Initialization
 */
jQuery(document).ready(function(){
    sadad_container();
    jQuery('body').on( 'updated_checkout', function() {
        sadad_container();
    });
});
/**
 * Sadad container
 */
function sadad_container(){
    jQuery("#send_otp_code").click(sendSadadOTPCode);
    jQuery('#sadad_mobile_number').keypress(function(e){
        var keyCode = e.keyCode || e.which;
        if (keyCode == 13){
            e.preventDefault();
            sendSadadOTPCode();
            return false;
        }
    });
    jQuery('#sadad_birth_year').keypress(function(e){
        var keyCode = e.keyCode || e.which;
        if (keyCode == 13){
            e.preventDefault();
            sendSadadOTPCode();
            return false;
        }
    });
    jQuery("#resend_otp_code").click(function(e){
        e.preventDefault();
        jQuery('#otp_code_area').hide();
        jQuery('#sadad_success').hide();
        jQuery('#sadad_error').hide();
        jQuery('#send_otp_code_area').fadeIn();
    });
}
/**
 * API Request
 */
function sendSadadOTPCode() {

    jQuery('#sadad_mobile_number').prop("disabled", true);
    jQuery('#sadad_birth_year').prop("disabled", true);
    jQuery('#send_otp_code').prop("disabled", true);
    jQuery('#send_otp_code i').removeClass("fa-envelope");
    jQuery('#send_otp_code i').addClass("fa-spinner fa-spin");

    jQuery('#sadad_success').hide();
    jQuery('#sadad_error').hide();

    jQuery.ajax({
        type: 'POST',
        dataType: 'json',
        url: sadad_vars.url,
        data: {
            'order_id': _sadad_order_id,
            'amount': _sadad_amount,
            'mobile_number':jQuery('#sadad_mobile_number').val(),
            'birth_year':jQuery('#sadad_birth_year').val(),
            'nonce': sadad_vars.nonce,
            'action': 'sadad_verify' 
        }, success: function (result) {
            jQuery('#sadad_mobile_number').prop("disabled", false);
            jQuery('#sadad_birth_year').prop("disabled", false);
            jQuery('#send_otp_code').prop("disabled", false);
            jQuery('#send_otp_code i').addClass("fa-envelope");
            jQuery('#send_otp_code i').removeClass("fa-spinner fa-spin");

            if(result.success){
                jQuery('#sadad_success').show();
                jQuery('#sadad_success').text(result.data.message);

                jQuery('#otp_code_area').show();
                jQuery('#send_otp_code_area').hide();

            }else{
                jQuery('#sadad_error').show();
                errorMessage = result.data.error.message;
                jQuery('#sadad_error').text(errorMessage);
            }
        },
        error: function () {

            jQuery('#sadad_mobile_number').prop("disabled", false);
            jQuery('#sadad_birth_year').prop("disabled", false);
            jQuery('#send_otp_code').prop("disabled", false);
            jQuery('#send_otp_code i').addClass("fa-envelope");
            jQuery('#send_otp_code i').removeClass("fa-spinner fa-spin");

        }
    });

} 