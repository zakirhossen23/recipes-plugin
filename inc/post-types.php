<?php
/**
 * Plugin: Post Types
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'mytheme_register_taxonomies' ) ) {
	/**
	 * Registers custom taxonomies.
	 */
	function mytheme_register_taxonomies() {

		if ( ! taxonomy_exists( 'course' ) ) {
			$slug = get_option( 'permalink_course_base' );
			if ( ! $slug ) {
				$slug = 'course';
			}

			$labels = array(
				'name'          => __( 'Courses', 'recipes' ),
				'singular_name' => __( 'Course', 'recipes' ),
				'menu_name'     => __( 'Courses', 'recipes' ),
				'edit_item'     => __( 'Edit Course', 'recipes' ),
				'search_items'  => __( 'Search Courses', 'recipes' ),
				'not_found'     => __( 'No courses found', 'recipes' ),
				'add_new_item'  => __( 'Add New Course', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Course', 'recipes' ),
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'query_var'         => 'course',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'course', 'recipe', $args );
		}

		if ( ! taxonomy_exists( 'cuisine' ) ) {
			$slug = get_option( 'permalink_cuisine_base' );
			if ( ! $slug ) {
				$slug = 'cuisine';
			}

			$labels = array(
				'name'          => __( 'Cuisines', 'recipes' ),
				'singular_name' => __( 'Cuisine', 'recipes' ),
				'menu_name'     => __( 'Cuisines', 'recipes' ),
				'edit_item'     => __( 'Edit Cuisine', 'recipes' ),
				'search_items'  => __( 'Search Cuisines', 'recipes' ),
				'not_found'     => __( 'No cuisine found', 'recipes' ),
				'add_new_item'  => __( 'Add New Cuisine', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Cuisine', 'recipes' ),
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'query_var'         => 'cuisine',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'cuisine', 'recipe', $args );
		}

		if ( ! taxonomy_exists( 'special-diet' ) ) {
			$slug = get_option( 'permalink_special_diet_base' );
			if ( ! $slug ) {
				$slug = 'special-diet';
			}

			$labels = array(
				'name'          => __( 'Special Diets', 'recipes' ),
				'singular_name' => __( 'Special Diet', 'recipes' ),
				'menu_name'     => __( 'Special Diets', 'recipes' ),
				'edit_item'     => __( 'Edit Special Diet', 'recipes' ),
				'search_items'  => __( 'Search Special Diet', 'recipes' ),
				'not_found'     => __( 'No special diets found', 'recipes' ),
				'add_new_item'  => __( 'Add New Special Diet', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Special Diet', 'recipes' ),
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'query_var'         => 'special-diet',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'special-diet', 'recipe', $args );
		}

		if ( ! taxonomy_exists( 'skill-level' ) ) {
			$slug = get_option( 'permalink_skill_level_base' );
			if ( ! $slug ) {
				$slug = 'skill-level';
			}

			$labels = array(
				'name'          => __( 'Skill Levels', 'recipes' ),
				'singular_name' => __( 'Skill Level', 'recipes' ),
				'menu_name'     => __( 'Skill Levels', 'recipes' ),
				'edit_item'     => __( 'Edit Skill Level', 'recipes' ),
				'search_items'  => __( 'Search Skill Levels', 'recipes' ),
				'not_found'     => __( 'No skill level found', 'recipes' ),
				'add_new_item'  => __( 'Add New Skill Level', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Skill Level', 'recipes' ),
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'query_var'         => 'skill-level',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'skill-level', 'recipe', $args );
		}

		if ( ! taxonomy_exists( 'collection' ) ) {
			$slug = get_option( 'permalink_collection_base' );
			if ( ! $slug ) {
				$slug = 'collection';
			}

			$labels = array(
				'name'          => __( 'Collections', 'recipes' ),
				'singular_name' => __( 'Collection', 'recipes' ),
				'menu_name'     => __( 'Collections', 'recipes' ),
				'edit_item'     => __( 'Edit Collection', 'recipes' ),
				'search_items'  => __( 'Search Collections', 'recipes' ),
				'not_found'     => __( 'No collection found', 'recipes' ),
				'add_new_item'  => __( 'Add New Collection', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Collection', 'recipes' ),
				'labels'            => $labels,
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_in_nav_menus' => true,
				'query_var'         => 'collection',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'collection', 'recipe', $args );
		}

		if ( ! taxonomy_exists( 'recipe-tag' ) ) {
			$slug = get_option( 'permalink_recipe_tag_base' );
			if ( ! $slug ) {
				$slug = 'recipe-tag';
			}

			$labels = array(
				'name'          => __( 'Recipe Tags', 'recipes' ),
				'singular_name' => __( 'Recipe Tag', 'recipes' ),
				'menu_name'     => __( 'Tags', 'recipes' ),
				'edit_item'     => __( 'Edit Recipe Tag', 'recipes' ),
				'search_items'  => __( 'Search Recipe Tags', 'recipes' ),
				'not_found'     => __( 'No recipe tags found', 'recipes' ),
				'add_new_item'  => __( 'Add New Recipe Tag', 'recipes' ),
			);

			$args = array(
				'label'             => __( 'Recipe Tag', 'recipes' ),
				'labels'            => $labels,
				'singular_label'    => __( 'Recipe Tag', 'recipes' ),
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'query_var'         => 'recipe-tag',
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'show_tagcloud'     => true,
				'rewrite'           => array(
					'slug'       => $slug,
					'with_front' => false,
				),
			);

			register_taxonomy( 'recipe-tag', 'recipe', $args );
		}
	}
}
add_action( 'init', 'mytheme_register_taxonomies' );

if ( ! function_exists( 'mytheme_post_types' ) ) {
	/**
	 * Registers custom post types.
	 */
	function mytheme_post_types() {

		$slug = get_option( 'permalink_recipe_base' );
		if ( ! $slug ) {
			$slug = 'recipe';
		}

		$slug_archive = get_option( 'permalink_recipe_archive_base' );
		if ( ! $slug_archive ) {
			$slug_archive = _x( 'recipes', 'Recipe archive page URL slug.', 'recipes' );
		}

		$labels = array(
			'name'          => __( 'Recipes', 'recipes' ),
			'singular_name' => __( 'Recipe', 'recipes' ),
			'add_new'       => __( 'Add New Recipe', 'recipes' ),
			'edit_item'     => __( 'Edit Recipe', 'recipes' ),
			'search_items'  => __( 'Search Recipes', 'recipes' ),
			'not_found'     => __( 'No recipes found', 'recipes' ),
			'new_item'      => __( 'New Recipe', 'recipes' ),
			'add_new_item'  => __( 'Add New Recipe', 'recipes' ),
			'view_item'     => __( 'View Recipe', 'recipes' ),
		);

		$args = array(
			'labels'              => $labels,
			'menu_icon'           => 'dashicons-list-view',
			'menu_position'       => 5,
			'public'              => true,
			'has_archive'         => $slug_archive,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'revisions' ),
			'rewrite'             => array(
				'slug'       => $slug,
				'with_front' => false,
			),
		);

		register_post_type( 'recipe', $args );
	}
}
add_action( 'init', 'mytheme_post_types' );
