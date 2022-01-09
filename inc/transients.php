<?php
/**
 * Plugin: Transients
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Deletes transient used by the widget when updating the widget.
 * Only for widgets added by Recipes.
 *
 * @param array     $instance     The current widget instance's settings.
 * @param array     $new_instance Array of new widget settings.
 * @param array     $old_instance Array of old widget settings.
 * @param WP_Widget $wp_widget    The current widget instance.
 */
function rcps_widget_update_callback( $instance, $new_instance, $old_instance, $wp_widget ) {

	// Delete transients where the transient key is dynamically created.
	if ( strpos( $wp_widget->id, 'rcps_widget_list_related_recipes' ) !== false ) {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( // phpcs:ignore
			'DELETE FROM ' . $wpdb->options . ' WHERE (option_name LIKE %s OR option_name LIKE %s)',
			'_transient_rcps_widget_related_recipes_to_%',
			'_transient_timeout_rcps_widget_related_recipes_to_%'
		) );
	} elseif ( strpos( $wp_widget->id, 'rcps_widget_' ) !== false ) {
		$transient_key    = 'rcps_widget_' . $wp_widget->id;
		$delete_transient = delete_transient( $transient_key );
	}

	return $new_instance;
}
add_filter( 'widget_update_callback', 'rcps_widget_update_callback', 10, 4 );

/**
 * Deletes related transients when saving pages.
 *
 * @param  int $post_id Post ID.
 */
function mytheme_delete_transients( $post_id ) {

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	$post_type = get_post_type( $post_id );

	if ( 'recipe' === $post_type ) {
		// Delete transients related to recipes.
		global $wpdb;

		$wpdb->query( $wpdb->prepare( // phpcs:ignore
			'DELETE FROM ' . $wpdb->options . ' WHERE (option_name LIKE %s OR option_name LIKE %s)',
			'_transient_rcps_widget_%',
			'_transient_timeout_rcps_widget_%'
		) );
	} elseif ( 'page' === $post_type ) {
		delete_transient( 'rcps_page_with_shortcode_rcps_edit_recipe' );
		delete_transient( 'rcps_page_with_shortcode_rcps_user_settings' );
		delete_transient( 'rcps_page_with_shortcode_rcps_member_directory' );
	}
}
add_action( 'save_post', 'mytheme_delete_transients' );
