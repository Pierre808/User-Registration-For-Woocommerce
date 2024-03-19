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
        $this->register_scripts_and_styles();

        //wp footer hook
        add_action('wp_footer', array($this, 'user_registration_for_woocommerce_enqueue_footer_script'));
        
        //custom endpoint
        add_action('rest_api_init', array($this, 'user_registration_for_woocommerce_verification_endpoint'));

        //add to admin menu
        add_action('admin_menu', array($this, 'user_registration_for_woocommerce_admin_menu'));

        $this->register_registration_hooks();

        $this->register_ajax_hooks();
    }

    

    /**
     * ---------------------------
     * -------- CALLBACKS --------
     * ---------------------------
     */

    /**
     * Own endpoint for the verification link
     */
    public function user_registration_for_woocommerce_verification_endpoint() {
        register_rest_route('user-registration/v1', '/verification-link', array(
            'methods' => 'GET',
            'callback' => array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_handle_verification_request'),
        ));
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
        //enqueue scripts and styles here
    }

    /**
     * Callback for footer -> footerscript
     */
    public function user_registration_for_woocommerce_enqueue_footer_script() {
        if(is_account_page()) {
            wp_enqueue_script('user_registration_for_woocommerce_verificationmail_script',  plugin_dir_url(__FILE__) . '../assets/js/verificationmail-script.js');
            wp_localize_script('user_registration_for_woocommerce_verificationmail_script', 'ajaxurl', array( admin_url('admin-ajax.php') ));
        }
    }

    /**
     * Save to option ajax hook callback
     */
    public function user_registration_for_woocommerce_save_to_options() {
        include(plugin_dir_path(__FILE__) . 'ajax/save_to_options.php');
    }

    public function wp_ajax_user_registration_for_woocommerce_resend_verification_email() {
        include(plugin_dir_path(__FILE__) . 'ajax/resend_verification_email.php');
    }



    /**
     * ---------------------------
     * ---- PRIVATE FUNCTIONS ----
     * ---------------------------
     */

    /**
     * Enqueue all scripts and styles
     */
    private function register_scripts_and_styles() {
        //scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'user_registration_for_woocommerce_enqueue_custom_styles_and_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'user_registration_for_woocommerce_enqueue_jquery'));
    }

    /**
     * Register all hooks related to registration (and login)
     */
    private function register_registration_hooks() {
        //registration form hook
        add_action( 'woocommerce_register_form_start', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_extra_register_fields'));

        add_action( 'woocommerce_register_post', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_validate_extra_register_fields'), 10, 3);

        //created customer hook
        add_action('woocommerce_created_customer', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_created_customer_hook'), 10, 1);

        //registration redirect hook (before redirect)
        add_action('woocommerce_registration_redirect', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_custom_registration_redirect'), 10, 1);
        //login redirect hook (before redirect)
        add_action('woocommerce_login_redirect', array($this->userRegistrationForWoocommerceCore, 'user_registration_for_woocommerce_custom_login_redirect'), 10, 2);
    }

    /**
     * Register all hooks for ajax functions
     */
    private function register_ajax_hooks() {
        //save to options
        add_action('wp_ajax_user_registration_for_woocommerce_save_to_options', array($this, 'user_registration_for_woocommerce_save_to_options')); // For logged-in users
        add_action('wp_ajax_nopriv_user_registration_for_woocommerce_save_to_options', array($this, 'user_registration_for_woocommerce_save_to_options')); // For non-logged-in users

        //resend verification mail
        add_action('wp_ajax_user_registration_for_woocommerce_resend_verification_email', array($this, 'wp_ajax_user_registration_for_woocommerce_resend_verification_email')); // For logged-in users
        add_action('wp_ajax_nopriv_user_registration_for_woocommerce_resend_verification_email', array($this, 'wp_ajax_user_registration_for_woocommerce_resend_verification_email')); // For non-logged-in users
    }
}