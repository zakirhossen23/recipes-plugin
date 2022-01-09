<?php
/**
 * Plugin: Misc
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'mytheme_get_members' ) ) {
	/**
	 * Returns members with post count for the member directory template.
	 */
	function mytheme_get_members() {

		$return = array();

		$args = array(
			'orderby' => 'registered',
			'order'   => 'ASC',
		);

		if ( ! mytheme_get_option( 'show_admins_in_member_directory' ) ) {
			$args['role__not_in'] = 'Administrator';
		}

		$user_query = new WP_User_Query( $args );

		if ( ! empty( $user_query->results ) ) {
			foreach ( $user_query->results as $user ) {
				$return[ $user->ID ]['ID'] = $user->ID;
			}
		}
		wp_reset_postdata();

		return $return;
	}
}

if ( ! function_exists( 'mytheme_set_cookies' ) ) {
	/**
	 * Sets cookies.
	 */
	function mytheme_set_cookies() {
		if ( wp_doing_ajax() ) {
			return;
		}

		$cookie_value = filter_input( INPUT_COOKIE, 'rcps' );

		if ( empty( $cookie_value ) ) {
			setcookie( 'rcps', mytheme_get_user_key(), time() + 180 * MINUTE_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );
		}
	}
}
add_action( 'plugins_loaded', 'mytheme_set_cookies', 0 );

if ( ! function_exists( 'mytheme_get_user_key' ) ) {
	/**
	 * Checks the user's cookie if they have it. Creates one if they don't.
	 * If the user is logged in, uses the user's ID.
	 */
	function mytheme_get_user_key() {

		if ( is_user_logged_in() ) {
			return get_current_user_id();
		} elseif ( ! is_user_logged_in() ) {

			$cookie_value = filter_input( INPUT_COOKIE, 'rcps' );

			if ( ! empty( $cookie_value ) ) {
				$key = (string) $cookie_value;
			} else {
				$key = md5( time() . wp_rand() );
			}

			return $key;
		}
	}
}

if ( ! function_exists( 'mytheme_insert_attachment' ) ) {
	/**
	 * Handles the file upload for the front-end forms.
	 *
	 * @param  string  $file_handler  Uploaded file handler.
	 * @param  int     $post_id       Post ID.
	 * @param  boolean $set_thumbnail Whether to set the uploaded image as thumbnail.
	 * @return int                    Attachment ID.
	 */
	function mytheme_insert_attachment( $file_handler, $post_id = 0, $set_thumbnail = false ) {

		if ( UPLOAD_ERR_OK !== $_FILES[ $file_handler ]['error'] ) {
			__return_false();
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$attachment_id = media_handle_upload( $file_handler, $post_id );

		if ( ! empty( $post_id ) && true === $set_thumbnail ) {
			update_post_meta( $post_id, '_thumbnail_id', $attachment_id );
		}

		return $attachment_id;
	}
}

if ( ! function_exists( 'rcps_filter_the_content_amp' ) ) {
	/**
	 * Adds custom meta fields and taxonomies to the AMP post content.
	 *
	 * @param  string $content Post content.
	 * @return string
	 */
	function rcps_filter_the_content_amp( $content ) {

		// Check if we're in a singular recipe, and AMP is supported.
		if ( is_singular( 'recipe' ) && function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {

			// Turn on output buffering.
			ob_start();

			get_template_part( 'templates/template', 'amp-meta' );

			// Gets current buffer contents and deletes current output buffer.
			$html = ob_get_clean();

			return $html . $content;
		}

		return $content;
	}
}
add_filter( 'the_content', 'rcps_filter_the_content_amp' );

/**
 * Builds nutrition facts for the recipe.
 * Uses CMB2 fields to get values.
 *
 * @param Wp_Post $post    Wp_Post object.
 * @param array   $exclude Excluded meta fields.
 *
 * @return array
 */
function rcps_get_nutrition_facts( $post, $exclude = false ) {

	if ( ! class_exists( '\CMB2_Boxes' ) ) {
		return;
	}

	$return = array();

	$meta_box = \CMB2_Boxes::get( 'rcps_cmb2_box_recipe_nutrition' );

	if ( ! empty( $meta_box ) ) {
		foreach ( $meta_box->meta_box['fields'] as $meta_key => $fields ) {

			// If the field is excluded.
			if ( ! empty( $exclude ) && is_array( $exclude ) && in_array( $meta_key, $exclude, true ) ) {
				continue;
			}

			$meta_value = $post->$meta_key;

			if ( ! empty( $meta_value ) ) {

				$object = new \stdClass();

				$object->name   = $fields['name'];
				$object->amount = $meta_value;

				// Structured data name.
				if ( ! empty( $fields['rcps_structured_data_name'] ) ) {
					$object->structured_data_name = $fields['rcps_structured_data_name'];
				}

				// Unit.
				if ( ! empty( $fields['rcps_unit'] ) ) {
					$object->unit = $fields['rcps_unit'];
				}

				// Daily value.
				if ( ! empty( $fields['rcps_daily_value'] ) ) {
					$object->daily_value = $fields['rcps_daily_value'];

					// Calculate percentage of the daily value.
					$object->daily_value_percent = round( 100 * ( $meta_value / $object->daily_value ), 0 ) . '%';
				}

				$return[ $meta_key ] = $object;
			}
		}
	}

	return $return;
}

/**
 * Returns ID of a page using a shortcode.
 *
 * @param string $shortcode Shortcode.
 *
 * @return int
 */
function rcps_get_page_with_shortcode( $shortcode ) {

	$transient_key = 'rcps_page_with_shortcode_' . esc_attr( $shortcode );

	$page_id = get_transient( $transient_key );

	if ( false === $page_id ) {

		$paged = 0;

		do {
			$paged++;

			$wp_query_args = array(
				'post_type'      => 'page',
				'posts_per_page' => 100,
				'order'          => 'date',
				'order'          => 'DESC',
				'paged'          => $paged,
			);

			$wp_query_pages = new WP_Query( $wp_query_args );

			while ( $wp_query_pages->have_posts() ) {
				$wp_query_pages->the_post();

				if ( has_shortcode( get_the_content(), $shortcode ) ) {
					set_transient( $transient_key, get_the_ID(), MONTH_IN_SECONDS );
					return get_the_ID();
				}
			}

			wp_reset_postdata();
		} while ( $wp_query_pages->have_posts() );
	}

	return $page_id;
}

/**
 * Makes it possible to use 'rand' orderby parameter in user query.
 *
 * @param WP_User_Query $class User query.
 */
function rcps_random_user_query( $class ) {

	if ( 'rand' === $class->query_vars['orderby'] ) {
		$class->query_orderby = str_replace( 'user_login', 'RAND()', $class->query_orderby );
	}
}
add_action( 'pre_user_query', 'rcps_random_user_query' );

/**
 * Returns HTML for a fallback image.
 */
function mytheme_get_term_fallback_image() {

	$attr = array(
		'width'  => '280',
		'height' => '200',
		'src'    => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARgAAADICAMAAAAEGQ4lAAAAA1BMVEXd3d3u346CAAAATUlEQVR42u3BAQ0AAADCoPdPbQ8HFAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAI8G24gAAbc3P+EAAAAASUVORK5CYII=',
	);

	ob_start();
	echo '<img ';
	foreach ( $attr as $key => $value ) {
		if ( ! empty( $key ) ) {
			if ( ! empty( $key ) && ! empty( $value ) ) {
				echo esc_attr( $key ) . '="' . esc_attr( $value ) . '" ';
			}
		}
	}
	echo 'loading="eager">';
	$html = ob_get_clean();

	return $html;
}
