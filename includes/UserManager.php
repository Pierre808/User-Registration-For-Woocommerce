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
    }

    /**
     * Logs a user out and redirects him to the next page
     * 
     * @param   $redirect  
     * @param   $notices    array of notices consisting of 'notice' and 'type' key, to be displayed as wc notice
     */
    public function logout_and_redirect($redirect, $notices) {
        $this->logout();
        

        if (!isset(WC()->session)) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        // Only create when there is none, e.g may clear the existing cart item
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie(true);
        
        }
        do_action( 'woocommerce_set_cart_cookies',  true );

        for($i = 0; $i < count($notices); $i++) {
            wc_add_notice($notices[$i]['notice'], $notices[$i]['type']);
        }

        return $redirect;
    }

    public function logout() {
        wp_logout();
    }
}