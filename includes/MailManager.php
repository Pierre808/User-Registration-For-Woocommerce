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
        $subject = 'Please verify your email address';
        $message = 'Please click on the following link to verify your email address: [Verification Link]';

        sendEmail($email, $subject, $message);
    }
}