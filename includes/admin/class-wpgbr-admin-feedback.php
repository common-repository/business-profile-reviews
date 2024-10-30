<?php

namespace WP_Hut_Google_Business_Reviews\Includes\Admin;

/**
 * Wpgbr_Admin_Feedback
 */
class Wpgbr_Admin_Feedback {
	/**
	 * Feedback Messages
	 *
	 * @var string[]
	 */
	private static $feedbacks = array(
		'general_saved' => 'General Settings Saved successfully.',
		'google_saved'  => 'Google Settings Saved successfully.',
	);

	/**
	 * Init
	 */
	public function init() {
		add_filter( 'removable_query_args', array( $this, 'remove_query_args' ) );
		add_action( 'admin_notices', array( $this, 'parse_notices_from_url' ) );
		add_action( 'admin_notices', array( $this, 'render_notices' ) );
	}

	/**
	 * Remove_query_args
	 *
	 * @param array $args array of query args.
	 *
	 * @return array
	 */
	public function remove_query_args( $args ): array {
		return array_merge( $args, array( 'wpgbr_feedback' ) );
	}

	/**
	 * Parse_notices_from_url
	 */
	public function parse_notices_from_url() {
		if ( ! isset( $_GET['wpgbr_feedback'] ) ) {
			return;
		}

		$this->notice_id = sanitize_text_field( wp_unslash( $_GET['wpgbr_feedback'] ) );
	}

	/**
	 *
	 */
	public function render_notices() {
		if ( empty( $this->notice_id ) || ! $this->is_valid_screen() ) {
			return;
		}

		if ( doing_action( 'admin_notices' ) && $this->needs_repositioned() ) {
			add_action( 'wpgbr_admin_notices', array( $this, 'render_notices' ) );
			return;
		}

		?>
		<div class="notice notice-success is-dismissible">
		<?php $msg = 'custom_msg' !== $this->notice_id ? self::$feedbacks[ $this->notice_id ] : get_option( 'wpgbr_feedback_msg' ); ?>
			<p><?php echo esc_html( $msg ); ?></p>
		</div>
		<?php

		$this->notice_id = '';
	}

	/**
	 * Is valid Screen
	 *
	 * @param string $screen_id screen_id.
	 *
	 * @return bool
	 */
	protected function is_valid_screen( $screen_id = '' ) {
		if (  '' === $screen_id ) {
			$screen    = get_current_screen();
			$screen_id = $screen->id;
		}

		return $screen_id === 'dashboard' || $screen_id === 'plugins' || str_contains( $screen_id, 'wpgbr' );
	}

	/**
     * Needs_repositioned
     *
	 * @param string $screen_id screen_id.
	 *
	 * @return bool
	 */
	protected function needs_repositioned( string $screen_id = '' ): bool {
		if ( $screen_id === '' ) {
			$screen    = get_current_screen();
			$screen_id = $screen->id;
		}

		$screen_ids = array( 'google-reviews_page_wpgbr-settings' );
		return in_array( $screen_id, $screen_ids );
	}
}
