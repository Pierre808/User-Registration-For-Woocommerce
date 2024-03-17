<?php

class UserRegistrationForWoocommerceMailManager{
    private $core;

    public function __construct($pCore) {
        $this->core = $pCore;
    }
    
    /**
     * Sends an email
     * 
     * @param   $email      The email address there the mail should be send to
     * @param   $subject    The subject 
     * @param   $message    The mails message content
     * 
     * @return  string|bool string with error text, when email is not valid
     *                      bool whether the email was send successfully, if the validation succeeded
     */
    public function sendEmail($email, $subject, $message) {
        // Validate email and username
        if (!is_email($email)) {
            // If email is not valid, return or handle the error
            return "Invalid email!";
        }

        return wp_mail($email, $subject, $message);
    }

    /**
     * Sends the standard verification email
     * 
     * @param   $email  The email address there the mail should be send to
     * 
     * @return  string|bool string with error text, when email is not valid
     *                      bool whether the email was send successfully, if the validation succeeded
     */
    public function sendStandardVerificationEmail($user_id = '', $verificationCode = '') {
        $user_info = '';
        if($user_id == '') {
            $user_info = wp_get_current_user();
        }
        else {
            $user_info = get_userdata($user_id);
        }
        $email = $user_info->user_email;

        if($verificationCode == '') {
            $verificationCode = $this->core->verificationCodeManager->generateCode();
            $verificationCodeExpires = $this->core->verificationCodeManager->getCodeExpiration();
            
            $verificationCodeResult = $this->core->databaseHelper->addVerificationCode($verificationCode, $user_id, $verificationCodeExpires);
        }

        $subject = 'Bitte verifizieren Sie Ihre E-Mail-Adresse';
        //$message = 'Please click on the following link to verify your email address: [Verification Link]';
        
        $verificationLinkUrl = home_url() . "/wp-json/user-registration/v1/verification-link";

        // Add parameter to URL
        if (strpos($verificationLinkUrl, '?') !== false) {
            // URL already has parameters
            $verificationLinkUrl .= "&user_registration_code=$verificationCode";
        } else {
            // URL does not have parameters
            $verificationLinkUrl .= "?user_registration_code=$verificationCode";
        }
        
        require_once plugin_dir_path(__FILE__) . "PlaceholderFilter.php";
        $placeholderFilter = new UserRegistrationForWoocommercePlaceholderFilter();
        $messageContent = $placeholderFilter->verificationlinkPlaceholder(
            get_option('user_registration_for_woocommerce_verification_mail_content_value'), 
            $verificationLinkUrl
        );

        $messageContent = $this->stripslashes($messageContent);

        $message = '
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                /* Your CSS styles here */
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                }
                .container {
                    width: 80%;
                    margin: 0 auto;
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                }
                h1 {
                    color: #333;
                }
                p {
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                '
                . $messageContent . 
                '
            </div>
        </body>
        </html>
        ';

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        $r = $this->sendEmail($email, $subject, $message);

        // Reset content type to default
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        return $r;
    }

    public function set_html_content_type() {
        return 'text/html';
    }

    /**
    * Stripslashed helper
    * @param string $str
    * @return string cleaned string
    */
    private function stripslashes($str) {
       return preg_replace_callback('/\\\\(.?)/', function ($matches) {
           switch ($matches[1]) {
               case '\\':
                   return '\\';
               case '0':
                   return '\u0000';
               case '':
                   return '';
               default:
                   return $matches[1];
           }
       }, $str);
   }
}