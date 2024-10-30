<?php

namespace WP_Hut_Google_Business_Reviews\Includes;

use  WP_Hut_Google_Business_Reviews\Includes\Admin\Wpgbr_Admin_Feedback ;
use  WP_Hut_Google_Business_Reviews\Includes\Admin\Wpgbr_Admin_Menu ;
use  WP_Hut_Google_Business_Reviews\Includes\Admin\Wpgbr_Admin_Settings ;
use  WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Activator ;
use  WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Db ;
use  WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Google_Connect ;
use  WP_Hut_Google_Business_Reviews\Includes\Frontend\Wpgbr_Frontend ;
class Wpgbr_Google_Business_Reviews
{
    protected  $name ;
    protected  $version ;
    public function __construct()
    {
        $this->name = 'wp-google-business-reviews';
        $this->version = WPGBR_VERSION;
    }
    
    public function init()
    {
        global  $wgbr_fs ;
        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts_styles' ) );
    }
    
    public function init_plugin()
    {
        global  $wgbr_fs ;
        $this->load_languages();
        // Core.
        $db = new Wpgbr_Db();
        $activator = new Wpgbr_Activator( $db );
        $activator->init();
        // frontend.
        $frontend_wpgbr = new Wpgbr_Frontend();
        $frontend_wpgbr->init();
        
        if ( is_admin() ) {
            $admin_menu = new Wpgbr_Admin_Menu();
            $admin_menu->init();
            $admin_settings = new Wpgbr_Admin_Settings();
            $admin_settings->init();
            $admin_feedback = new Wpgbr_Admin_Feedback();
            $admin_feedback->init();
            $gconnect = new Wpgbr_Google_Connect();
            $gconnect->init();
        }
    
    }
    
    public function load_languages()
    {
        load_plugin_textdomain( 'wp-google-business-reviews', false, basename( dirname( WPGBR_PLUGIN_FILE ) ) . '/languages' );
    }
    
    public function load_admin_scripts_styles( $hook )
    {
        if ( 'gb-reviews_page_wpgbr-settings' !== $hook && 'toplevel_page_wpgbr' !== $hook ) {
            return;
        }
        wp_enqueue_style(
            'wpgbr_admin_main_css',
            WPGBR_PLUGIN_URL . '/asset/admin/css/wpgbr-admin-main-style.css',
            false,
            '1.0.0'
        );
        wp_enqueue_script(
            'wpgbr_admin_main_js',
            WPGBR_PLUGIN_URL . '/asset/admin/js/wpgbr-admin-main.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
        wp_localize_script( 'wpgbr_admin_main_js', 'wpgbr_ajax_object', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'site_url' => site_url(),
        ) );
    }

}
