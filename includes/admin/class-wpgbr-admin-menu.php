<?php
namespace WP_Hut_Google_Business_Reviews\Includes\Admin;

/**
 * Wpgbr_Admin_Menu
 */
class Wpgbr_Admin_Menu {
	/**
	 *  Constructor
	 */
	public function __construct() {
	}

	/**
	 * Init
	 */
	public function init() {
		add_action( 'admin_menu', array( $this, 'add_page' ) );
		add_action( 'admin_menu', array( $this, 'add_subpages' ) );
		// add_filter('submenu_file', array($this, 'remove_submenu_pages'));
		// add_filter('admin_body_class', array($this, 'add_admin_body_class'));?
	}

	/**
	 * Add_page
	 */
	public function add_page() {
		add_menu_page(
			'WP Google Business Reviews',
			'GB Reviews',
			'manage_options',
			'wpgbr',
			array( $this, 'general_page' ),
			WPGBR_MENU_URL,
			25
		);
	}

	/**
	 * Add_subpages
	 */
	public function add_subpages() {
		add_submenu_page(
			'wpgbr',
			'Settings',
			'Settings',
			'manage_options',
			'wpgbr-settings',
			array( $this, 'general_page' )
		);

	}

	/**
	 * General_page
	 */
	public function general_page() {
		echo '<div class="wpgbr-admin-page">';
		do_action( 'wpgbr_admin_page_general' );
		echo '</div>';
	}
}
