<?php
class UserRegistrationForWoocommerceUserManager {

    /**
     * constructor for UserManager class
     */
    public function __construct() {
        
    }

    /**
     * Contains all the logic to add an user
     * 
     * @param   $user_id    The ID of the user that should be added
     */
    public function addUser($user_id) {
        require_once plugin_dir_path(__FILE__) . "DatabaseHelper.php";
        require_once plugin_dir_path(__FILE__) . "Statuses.php";
        $databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
        $dbAddUserResult = $databaseHelper->addUser($user_id, Status::PENDING);

        //database error handling
        if(!is_int($dbAddUserResult)) {
            if(is_bool($dbAddUserResult) && $dbAddUserResult === false) {
                return "An error occured while saving the user information to the database!";
            }
            if(is_string($dbAddUserResult)) {
                return $dbAddUserResult;
            }
            else {
                return "An unknown error occured while adding the user to the Database";
            }
        }

        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
        $username = $user_info->user_login;

        require_once plugin_dir_path(__FILE__) . "MailManager.php";
        $mailManager = new UserRegistrationForWoocommerceMailManager();
        $mailSendingResult = $mailManager->sendStandardVerificationEmail($email);
    
        //mail error handling
        if($mailSendingResult !== true) {
            if(is_bool($mailSendingResult) && $mailSendingResult === false) {
                return "An error occured while sending the mail";
            }
            if(is_string($mailSendingResult)) {
                return $mailSendingResult;
            }
            else {
                return "An unknown error occured while sending the mail";
            }
        }
    }
}