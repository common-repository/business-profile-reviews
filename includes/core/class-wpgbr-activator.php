<?php

namespace WP_Hut_Google_Business_Reviews\Includes\Core;


/**
 * Activator Class
 */
class Wpgbr_Activator {
	/**
	 * @var $database
	 */
	private $database;

	/**
	 * Constructor
	 * @param $database
	 */
	public function __construct( $database ) {
		$this->database = $database;
	}

	/**
	 * Init
	 */
	public function init() {
		add_action( 'init', array( $this, 'check_version' ) );
	}

	/**
	 * Check Plugin Version
	 */
	public function check_version() {
		if ( version_compare( get_option( 'wpgbr_version' ), WPGBR_VERSION, '<' ) ) {
			$this->activate();
		}
	}

	/**
	 * Activate
	 */
	private function activate() {
		$this->database->create();
		update_option( 'wpgbr_version', WPGBR_VERSION );

	}
}
