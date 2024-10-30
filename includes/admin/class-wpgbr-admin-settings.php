<?php

namespace WP_Hut_Google_Business_Reviews\Includes\Admin;

use  WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Google_Connect ;
/**
 * Admin Settings Page
 */
class Wpgbr_Admin_Settings
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Initialise Settings Hooks
     */
    public function init()
    {
        add_action( 'wpgbr_admin_page_general', array( $this, 'general_page' ) );
        add_action( 'admin_post_wpgbr_settings_save', array( $this, 'wpgbr_settings_save' ) );
    }

    /**
     * General Page Settings
     */
    public function general_page()
    {
        global  $wgbr_fs ;
        $wpgbr_google_api_key = get_option( 'wpgbr_google_api_key' );
        $wpgbr_google_placeId = get_option( 'wpgbr_google_placeId' );
        $tab = 'google';
        ?>
		<div class="wpgbr-tophead">
			<div class="wpgbr-tophead-title">
				<img src="<?php
        esc_attr_e( WPGBR_ASSETS_URL . 'admin/img/wpgbr.png' );
        ?>" alt="logo" width="32">
				Google Reviews
			</div>
		</div>
		<div class="wpgbr-page-title">
			Settings
		</div>

		<?php
        do_action( 'wpgbr_admin_notices' );
        ?>

		<div class="wpgbr-settings-workspace">

			<div data-nav-tabs="">

				<div class="nav-tab-wrapper wpgbr-nav-tab-wrapper">
				   <?php
        ?>
					<a href="#wpgbr-google"
					   class="tab2 nav-tab
					   <?php
        if ( $tab == 'google' ) {
            ?>
							 nav-tab-active<?php
        }
        ?>">Google</a>
				</div>
		<?php
        ?>
				<div id="wpgbr-google" class="tab-content"
					 style="display:<?php
        echo  ( $tab === 'google' ? 'block' : 'none' ) ;
        ?>;">
					<h3>Google</h3>
					<form method="post"
						  action="<?php
        echo  esc_url( admin_url( 'admin-post.php?action=wpgbr_settings_save&wpgbr_tab=google' ) ) ;
        ?>">
						<?php
        wp_nonce_field( 'wpgbr-nonce_google', 'wpgbr-form_nonce_google' );
        ?>

						<div class="wpgbr-form-field">
							<div class="wpgbr-form-label">
								<label><b>Google Places API key</b></label>
							</div>
							<div class="wp-review-field-option">
								<input type="password" name="wpgbr_google_api_key" id="wpgbr_google_api_key" value="<?php
        echo  esc_attr( $wpgbr_google_api_key ) ;
        ?>"  autocomplete="off">
								<p>To create Google API key, please read: <a href="https://wp-hut.com/docs/how-to-connect-google-reviews/#3-toc-title" target="_blank">Full Installation Guide</a></p>
							</div>
						</div>

						<div class="wpgbr-form-field">
							<div class="wpgbr-form-label">
								<label><b>Google Places ID</b></label>
							</div>
							<div class="wp-review-field-option">
								<input type="text" name="wpgbr_google_placeId" id="wpgbr_google_placeId" value="<?php
        echo  esc_attr( $wpgbr_google_placeId ) ;
        ?>"  autocomplete="off">
								<p>To get your Google Places ID, please read: <a href="https://wp-hut.com/docs/how-to-connect-google-reviews/" target="_blank">Full Installation Guide</a>
								</p>
							</div>
						</div>
						<input type="hidden" name="name" value="google">
						<div class="wpgbr-form-field">
							<div class="wpgbr-form-label">
								<label></label>
							</div>
							<div class="wp-review-field-option">
								<div style="padding-top:15px">
									<button id="" type="submit" class="button button-primary">Save & Update</button>
									<button id="connect_google" type="button" class="button button-primary">Connect
										Google
									</button>
								</div>
								<div class="admin-feedback hide"></div>
							</div>

						</div>

					</form>
				</div>


			</div>

		</div>
		<?php
    }

    /**
     *
     */
    public function wpgbr_settings_save()
    {
        global  $wpdb, $wgbr_fs ;
        $feedback_msg = '';
        if ( !function_exists( 'wp_nonce_field' ) ) {
            function wp_nonce_field()
            {
            }

        }
        if ( !current_user_can( 'manage_options' ) ) {
            die( 'The account you\'re logged in to doesn\'t have permission to access this page.' );
        }

        if ( !empty($_POST) ) {

            if ( isset( $_POST['name'] ) && $_POST['name'] === 'google' ) {

                if ( !isset( $_POST['wpgbr-form_nonce_google'] ) || !wp_verify_nonce( $_POST['wpgbr-form_nonce_google'], 'wpgbr-nonce_google' ) ) {
                    die( 'Unable to save changes. Make sure you are accessing this page from the WordPress dashboard.' );
                } else {
                    // update
                    $fields = array( 'wpgbr_google_api_key', 'wpgbr_google_placeId' );
                    foreach ( $fields as $key => $value ) {
                        if ( isset( $_POST[$value] ) ) {
                            update_option( $value, trim( sanitize_text_field( wp_unslash( $_POST[$value] ) ) ) );
                        }
                    }
                    $feedback_msg = 'google_saved';
                    $this->redirect_to_tab( $feedback_msg );
                }

            } else {
                die( 'Unable to save changes. Make sure you are accessing this page from the WordPress dashboard.' );
            }

        } else {
            die( 'Unable to save changes. Make sure you are accessing this page from the WordPress dashboard.' );
        }

    }

    /**
     * Redirect to show the feedback
     *
     * @param string $feedback_msg
     */
    public function redirect_to_tab( $feedback_msg = '' )
    {

        if ( empty($_GET['wpgbr_tab']) ) {
            wp_safe_redirect( wp_get_referer() );
            exit;
        }

        $tab = sanitize_text_field( wp_unslash( $_GET['wpgbr_tab'] ) );
        $query_args = array(
            'wpgbr_tab' => $tab,
        );

        if ( !empty($feedback_msg) ) {
            $query_args['wpgbr_feedback'] = $feedback_msg;
        } else {
            $query_args['wpgbr_feedback'] = 'tes';
        }

        wp_safe_redirect( add_query_arg( $query_args, wp_get_referer() ) );
        exit;
    }

}
