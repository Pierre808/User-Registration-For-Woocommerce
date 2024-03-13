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
 * ADMIN PAGE CALLBACKS
 */
//index page
function user_registration_for_woocommerce_admin_page() {
    include(plugin_dir_path(__FILE__) . 'includes/pages/index.php');
}


/**
 * HOOKS CALLBACKS
 */
//woocommerce_created_customer action. before: user register
 function user_registration_for_woocommerce_user_register_hook($user_id) {
    require_once plugin_dir_path(__FILE__) . "includes/UserManager.php";
    $userManager = new UserRegistrationForWoocommerceUserManager();
    $userManager->addUser($user_id);
}

// admin menu pages
function user_registration_for_woocommerce_admin_menu()
{
    add_menu_page(
        'User Registration for WooCommerce', //Pagetitle
        'User Registration for Woo', //Menutitle
        'manage_options', //Capability
        'user-registration-for-woocommerce-admin-page', //Menus Slug
        'user_registration_for_woocommerce_admin_page' //Callback Function to generate content
    );
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

//add to admin menu
add_action('admin_menu', 'user_registration_for_woocommerce_admin_menu');