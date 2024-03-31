<?php

require_once plugin_dir_path(__FILE__) . "DatabaseHelper.php";
require_once plugin_dir_path(__FILE__) . "MailManager.php";
require_once plugin_dir_path(__FILE__) . "UserManager.php";
require_once plugin_dir_path(__FILE__) . "VerificationCodeManager.php";

class UserRegistrationForWoocommerceCore {
    public $databaseHelper;
    public $mailManager;
    public $userManager;
    public $verificationCodeManager;

    /**
     * constructor
     */
    public function __construct() {

        $this->databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
        $this->mailManager = new UserRegistrationForWoocommerceMailManager($this);
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
     * Add fields to the woocommerce registration
     */
    public function user_registration_for_woocommerce_extra_register_fields() {
        ?>
            <p class="form-row form-row-first">
                <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
                <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
            </p>

            <p class="form-row form-row-last">
                <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
                <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
            </p>

            <div class="clear"></div>
        <?php
    }

    /**
     * Validate the fields added to the woocommerce registration
     */
    function user_registration_for_woocommerce_validate_extra_register_fields($username, $email, $validation_errors) {
        if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
               $validation_errors->add( 'billing_first_name_error', __( 'Bitte gib Deinen Vornamen an.', 'woocommerce' ) );
        }
        if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {
               $validation_errors->add( 'billing_last_name_error', __( 'Bitte gib Deinen Nachnamen an.', 'woocommerce' ) );
        }
           
        return $validation_errors;
    }

    /**
     * Woocommerce_created_customer action. before: user register
     */
    public function user_registration_for_woocommerce_created_customer_hook($user_id) {
        //add user to database
        $this->userManager->addUser($user_id);


        //save additional registration fields
        if ( isset( $_POST['billing_first_name'] ) ) {
                //First name field which is by default
                update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
                // First name field which is used in WooCommerce
                update_user_meta( $user_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
        }
        if ( isset( $_POST['billing_last_name'] ) ) {
                // Last name field which is by default
                update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
                // Last name field which is used in WooCommerce
                update_user_meta( $user_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
        }


        //send mail
        $mailSendingResult = $this->mailManager->sendStandardVerificationEmail($user_id);
    
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
                ['notice' =>'Vielen Dank für Deine Registrierung. Dein Konto muss aktiviert werden, bevor Du dich anmelden kannst. Bitte überprüfe Deine E-Mail.', 
                'type' => 'notice']
            ));
        } 
        
        return $redirect;
    }

    /**
     * Add notice after user login redirect
     */
    public function user_registration_for_woocommerce_custom_login_redirect($redirect, $user = '') {
        if( isset($user) && is_a( $user, 'WP_User' ) && $user->ID > 0 ) {
            $userStatus = $this->databaseHelper->getUserStatus($user->ID);

            if($userStatus == null) return; //user not found

            require_once plugin_dir_path(__FILE__) . "Statuses.php";
            if($userStatus[0]->verification_status != Status::PENDING) return $redirect; //status not pending
               
            //block user from logging in
            return $this->userManager->logout_and_redirect($redirect, array(
                ['notice' =>'Dein Konto muss noch aktiviert werden, bevor Du dich anmelden kannst. Bitte überprüfe Deine E-Mail. <a data-id="'. $user->ID .'" id="user-registration-for-woocommerce-resend-verification-mail"> Erneut senden</a>', 
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
            ['notice' =>'Erfolgreich verifiziert. Du kannst dich nun anmelden.', 
            'type' => 'notice']
        ));
        
        wp_redirect($redirect);
        exit();
    }
}