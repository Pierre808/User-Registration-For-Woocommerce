<?php

/*
Plugin Name: User Registration for Woocommerce
Description: Plugin to handle everything about user registration.
Version: 1.0
Author: Pierre Equit
*/


/**
 * ---- INIT ----
 */
//plugin init code
function user_registration_for_woocommerce_init()
{
    require_once plugin_dir_path(__FILE__) . "includes/UserRegistrationForWoocommerceCore.php";
    $core = new UserRegistrationForWoocommerceCore();
    $core->init();
}

//init
add_action('init', 'user_registration_for_woocommerce_init');