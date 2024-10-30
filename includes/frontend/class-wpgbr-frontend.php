<?php

namespace WP_Hut_Google_Business_Reviews\Includes\Frontend;

use  WP_Hut_Google_Business_Reviews\Includes\Core\Wpgbr_Db ;
/**
 *
 */
class Wpgbr_Frontend
{
    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Init
     */
    public function init()
    {
        global  $wgbr_fs ;
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts_styles' ) );
        add_action( 'wp_ajax_load_widget_template', array( $this, 'load_widget_template' ) );
        add_action( 'wp_ajax_nopriv_load_widget_template', array( $this, 'load_widget_template' ) );
    }

    /**
     * Load_scripts_styles
     */
    public function load_scripts_styles()
    {
        global  $wgbr_fs ;
        $display_option = get_option( 'wpgbr_display_option' );
        $wpgbr_notification_period = get_option( 'wpgbr_notification_period' );
        $flag = false;

        if ( 'home' === $display_option ) {

            if ( is_front_page() ) {
                $flag = true;
            } else {
                $flag = false;
            }

        } else {
            $flag = true;
        }


        if ( $flag ) {
            wp_enqueue_style(
                'wpgbr_main_css',
                WPGBR_PLUGIN_URL . '/asset/frontend/css/wpgbr-style.css',
                false,
                '1.0.0'
            );
            wp_enqueue_script(
                'wpgbr_main_js',
                WPGBR_PLUGIN_URL . '/asset/frontend/js/wpgbr-main.js',
                array( 'jquery' ),
                '1.0.0'
            );
            wp_localize_script( 'wpgbr_main_js', 'my_ajax_object', array(
                'ajax_url'            => admin_url( 'admin-ajax.php' ),
                'site_url'            => site_url(),
                'notification_period' => $wpgbr_notification_period,
            ) );
        }

    }

    /**
     * load_widget_template
     */
    public function load_widget_template()
    {
        global  $wpdb, $wgbr_fs ;
        $style_classes = ' wp-gbr-font';
        $placeId = get_option( 'wpgbr_google_placeId' );
        $review_url = "https://search.google.com/local/writereview?placeid={$placeId}";

        if ( $placeId ) {
            $query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . Wpgbr_Db::BUSINESS_INFO_TABLE . ' WHERE place_id = %s', $placeId );
            $business_info = $wpdb->get_row( $query );

            if ( $business_info ) {
                $query = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . Wpgbr_Db::BUSINESS_REVIEW_TABLE . ' WHERE google_place_id = %s ORDER BY time DESC', $business_info->id );
                $reviews = $wpdb->get_results( $query );
                $dark_style = '';
                $close_img = 'close.svg';
                $google_img = 'powered_by_google_on_white.png';
                $viewer_class = '';
                $viewer_class = 'free-version-viewer';
                $style_classes .= ' bottom-right';
                $wpgbr_review_time_option = 'hide';
                ob_start();
                ?>
				<!--        <div id="wp-google-business-reviews" class="border-round bottom-right wp-gbr-font">-->
				<?php
                $business_pic = WPGBR_PLUGIN_URL . '/asset/frontend/img/logo.png';
                if ( $business_info->photo ) {
                    $business_pic = $business_info->photo;
                }
                $business_rating = 0;
                if ( $business_info->rating ) {
                    $business_rating = $business_info->rating;
                }
                ?>
				<div id="wp-gbr-viewer" class="bottom-right <?php
                echo  esc_attr( $viewer_class ) ;
                ?> <?php
                echo  esc_attr( $dark_style ) ;
                ?>">
					<div class="wp-gbr-viewer-header">
						<div href="#" target="_blank" class="inline-flex">
							<img src="<?php
                echo  esc_attr( $business_pic ) ;
                ?>'; ?>" alt=""
								 width="50"
								 class="brand-logo">
							<div class="ml-4">
								<div class="heading2"><?php
                echo  ( esc_html( $business_info->name ) ?: '' ) ;
                ?></div>
								<div class="inline-flex justify-space-around">
									<span class="wp-gbr-profile-rating"><?php
                echo  ( esc_html( $business_info->rating ) ?: 0 ) ;
                ?></span>
									<div class="stars-sm ml-2">
										<?php
                echo  $this->get_rating_stars( $business_rating );
                ?>
									</div>
									<span class="muted ml-3"><?php
                echo  ( esc_html( $business_info->review_count ) ?: 0 ) ;
                ?> Reviews</span>
								</div>
							</div>
						</div>
						<button class="btn-close" onclick="wpgbr.closeDiv()">
							<img src="<?php
                echo  esc_attr( WPGBR_PLUGIN_URL . '/asset/frontend/img/' . $close_img ) ;
                ?>" alt="">
							<!-- <img src="close-light.svg" alt=""> -->
						</button>
					</div>
					<div class="wp-gbr-viewer-body">
						<!--                        onclick="wpgbr.openWindow(this);"-->

						<div class="wpgbr-actions">
							<a href="<?php
                echo  esc_url( $review_url ) ;
                ?>"
							   class="wpgbr-action-button"> <img
										src="<?php
                echo  esc_attr( WPGBR_PLUGIN_URL . '/asset/frontend/img/pen.png' ) ;
                ?>" alt="">
								Write
								A Review</a>
						</div>
						<a href="<?php
                echo  esc_url( $review_url ) ;
                ?>"
						   class="wpgbr-more-reviews" target="_blank">View More Reviews</a>
					</div>
					<div class="wp-gbr-viewer-footer">
						<img src="<?php
                echo  esc_attr( WPGBR_PLUGIN_URL . '/asset/frontend/img/' . $google_img ) ;
                ?>"
							 alt="">
						<!-- <img src="powered_by_google_on_non_white.png" alt=""> -->
					</div>
				</div>

				<div id="wp-google-business-reviews" class="<?php
                echo  $style_classes ;
                ?>" onclick="wpgbr.openDiv()">

					<div href="#" target="_blank" id="wp-gbr-button" class="inline-flex">
						<img src="<?php
                echo  esc_attr( WPGBR_PLUGIN_URL . '/asset/frontend/img/google.svg' ) ;
                ?>" alt=""
							 width="34">
						<div class="ml-2">
							<div class="inline-flex justify-space-around">
								<span class="wp-gbr-profile-rating"><?php
                echo  ( esc_html( $business_info->rating ) ?: 0 ) ;
                ?></span>
								<div class="stars ml-2">
									<?php
                echo $this->get_rating_stars( $business_rating );
                ?>
								</div>
							</div>
							<div class="wp-gbr-total-rating">Based on
								<span><?php
                echo  ( esc_html( $business_info->review_count ) ?: 0 ) ;
                ?></span> Reviews
							</div>
						</div>
					</div>
				</div>

                <?php
                ?>
				<?php
                echo  ob_get_clean() ;
            }

        }

        wp_die();
    }

    /**
     * Get_rating_stars
     *
     * @param $rating
     *
     * @return string
     */
    public function get_rating_stars( $rating ) : string
    {
        $op = '';

        if ( $rating > 4.5 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
        } elseif ( $rating > 4 && $rating <= 4.5 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-half.svg') . '" alt="">';
        } elseif ( $rating > 3.5 && $rating <= 4 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } elseif ( $rating > 3 && $rating <= 3.5 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-half.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } elseif ( $rating > 2.5 && $rating <= 3 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } elseif ( $rating > 2 && $rating <= 2.5 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-half.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } elseif ( $rating > 1.5 && $rating <= 2 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } elseif ( $rating > 1 && $rating <= 1.5 ) {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-half.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        } else {
            $op .= '<img src="' . esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
            $op .= '<img src="' .  esc_attr(WPGBR_PLUGIN_URL . '/asset/frontend/img/star-empty.svg') . '" alt="">';
        }

        return $op;
    }

}
