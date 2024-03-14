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
        $linkHtml = '<a href="' . $verificationlink . '" target="_blank">Verifizieren</a>';

        $text = str_replace("[verificationlink]", $linkHtml, $text);
        return $text;
    }
}
