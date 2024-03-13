<?php

require_once plugin_dir_path(__FILE__) . "DatabaseHelper.php";
require_once plugin_dir_path(__FILE__) . "MailManager.php";
require_once plugin_dir_path(__FILE__) . "UserManager.php";

class UserRegistrationForWoocommerceCore {
    private $databaseHelper;
    private $mailManager;
    private $userManager;

    /**
     * constructor
     */
    public function __construct() {

        $this->databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
        $this->mailManager = new UserRegistrationForWoocommerceMailManager();
        $this->userManager = new UserRegistrationForWoocommerceUserManager();

        //hooks
        require_once plugin_dir_path(__FILE__) . "HooksManager.php";
        $hooksManager = new UserRegistrationForWoocommerceHooksManager($this);
        $hooksManager->addHooks();
    }

    /**
     * Plugin init code
     */
    public function init() {
        $this->databaseHelper->init();
    }


    /**
     * Woocommerce_created_customer action. before: user register
     */
    public function user_registration_for_woocommerce_user_register_hook($user_id) {
        $this->userManager->addUser($user_id);
    }

    /**
     * Add notice after user registration redirect
     */
    public function user_registration_for_woocommerce_custom_registration_redirect($redirect_to) {
        return $this->userManager->logout_and_redirect($redirect_to, array(
            ['notice' =>'Vielen Dank für Ihre Registrierung. Ihr Konto muss aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen Sie Ihre E-Mail.', 
            'type' => 'notice']
        ));
    }

    /**
     * Add notice after user login redirect
     */
    public function user_registration_for_woocommerce_custom_login($redirect, $user = '') {
        //TODO: resend mail link

        if( isset($user) && is_a( $user, 'WP_User' ) && $user->ID > 0 ) {
            $userStatus = $this->databaseHelper->getUserStatus($user->ID);

            if($userStatus == null) return; //user not found

            require_once plugin_dir_path(__FILE__) . "Statuses.php";
            if($userStatus[0]->verification_status != Status::PENDING) return $redirect; //status not pending
               
            //block user from logging in
            return $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Ihr Konto muss noch aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen sie Ihre E-Mail.', 
                'type' => 'error']
            ));
        } else {
            // user error
        }

        
    }
}