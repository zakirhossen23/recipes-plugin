<?php
/**
 * Plugin: Permalinks
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'mytheme_initialize_theme_options' ) ) {
	/**
	 * Adds a section for custom permalinks.
	 */
	function mytheme_initialize_theme_options() {
		add_settings_section( 'permalink_settings_section', __( 'Recipes Theme', 'recipes' ), 'mytheme_general_options_callback', 'permalink' );
	}
}
add_action( 'admin_init', 'mytheme_initialize_theme_options' );

if ( ! function_exists( 'mytheme_general_options_callback' ) ) {
	/**
	 * Adds instructions for the custom permalink section.
	 */
	function mytheme_general_options_callback() {
		echo '<p>' . esc_html__( 'Change the URL structure used in the theme. If you leave these blank the defaults will be used.', 'recipes' ) . '</p>';
	}
}

if ( ! function_exists( 'mytheme_load_permalinks' ) ) {
	/**
	 * Adds form fields to the custom permalink section.
	 */
	function mytheme_load_permalinks() {

		$custom_permalinks = array(
			'recipe'         => __( 'Recipe base', 'recipes' ),
			'recipe_archive' => __( 'Recipe archive base', 'recipes' ),
		);

		$taxonomies = get_object_taxonomies( 'recipe', 'objects' );

		foreach ( $taxonomies as $taxonomy ) {
			$slug = str_replace( '-', '_', $taxonomy->name );

			// Translators: %s is the singular name of the taxonomy.
			$custom_permalinks[ $slug ] = sprintf( esc_html_x( '%s base', 'Taxonomy label in permalink settings', 'recipes' ), $taxonomy->labels->singular_name );
		}

		foreach ( $custom_permalinks as $slug => $title ) {
			add_settings_field(
				'permalink_' . $slug . '_base',
				$title,
				'mytheme_permalink_callback',
				'permalink',
				'permalink_settings_section',
				array(
					'term' => $slug,
				)
			);
		}

		foreach ( $custom_permalinks as $slug => $title ) {
			$value = filter_input( INPUT_POST, 'permalink_' . $slug . '_base' );
			if ( isset( $value ) ) {
				update_option( 'permalink_' . $slug . '_base', sanitize_option( 'category_base', $value ) );
			}
		}
	}
}
add_action( 'load-options-permalink.php', 'mytheme_load_permalinks' );

if ( ! function_exists( 'mytheme_permalink_callback' ) ) {
	/**
	 * Callback function for displaying the form fields.
	 *
	 * @param  array $args Additional arguments passed to the function..
	 */
	function mytheme_permalink_callback( $args ) {

		$value = get_option( 'permalink_' . $args['term'] . '_base' );

		echo '<input type="text" value="' . esc_attr( $value ) . '" name="permalink_' . esc_attr( $args['term'] ) . '_base" id="permalink_' . esc_attr( $args['term'] ) . '_base" class="regular-text code">';
	}
}
