<?php
class UserRegistrationForWoocommerceUserManager {

    /**
     * constructor for UserManager class
     */
    public function __construct() {
        
    }

    public function addUser($user_id) {
        require_once plugin_dir_path(__FILE__) . "DatabaseHelper.php";
        require_once plugin_dir_path(__FILE__) . "Statuses.php";
        $databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
        $databaseHelper->addUser($user_id, Status::PENDING);

        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
        $username = $user_info->user_login;

        require_once plugin_dir_path(__FILE__) . "MailManger.php";
        $mailManager = new UserRegistrationForWoocommerceMailManager();
        $mailManager->sendStandardVerificationEmail($email);
    }
}