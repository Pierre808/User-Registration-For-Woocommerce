jQuery(document).ready(function($) {
    /**
     * On verificationmail link prefix change
     */
    $('#user_registration_for_woocommerce_verification_link_prefix').on('input', function() {
        console.log("updatin prefix");
        updateVerificationLinkPrefix(); //TODO: max length: 16 chars <-- VERCHAR(80) - 64
    });


    /**
     * On verification mail content change
     */
    tinyMCE.get('user_registration_for_woocommerce_verification_mail_content').on('change', function() {
        tinyMCE.triggerSave();

        console.log("autosaving verification mail content...");
        saveContent();
    });


    function saveContent() {
        return $.ajax({
            type: 'POST',
            url: ajaxurl[0], // The wp ajax url
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

    function updateVerificationLinkPrefix() {
        return $.ajax({
            type: 'POST',
            url: ajaxurl[0], // The wp ajax url
            data: {
                action: 'user_registration_for_woocommerce_save_to_options',
                values : {
                    0 : {
                        name: "user_registration_for_woocommerce_verification_mail_link_prefix",
                        data: $('#user_registration_for_woocommerce_verification_link_prefix').val()
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