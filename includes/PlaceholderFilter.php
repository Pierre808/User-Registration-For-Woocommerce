<?php
/**
 * Helper class to replace placeholders in texts.
 * Usually placeholders are defined as [placeholder_name]
 */
class UserRegistrationForWoocommercePlaceholderFilter {

    /**
     * constructor for PlaceholderFilter class
     */
    public function __construct() {
        
    }

    /**
     * Replaces all verificationlink placeholders
     */
    public function verificationlinkPlaceholder($text, $verificationlink) {
        /*$linkHtml = '
            <a href="" target="_blank" 
            data-saferedirecturl="https://www.google.com/url?q=http://localhost/wordpress-local/mein-konto/?alg_wc_ev_verify_email%3DeyJpZCI6MTcsImNvZGUiOiJkZmMzNThlZGJiOWY0OTNiMTIzODIyNjQ5MGRjOWEzZCJ9&amp;source=gmail&amp;ust=1710428283624000&amp;usg=AOvVaw1WkuBJHAT1PL2aCNsfjr_C" 
            jslog="32272; 1:WyIjdGhyZWFkLWY6MTc5MzMxNTE1MzI3NjIyMjg1NyJd; 4:WyIjbXNnLWY6MTc5MzM1Njc5NjY1NjE5ODgyNiJd">
                click here
            </a>
        ';*/
        $linkHtml = '<a href="' . $verificationlink . '" target="_blank">Verifizieren</a>';

        $text = str_replace("[verificationlink]", $linkHtml, $text);
        return $text;
    }
}
