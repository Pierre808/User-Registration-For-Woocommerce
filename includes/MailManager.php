<?php

class UserRegistrationForWoocommerceMailManager{
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

        wp_mail($email, $subject, $message);
    }

    /**
     * Sends the standard verification email
     * 
     * @param   $email  The email address there the mail should be send to
     * 
     * @return  string|bool string with error text, when email is not valid
     *                      bool whether the email was send successfully, if the validation succeeded
     */
    public function sendStandardVerificationEmail($email) {
        $subject = 'Bitte verifizieren Sie Ihre E-Mail-Adresse';
        //$message = 'Please click on the following link to verify your email address: [Verification Link]';
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
                . get_option('user_registration_for_woocommerce_verification_mail_content_value') . 
                '
            </div>
        </body>
        </html>
        ';

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));

        $this->sendEmail($email, $subject, $message);

        // Reset content type to default
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
    }

    public function set_html_content_type() {
        return 'text/html';
    }
}