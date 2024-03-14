<?php

header('Content-type: application/json');

try{    
    $user_id = $_POST['idVal'];

    update_option('urfw-ui', $user_id);

    require_once plugin_dir_path(__FILE__) . "../MailManager.php";
    require_once plugin_dir_path(__FILE__) . "../UserRegistrationForWoocommerceCore.php";
    $core = new UserRegistrationForWoocommerceCore();
    $mailManager = new UserRegistrationForWoocommerceMailManager($core);

    $mailSendingResult = $mailManager->sendStandardVerificationEmail($user_id);
    
    //mail error handling
    if($mailSendingResult !== true) {
        if(is_bool($mailSendingResult) && $mailSendingResult === false) {
            wp_send_json(['message' => "error: An error occured while sending the mail"]);
        }
        if(is_string($mailSendingResult)) {
            wp_send_json(['message' => "error: {$mailSendingResult}"]);
        }
        else {
            wp_send_json(['message' => "error: An unknown error occured while sending the mail"]);
        }
    }

    wp_send_json(['message' => "success!"]);
}
catch (Exception $e){
    wp_send_json(['message' => "error: " . $e->getMessage()]);
}

wp_die();