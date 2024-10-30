<?php
namespace WP_Hut_Google_Business_Reviews\Includes\Core;

use WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Db;

/**
 * Google Connect
 */
class Wpgbr_Google_Connect {
	/**
	 * @var
	 */
	private $_gkey;
	/**
	 * @var
	 */
	private $_placeId;

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_google_connect', array( $this, 'google_connect' ) );
		add_action('wpgbr_add_more_reviews', array($this, 'add_more_reviews'));
	}

	/**
	 * Init
	 */
	public function init() {
		$this->_gkey    = get_option( 'wpgbr_google_api_key' );
		$this->_placeId = get_option( 'wpgbr_google_placeId' );
	}

	/**
	 * Google API Connect
	 */
	public function google_connect() {
		$this->g_connect();
		die();
	}

	/**
	 * Google API Connect
	 */
	public function g_connect() {
		if ( current_user_can( 'manage_options' ) ) {

			if ( ! $this->_gkey || ! $this->_placeId ) {
				$this->_gkey    = get_option( 'wpgbr_google_api_key' );
				$this->_placeId = get_option( 'wpgbr_google_placeId' );
			}

			if ( $this->_gkey && strlen( $this->_gkey ) > 0 && $this->_placeId && strlen( $this->_placeId ) > 0 ) {

				$url       = $this->setup_url();
				$res       = wp_remote_get( $url );
				$body      = wp_remote_retrieve_body( $res );
				$body_json = json_decode( $body );

				if ( $body_json && isset( $body_json->result ) ) {
					$photo                             = $this->business_logo( $body_json->result, $this->_gkey );
					$body_json->result->business_photo = $photo;
					// error_log(print_r($body_json->result, true));
					$this->save_reviews( $body_json->result );
					wp_send_json( array( 'msg' => 'Saved Reviews Successfully' ), 200 );
					// return Success
				} else {
					wp_send_json( array( 'msg' => 'couldn\'t fetch the details. Please check the placeId' ), 400 );
				}
			} else {
				wp_send_json( array( 'msg' => 'Please provide API key and PlaceId' ), 400 );
			}
		}
	}

	/**
	 * Add More Reviews
	 */
	public function add_more_reviews() {
		$google_api_key = get_option( 'wpgbr_google_api_key' );
		if ( ! $google_api_key ) {
			return;
		}

		$place_id     = get_option( 'wpgbr_google_placeId' );
		$reviews_lang = 'en-US';

		$url = $this->setup_url( $reviews_lang );

		$res       = wp_remote_get( $url );
		$body      = wp_remote_retrieve_body( $res );
		$body_json = json_decode( $body );

		if ( $body_json && isset( $body_json->result ) ) {
			$photo                             = $this->business_logo( $body_json->result, $this->_gkey );
			$body_json->result->business_photo = $photo;

			$this->save_reviews( $body_json->result );
		}

//		delete_transient( 'grw_refresh_reviews_' . join( '_', $args ) );
	}

	/**
	 * Save Reviews to DB
	 *
	 * @param $place
	 */
	function save_reviews( $place ) {
		global $wpdb;

		$google_place_id = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT id FROM ' . $wpdb->prefix . Wpgbr_Db::BUSINESS_INFO_TABLE .
				' WHERE place_id = %s',
				$place->place_id
			)
		);

		// Insert or update Google place
		if ( $google_place_id ) {

			// Update Google place
			$update_params = array(
				'name'    => $place->name,
				'rating'  => $place->rating,
				'updated' => round( microtime( true ) * 1000 ),
			);

			$review_count = isset( $place->user_ratings_total ) ? $place->user_ratings_total : 0;

			if ( $review_count > 0 ) {
				$update_params['review_count'] = $review_count;
			}
			if ( isset( $place->business_photo ) && strlen( $place->business_photo ) > 0 ) {
				$update_params['photo'] = $place->business_photo;
			}
			$wpdb->update( $wpdb->prefix . Wpgbr_Db::BUSINESS_INFO_TABLE, $update_params, array( 'ID' => $google_place_id ) );

			// Insert Google place rating stats
			$stats = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT rating, review_count FROM ' . $wpdb->prefix . Wpgbr_Db::BUSINESS_STATS_TABLE .
					' WHERE google_place_id = %d ORDER BY id DESC LIMIT 1',
					$google_place_id
				)
			);
			if ( count( $stats ) > 0 ) {
				if ( $stats[0]->rating != $place->rating || ( $review_count > 0 && $stats[0]->review_count != $review_count ) ) {
					$wpdb->insert(
						$wpdb->prefix . Wpgbr_Db::BUSINESS_STATS_TABLE,
						array(
							'google_place_id' => $google_place_id,
							'time'            => time(),
							'rating'          => $place->rating,
							'review_count'    => $review_count,
						)
					);
				}
			} else {
				$wpdb->insert(
					$wpdb->prefix . Wpgbr_Db::BUSINESS_STATS_TABLE,
					array(
						'google_place_id' => $google_place_id,
						'time'            => time(),
						'rating'          => $place->rating,
						'review_count'    => $review_count,
					)
				);
			}
		} else {

			// Insert Google place
			$place_rating = isset( $place->rating ) ? $place->rating : null;
			$review_count = isset( $place->user_ratings_total ) ?
				$place->user_ratings_total : ( isset( $place->reviews ) ? count( $place->reviews ) : null );

			$wpdb->insert(
				$wpdb->prefix . Wpgbr_Db::BUSINESS_INFO_TABLE,
				array(
					'place_id'     => $place->place_id,
					'name'         => $place->name,
					'photo'        => $place->business_photo,
					'icon'         => $place->icon,
					'address'      => $place->formatted_address,
					'rating'       => $place_rating,
					'url'          => isset( $place->url ) ? $place->url : null,
					'website'      => isset( $place->website ) ? $place->website : null,
					'review_count' => $review_count,
				)
			);
			$google_place_id = $wpdb->insert_id;

			if ( $place_rating > 0 ) {
				$wpdb->insert(
					$wpdb->prefix . Wpgbr_Db::BUSINESS_STATS_TABLE,
					array(
						'google_place_id' => $google_place_id,
						'time'            => time(),
						'rating'          => $place_rating,
						'review_count'    => $review_count,
					)
				);
			}
		}

		// Insert or update Google reviews
		if ( $place->reviews ) {

			$reviews = $place->reviews;

			foreach ( $reviews as $review ) {
				$google_review_id = 0;
				if ( isset( $review->author_url ) && strlen( $review->author_url ) > 0 ) {
					$where       = ' WHERE author_url = %s';
					$where_array = array( $review->author_url );
				} elseif ( isset( $review->author_name ) && strlen( $review->author_name ) > 0 ) {
					$where       = ' WHERE author_name = %s';
					$where_array = array( $review->author_name );
				} else {
					$where       = ' WHERE time = %s';
					$where_array = array( $review->time );
				}

				$review_lang = ( $review->language == 'en-US' ? 'en' : $review->language );
				if ( strlen( $review_lang ) > 0 ) {
					$where = $where . ' AND language = %s';
					array_push( $where_array, $review_lang );
				}

				$google_review_id = $wpdb->get_var(
					$wpdb->prepare(
						'SELECT id FROM ' . $wpdb->prefix . Wpgbr_Db::BUSINESS_REVIEW_TABLE . $where,
						$where_array
					)
				);

				if ( $google_review_id ) {
					$update_params = array(
						'rating' => $review->rating,
						'text'   => $review->text,
					);
					if ( isset( $review->profile_photo_url ) ) {
						$update_params['profile_photo_url'] = $review->profile_photo_url;
					}
					$wpdb->update( $wpdb->prefix . Wpgbr_Db::BUSINESS_REVIEW_TABLE, $update_params, array( 'id' => $google_review_id ) );
				} else {
					$wpdb->insert(
						$wpdb->prefix . Wpgbr_Db::BUSINESS_REVIEW_TABLE,
						array(
							'google_place_id'   => $google_place_id,
							'rating'            => $review->rating,
							'text'              => $review->text,
							'time'              => $review->time,
							'language'          => $review_lang,
							'author_name'       => $review->author_name,
							'author_url'        => isset( $review->author_url ) ? $review->author_url : null,
							'profile_photo_url' => isset( $review->profile_photo_url ) ? $review->profile_photo_url : null,
						)
					);
				}
			}
		}
	}

	/**
	 * Setup API URL
	 *
	 * @param string $reviews_lang
	 *
	 * @return string
	 */
	private function setup_url( $reviews_lang = '' ) {
		$url = WPGBR_GOOGLE_PLACE_API . 'details/json?placeid=' . $this->_placeId . '&key=' . $this->_gkey;
		if ( strlen( $reviews_lang ) > 0 ) {
			$url = $url . '&language=' . $reviews_lang;
		}
		return $url;
	}


	/**
	 * Set Business Logo
	 *
	 * @param $response_json
	 * @param $google_api_key
	 *
	 * @return mixed|null
	 */
	private function business_logo( $response_json, $google_api_key ) {
		if ( isset( $response_json->photos ) ) {
			$url = add_query_arg(
				array(
					'photoreference' => $response_json->photos[0]->photo_reference,
					'key'            => $google_api_key,
					'maxwidth'       => '300',
					'maxheight'      => '300',
				),
				'https://maps.googleapis.com/maps/api/place/photo'
			);
			$res = wp_remote_get( $url, array( 'timeout' => 8 ) );
			if ( ! is_wp_error( $res ) ) {
				$bits     = wp_remote_retrieve_body( $res );
				$filename = $response_json->place_id . '.jpg';

				$upload_dir    = wp_upload_dir();
				$full_filepath = $upload_dir['path'] . '/' . $filename;
				if ( file_exists( $full_filepath ) ) {
					wp_delete_file( $full_filepath );
				}

				$upload = wp_upload_bits( $filename, null, $bits );
				return $upload['url'];
			}
		}
		return null;
	}
}
