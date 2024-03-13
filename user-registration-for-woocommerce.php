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
 * AJAX HOOKS CALLBACKS
 */
function user_registration_for_woocommerce_save_to_options() {
    include(plugin_dir_path(__FILE__) . 'includes/ajax/save_to_options.php');
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

// add notice after user registration redirect
function user_registration_for_woocommerce_custom_registration_redirect($redirect_to) {
    //wp_destroy_current_session();
    //wp_clear_auth_cookie();
    //wp_set_current_user( 0 );

    wp_logout();
    
    // Only create when there is none, e.g may clear the existing cart item
    if ( ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie(true);
    
    }
    do_action( 'woocommerce_set_cart_cookies',  true );

    // Add a notice for the user
    //wc_add_notice('Thank you for your registration. Your account has to be activated before you can login. Please check your email.', 'notice');
    wc_add_notice('Vielen Dank für Ihre Registrierung. Ihr Konto muss aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen Sie Ihre E-Mail.', 'notice');

    
    // Redirect to the account page
    return wc_get_page_permalink('myaccount');
}

// add notice after user registration redirect
function user_registration_for_woocommerce_custom_login($redirect) {
    wp_logout();
    
    // Only create when there is none, e.g may clear the existing cart item
    if ( ! WC()->session->has_session() ) {
        WC()->session->set_customer_session_cookie(true);
    
    }
    do_action( 'woocommerce_set_cart_cookies',  true );

    wc_add_notice('Ihr Konto muss noch aktiviert werden, bevor Sie sich anmelden können. Bitte überprüfen sie Ihre E-Mail', 'error');

    /*
    // Check if the user is accessing the account page and if they are logged in
    if (is_account_page() && is_user_logged_in()) {
        // Force log out the user
        wp_logout();
        
        // Redirect the user to the homepage or any other page as per your requirement
        $redirect = home_url();
    }
    */

    return $redirect;
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

//created customer hook
add_action('woocommerce_created_customer', 'user_registration_for_woocommerce_user_register_hook', 10, 1);

//registration redirect hook (before redirect)
add_action('woocommerce_registration_redirect', 'user_registration_for_woocommerce_custom_registration_redirect');
//login redirect hook (before redirect)
add_action('woocommerce_login_redirect', 'user_registration_for_woocommerce_custom_login');
//add_filter('authenticate', 'user_registration_for_woocommerce_custom_login', 10, 3);

//add to admin menu
add_action('admin_menu', 'user_registration_for_woocommerce_admin_menu');


/**
 * ---- AJAX HOOKS ----
 */
 //save to options
 add_action('wp_ajax_user_registration_for_woocommerce_save_to_options', 'user_registration_for_woocommerce_save_to_options'); // For logged-in users
 add_action('wp_ajax_nopriv_user_registration_for_woocommerce_save_to_options', 'user_registration_for_woocommerce_save_to_options'); // For non-logged-in users