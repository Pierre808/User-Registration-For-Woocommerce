jQuery(document).ready(function($) {
    console.log("script");
    $("#user-registration-for-woocommerce-resend-verification-mail").hover(function() {
        $(this).css('cursor','pointer');
    }, function() {
        $(this).css('cursor','auto');
    });

    $("#user-registration-for-woocommerce-resend-verification-mail").on("click", function() {
        var id = $(this).data('id');
        resendEmail(id);
    });


    function resendEmail(id) {
        return $.ajax({
            type: 'POST',
            url: ajaxurl[0], // The wp ajax url
            data: {
                action: 'user_registration_for_woocommerce_resend_verification_email',
                idVal: id
            },
            success: function(response) {
                console.log("Success:", response);
            },
            error: function (error) {
                console.log("Error:", error);
            }
        });
    }
});