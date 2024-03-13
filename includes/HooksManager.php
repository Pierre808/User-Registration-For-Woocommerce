<?php

class UserRegistrationForWoocommerceHooksManager {
    private $userRegistrationForWoocommerceCore;

    /**
     * Constructor
     */
    public function __construct($pUserRegistrationForWoocommerceCore) {
        $this->userRegistrationForWoocommerceCore = $pUserRegistrationForWoocommerceCore;
    }

    /**
     * Hooks
     */
    public function addHooks() {
        //scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'user_registration_for_woocommerce_enqueue_custom_styles_and_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'user_registration_for_woocommerce_enqueue_jquery'));

        //created customer hook
        add_action('woocommerce_created_customer', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_user_register_hook'), 10, 1);

        //registration redirect hook (before redirect)
        add_action('woocommerce_registration_redirect', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_custom_registration_redirect'));
        //login redirect hook (before redirect)
        add_action('woocommerce_login_redirect', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_custom_login'));

        //add to admin menu
        add_action('admin_menu', array($this, 'user_registration_for_woocommerce_admin_menu'));

        
        //save to options
        add_action('wp_ajax_user_registration_for_woocommerce_save_to_options', array($this, 'user_registration_for_woocommerce_save_to_options')); // For logged-in users
        add_action('wp_ajax_nopriv_user_registration_for_woocommerce_save_to_options', array($this, 'user_registration_for_woocommerce_save_to_options')); // For non-logged-in users
    }


    /**
     * Admin menu pages callback
     */
    public function user_registration_for_woocommerce_admin_menu()
    {
        add_menu_page(
            'User Registration for WooCommerce', //Pagetitle
            'User Registration for Woo', //Menutitle
            'manage_options', //Capability
            'user-registration-for-woocommerce-admin-page', //Menus Slug
            array($this, 'user_registration_for_woocommerce_admin_page') //Callback Function to generate content
        );
    }

    /**
     * User Registration for WooCommerce page callback
     */
    public function user_registration_for_woocommerce_admin_page() {
        include(plugin_dir_path(__FILE__) . 'pages/index.php');
    }

    /**
     * Jquery script callback
     */
    public function user_registration_for_woocommerce_enqueue_jquery() {
        wp_enqueue_script('jquery');
    }

    /**
     * Custom script and styles callback
     */
    public function user_registration_for_woocommerce_enqueue_custom_styles_and_scripts() {
        //add custom script and styles here
    }


    /**
     * Save to option ajax hook callback
     */
    public function user_registration_for_woocommerce_save_to_options() {
        include(plugin_dir_path(__FILE__) . 'ajax/save_to_options.php');
    }
}