<?php

require_once plugin_dir_path(__FILE__) . "DatabaseHelper.php";
require_once plugin_dir_path(__FILE__) . "MailManager.php";
require_once plugin_dir_path(__FILE__) . "UserManager.php";
require_once plugin_dir_path(__FILE__) . "VerificationCodeManager.php";

class UserRegistrationForWoocommerceCore {
    private $databaseHelper;
    private $mailManager;
    private $userManager;
    private $verificationCodeManager;

    /**
     * constructor
     */
    public function __construct() {

        $this->databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
        $this->mailManager = new UserRegistrationForWoocommerceMailManager();
        $this->userManager = new UserRegistrationForWoocommerceUserManager();
        $this->verificationCodeManager = new UserRegistrationForWoocommerceVerificationCodeManager();

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
        
        //block user from logging in
        $verificationCode = $this->verificationCodeManager->generateCode();
        $verificationCodeExpires = $this->verificationCodeManager->getCodeExpiration();
        
        $verificationCodeResult = $this->databaseHelper->addVerificationCode($verificationCode, $user_id, $verificationCodeExpires);

        if(null != $verificationCodeResult && !is_int($verificationCodeResult)) {
            //error
        }

        $user_info = get_userdata($user_id);
        $email = $user_info->user_email;
        $username = $user_info->user_login;

        $mailSendingResult = $this->mailManager->sendStandardVerificationEmail($email, $verificationCode);
    
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

    /**
     * Add notice after user registration redirect
     */
    public function user_registration_for_woocommerce_custom_registration_redirect($redirect_to){
        $user = wp_get_current_user();
        if( isset($user) && is_a( $user, 'WP_User' ) && $user->ID > 0 ) {
            return $this->userManager->logout_and_redirect($redirect_to, array(
                ['notice' =>'Vielen Dank für Ihre Registrierung. Ihr Konto muss aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen Sie Ihre E-Mail.', 
                'type' => 'notice']
            ));
        } 
        
        return $redirect;
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
        } 
        
        return $redirect;
    }

    /**
     * Hanlde verification GET request
     * 
     * @param   $data   GET data (should consist of 'user_registration_code')
     */
    public function user_registration_for_woocommerce_handle_verification_request($data) {
        $redirect = get_permalink( wc_get_page_id( 'myaccount' ) );

        if(!$data->get_params() || !array_key_exists('user_registration_code', $data->get_params()))
        {
            $redirect = $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Registration code missing!', 
                'type' => 'error']
            ));

            update_option('urfw-redirect', 1);

            wp_redirect($redirect);

            exit();
        }

        $verificationCode = $data['user_registration_code'];

        $verificationCodeDb = $this->databaseHelper->getVerificationCode($verificationCode);
        if($verificationCodeDb == null) {
            $redirect = $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Invalid registration code!', 
                'type' => 'error']
            ));

            wp_redirect($redirect);

            exit();
        }

        $verificationCodeExpires =  date( 'Y-m-d H:i:s', strtotime( $verificationCodeDb->expires ) ); 
        $currentDateTime =  date( 'Y-m-d H:i:s', strtotime( current_time('Y-m-d H:i:s') ) );
        if($verificationCodeExpires < $currentDateTime) {
            $redirect = $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Registration code expired!', 
                'type' => 'error']
            ));

            wp_redirect($redirect);

            exit();
        }

        $user = $this->databaseHelper->getUser($verificationCodeDb->user_id);
        if($user == null) {
            $redirect = $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Invalid user!', 
                'type' => 'error']
            ));
            
            wp_redirect($redirect);

            exit();
        }

        require_once plugin_dir_path(__FILE__) . "Statuses.php";
        $this->databaseHelper->setUserStatus($user->ID, Status::APPROVED);

        $redirect = $this->userManager->logout_and_redirect($redirect, array(
            ['notice' =>'Erfolgreich verifiziert. Sie können Sich nun anmelden.', 
            'type' => 'notice']
        ));
        
        wp_redirect($redirect);
        exit();
    }
}