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

    public function logout_and_redirect($redirect) {
        $this->logout();
        
        // Only create when there is none, e.g may clear the existing cart item
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie(true);
        
        }
        do_action( 'woocommerce_set_cart_cookies',  true );

        wc_add_notice('Ihr Konto muss noch aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen sie Ihre E-Mail', 'error');

        return $redirect;
    }

    public function logout() {
        //TODO: only logout if not verified!
        wp_logout();
    }
}