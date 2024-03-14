jQuery(document).ready(function($) {
    console.log("script");
    $("#user-registration-for-woocommerce-resend-verification-mail").hover(function() {
        $(this).css('cursor','pointer');
    }, function() {
        $(this).css('cursor','auto');
    });
});