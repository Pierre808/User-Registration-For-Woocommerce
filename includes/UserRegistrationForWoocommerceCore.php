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
        wp_logout();
        
        // Only create when there is none, e.g may clear the existing cart item
        if ( ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie(true);
        
        }
        do_action( 'woocommerce_set_cart_cookies',  true );

        // Add a notice for the user
        //wc_add_notice('Thank you for your registration. Your account has to be activated before you can login. Please check your email.', 'notice');
        wc_add_notice('Vielen Dank für Ihre Registrierung. Ihr Konto muss aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen Sie Ihre E-Mail.', 'notice');

        
        // Redirect to the account page
        return wc_get_page_permalink('myaccount');
    }

    /**
     * Add notice after user registration redirect
     */
    public function user_registration_for_woocommerce_custom_login($redirect) {
        return $this->userManager->logout_and_redirect($redirect);
    }
}