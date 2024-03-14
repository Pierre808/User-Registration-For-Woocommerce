<?php
class UserRegistrationForWoocommerceVerificationCodeManager {

    /**
     * expiration time in s.
     */
    private $defaultExpirationTime = 30 * 60; //30min

    /**
     * constructor for VerificationCodeManager class
     */
    public function __construct() {
        
    }

    /**
     * Generates a verification code
     * 
     * @return  string  the generated verification code
     */
    public function generateCode() {
        // Generate a random verification code
        $verificationCode = bin2hex(random_bytes(32)); // Generates a 32-character random string
        $prefix = '';
        if(get_option('user_registration_for_woocommerce_verification_mail_link_prefix') != null) {
            $prefix = get_option('user_registration_for_woocommerce_verification_mail_link_prefix');
        }
        $finalCode = $prefix . $verificationCode;

        return $finalCode;
    }

    public function getCodeExpiration($expires = '') {
        if($expires == '') {
            // Get current datetime
            $wp_current_datetime = current_time('Y-m-d H:i:s');
            $wp_current_datetime = date( 'Y-m-d H:i:s', strtotime( $wp_current_datetime ) + $this->defaultExpirationTime ); 

            $expires = $wp_current_datetime;
        }

        return $expires;
    }
}