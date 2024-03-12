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
    require_once plugin_dir_path(__FILE__) . "includes/DatabaseHelper.php";
    $databaseHelper = new UserRegistrationForWoocommerceDatabaseHelper();
    $databaseHelper->init();
}


/**
 * ---- SCRIPS AND STYLES
 */
//jquery
function user_registration_for_woocommerce_enqueue_jquery() {
    wp_enqueue_script('jquery');
}

//custom script and styles
function user_registration_for_woocommerce_enqueue_custom_styles_and_scripts() {
    //add custom script and styles here
}


/**
 * HOOKS CALLBACKS
 */
function user_registration_for_woocommerce_user_register_hook($user_id) {
    require_once plugin_dir_path(__FILE__) . "includes/UserManager.php";
    $userManager = new UserRegistrationForWoocommerceUserManager();
    $userManager->addUser($user_id);
}


/**
 * ---- HOOKS ----
 */
//init
add_action('init', 'user_registration_for_woocommerce_init');

//scripts and styles
add_action('admin_enqueue_scripts', 'user_registration_for_woocommerce_enqueue_custom_styles_and_scripts');
add_action('admin_enqueue_scripts', 'user_registration_for_woocommerce_enqueue_jquery');

//add_action('user_register', 'user_registration_for_woocommerce_user_register_hook', 10, 1);
add_action('woocommerce_created_customer', 'user_registration_for_woocommerce_user_register_hook', 10, 1);