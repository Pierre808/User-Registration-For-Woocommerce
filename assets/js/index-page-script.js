jQuery(document).ready(function($) {
    tinyMCE.get('user_registration_for_woocommerce_verification_mail_content').on('change', function() {
        tinyMCE.triggerSave();

        console.log("autosaving verification mail content...");
        saveContent();
    });

    function saveContent() {
        console.log("val: " , tinyMCE.get('user_registration_for_woocommerce_verification_mail_content').getContent());
        return $.ajax({
            type: 'POST',
            url: ajaxurl, // The wp ajax url
            data: {
                action: 'user_registration_for_woocommerce_save_to_options',
                values : {
                    0 : {
                        name: "user_registration_for_woocommerce_verification_mail_content_value",
                        data: tinyMCE.get('user_registration_for_woocommerce_verification_mail_content').getContent()
                    }
                }
            },
            success: function(response) {
                console.log("Saving success:", response);
            },
            error: function (error) {
                console.log("Error:", error);
            }
        });
    }
});