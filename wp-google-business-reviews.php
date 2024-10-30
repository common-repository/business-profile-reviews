<?php

/*
Plugin Name: Business Profile Reviews
Plugin URI: https://wp-hut.com/google-business-reviews-plugin-for-wordpress
Description: Display your Google business profile reviews to your site
Version: 1.0.2
Author: WP-HUT <support@wp-hut.com>
Author URI: https://wp-hut.com
Text Domain: wp-google-business-reviews
Domain Path: /languages
*/
namespace WP_Hut_Google_Business_Reviews;

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wgbr_fs' ) ) {
    wgbr_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'wgbr_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wgbr_fs()
        {
            global  $wgbr_fs ;
            
            if ( !isset( $wgbr_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wgbr_fs = fs_dynamic_init( array(
                    'id'              => '10865',
                    'slug'            => 'wp-google-business-reviews',
                    'type'            => 'plugin',
                    'public_key'      => 'pk_09a1bbcf5fc951db640d0aacb6f6c',
                    'is_premium'      => false,
                    'has_addons'      => false,
                    'has_paid_plans'  => true,
                    'trial'           => array(
                    'days'               => 14,
                    'is_require_payment' => false,
                ),
                    'has_affiliation' => 'selected',
                    'menu'            => array(
                    'slug'    => 'wpgbr',
                    'support' => false,
                ),
                    'is_live'         => true,
                ) );
            }
            
            return $wgbr_fs;
        }
        
        // Init Freemius.
        wgbr_fs();
        // Signal that SDK was initiated.
        do_action( 'wgbr_fs_loaded' );
    }

}

function wgbr_fs_custom_connect_message_on_update(
    $message,
    $user_first_name,
    $plugin_title,
    $user_login,
    $site_link,
    $freemius_link
)
{
    return sprintf(
        __( 'Hey %1$s' ) . ',<br>' . __( 'Please help us improve %2$s! If you opt-in, some data about your usage of %2$s will be sent to %5$s. If you skip this, that\'s okay! %2$s will still work just fine.', 'wp-google-business-reviews' ),
        $user_first_name,
        '<b>' . $plugin_title . '</b>',
        '<b>' . $user_login . '</b>',
        $site_link,
        $freemius_link
    );
}

wgbr_fs()->add_filter(
    'connect_message_on_update',
    'wgbr_fs_custom_connect_message_on_update',
    10,
    6
);
require ABSPATH . 'wp-includes/version.php';
define( 'WPGBR_VERSION', '1.0.0' );
define( 'WPGBR_PLUGIN_FILE', __FILE__ );
define( 'WPGBR_PLUGIN_URL', plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) );
define( 'WPGBR_ASSETS_URL', WPGBR_PLUGIN_URL . '/asset/' );
define( 'WPGBR_MENU_URL', WPGBR_PLUGIN_URL . '/asset/admin/img/menu.png' );
define( 'WPGBR_GOOGLE_PLACE_API', 'https://maps.googleapis.com/maps/api/place/' );
require_once __DIR__ . '/autoloader.php';
$wpgbr = new Includes\Wpgbr_Google_Business_Reviews();
$wpgbr->init();
