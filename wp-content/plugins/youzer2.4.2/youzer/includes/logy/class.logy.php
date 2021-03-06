<?php

class Logy {

    public function __construct() {

        // Load Functions.
        add_action( 'init', array( $this, 'init' ) );

        // Hide Dashboard
        add_action( 'after_setup_theme', array( $this, 'hide_dashboard' ) );

    }

    /**
     * Hide Dashboard Admin Bar For Non Admins.
     */
    function hide_dashboard() {

        if ( is_super_admin() ) {
            return;
        }
        
        if ( is_multisite() ) {

            global $blog_id;

            if ( ! current_user_can_for_blog( $blog_id, 'subscriber' ) ) {
                return;
            }

        } else {

            if ( ! current_user_can( 'subscriber' ) ) {
                return;
            }

        }
        
        if ( 'on' != yz_option( 'logy_hide_subscribers_dash', 'off' ) ) {
            return;
        }

        // ECHO 'allla';

        // Hide Admin Bar.
        if ( ! is_admin() ) {
            show_admin_bar( false );
        }

        // Hide Admin Dashboard.
        if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            wp_redirect( home_url() );
            exit;
        }

    }

    /**
     * # Init Logy Files
     */
    function init() {

        // Global Functions.
        require LOGY_CORE . 'general/logy-general-functions.php';

        if ( ! is_user_logged_in() ) {

            include LOGY_CORE . 'functions/logy-general-functions.php';
            include LOGY_CORE . 'functions/logy-social-functions.php';
        
            // General Functions
            include LOGY_CORE . 'functions/logy-admin-functions.php';
            include LOGY_CORE . 'functions/logy-bp-functions.php';

            // Classes
            include LOGY_CORE . 'class-logy-form.php';
            include LOGY_CORE . 'class-logy-query.php';
            include LOGY_CORE . 'class-logy-social.php';
            include LOGY_CORE . 'class-logy-rewrite.php';
            include LOGY_CORE . 'class-logy-styling.php';
            include LOGY_CORE . 'class-logy-widgets.php';
            include LOGY_CORE . 'class-logy-limit.php';


            // Include Main Pages
            include LOGY_CORE . 'pages/logy-login.php';
            include LOGY_CORE . 'pages/logy-register.php';
            include LOGY_CORE . 'pages/logy-lost-password.php';
            include LOGY_CORE . 'pages/logy-complete-registration.php';

            // Init Classes
            $this->login          = new Logy_Login();
            $this->form           = new Logy_Form();
            $this->social         = new Logy_Social();
            $this->limit          = new Logy_Limit();
            $this->styling        = new Logy_Styling();
            $this->register       = new Logy_Register();
            
        }

        // Global Files.
        require_once LOGY_CORE . 'class-logy-widgets.php';
        
    }
}