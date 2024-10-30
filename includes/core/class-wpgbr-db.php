<?php

namespace WP_Hut_Google_Business_Reviews\Includes\Core;

/**
 * Database Class
 */
class Wpgbr_Db {
	/**
	 * Business Info Table Name
	 */
	const BUSINESS_INFO_TABLE = 'wpgbr_business_info';

	/**
	 * Business Review Table Name
	 */
	const BUSINESS_REVIEW_TABLE = 'wpgbr_business_reviews';

	/**
	 * Business Stats Table Name
	 */
	const BUSINESS_STATS_TABLE = 'wpgbr_business_stats';

	/**
	 * Create Database Tables
	 */
	public function create() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wpgbr_business_info (' .
			   'id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,' .
			   'place_id VARCHAR(80) NOT NULL,' .
			   'name VARCHAR(255) NOT NULL,' .
			   'photo VARCHAR(255),' .
			   'icon VARCHAR(255),' .
			   'address VARCHAR(255),' .
			   'rating DOUBLE PRECISION,' .
			   'url VARCHAR(255),' .
			   'website VARCHAR(255),' .
			   'review_count INTEGER,' .
			   'updated BIGINT(20),' .
			   'PRIMARY KEY (`id`),' .
			   'UNIQUE INDEX wpgbr_place_id (`place_id`)' .
			   ') ' . $charset_collate . ';';

		dbDelta( $sql );
		error_log( 'Created db' );
		$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wpgbr_business_reviews (' .
			   'id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,' .
			   'google_place_id BIGINT(20) UNSIGNED NOT NULL,' .
			   'rating INTEGER NOT NULL,' .
			   'text VARCHAR(10000),' .
			   'time INTEGER NOT NULL,' .
			   'language VARCHAR(10),' .
			   'author_name VARCHAR(255),' .
			   'author_url VARCHAR(255),' .
			   'profile_photo_url VARCHAR(255),' .
			   "hide VARCHAR(1) DEFAULT '' NOT NULL," .
			   'PRIMARY KEY (`id`),' .
			   'INDEX wpgbr_google_place_id (`google_place_id`)' .
			   ') ' . $charset_collate . ';';

		dbDelta( $sql );

		$sql = 'CREATE TABLE IF NOT EXISTS ' . $wpdb->prefix . 'wpgbr_business_stats (' .
			   'id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,' .
			   'google_place_id BIGINT(20) UNSIGNED NOT NULL,' .
			   'time INTEGER NOT NULL,' .
			   'rating DOUBLE PRECISION,' .
			   'review_count INTEGER,' .
			   'PRIMARY KEY (`id`),' .
			   'INDEX wpgbr_google_place_id (`google_place_id`)' .
			   ') ' . $charset_collate . ';';

		dbDelta( $sql );
	}

	/**
	 * Drop Database Tables
	 */
	public function drop() {
		global $wpdb;

		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . self::BUSINESS_INFO_TABLE . ';' );
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . self::BUSINESS_REVIEW_TABLE . ';' );
		$wpdb->query( 'DROP TABLE ' . $wpdb->prefix . self::BUSINESS_STATS_TABLE . ';' );
	}
}
