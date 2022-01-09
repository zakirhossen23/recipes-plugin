<?php
/**
 * Meta-boxes.php
 *
 * CMB2 Meta Boxes.
 *
 * @package CMB2
 * @link https://github.com/WebDevStudios/CMB2
 */

// Defines prefix used for the custom fields.
define( 'PREFIX', 'custom_meta_' );

/**
 * Sets meta boxes for single recipes.
 */
function mytheme_custom_meta_single_recipe() {

	$cmb = new_cmb2_box( array(
		'id'           => PREFIX . 'single_recipe',
		'title'        => __( 'Recipe Details', 'recipes' ),
		'object_types' => array( 'recipe' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
		'closed'       => false, // True to keep the metabox closed by default.
	) );

	$cmb->add_field( array(
		'name'              => __( 'Prep Time', 'recipes' ),
		'desc'              => __( 'minutes', 'recipes' ),
		'id'                => PREFIX . 'prep_time',
		'type'              => 'text_small',
		'column'            => true,
		'rcps_form_display' => true,
		'attributes'        => array(
			'type' => 'number',
			'step' => '1',
			'min'  => '1',
			'size' => '3',
		),
	) );

	$cmb->add_field( array(
		'name'              => __( 'Cook Time', 'recipes' ),
		'desc'              => __( 'minutes', 'recipes' ),
		'id'                => PREFIX . 'cook_time',
		'type'              => 'text_small',
		'column'            => true,
		'rcps_form_display' => true,
		'attributes'        => array(
			'type' => 'number',
			'step' => '1',
			'min'  => '1',
			'size' => '3',
		),
	) );

	$cmb->add_field( array(
		'name' => __( 'Likes', 'recipes' ),
		'id'   => PREFIX . 'votes_up',
		'type' => 'text_small',
	) );

	$cmb->add_field( array(
		'name' => __( 'Dislikes', 'recipes' ),
		'id'   => PREFIX . 'votes_down',
		'type' => 'text_small',
	) );

	$cmb->add_field( array(
		'name'                  => __( 'External Recipe URL', 'recipes' ),
		'desc'                  => __( 'For example: http://www.recipe.com/delicious-cookies', 'recipes' ),
		'id'                    => PREFIX . 'external_url',
		'type'                  => 'textarea_small',
		'attributes'            => array(
			'rows' => '2',
		),
		'rcps_form_display'     => true,
		'rcps_form_label'       => __( 'Recipe Link', 'recipes' ),
		'rcps_form_extra_class' => 'rcps-wide',
	) );

	$cmb->add_field( array(
		'name'                  => __( 'External Recipe Site Title', 'recipes' ),
		'desc'                  => __( 'For example: Allrecipes', 'recipes' ),
		'id'                    => PREFIX . 'external_site',
		'type'                  => 'text_medium',
		'rcps_form_display'     => true,
		'rcps_form_label'       => __( 'Site Title', 'recipes' ),
		'rcps_form_extra_class' => 'rcps-wide',
	) );

	do_action( 'rcps_action_cmb2_box_single_recipe', $cmb );
}
add_action( 'cmb2_init', 'mytheme_custom_meta_single_recipe' );

/**
 * Registers video meta for posts and recipes.
 */
function mytheme_cmb2_box_video() {

	$cmb = new_cmb2_box( array(
		'id'           => 'cmb2_box_video',
		'title'        => __( 'Video', 'recipes' ),
		'object_types' => array( 'post', 'recipe' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
		'closed'       => false, // True to keep the metabox closed by default.
	) );

	$cmb->add_field( array(
		'name'                  => __( 'Video URL', 'recipes' ),
		'desc'                  => __( 'For example: https://www.youtube.com/watch?v=2kl3Liy5jcQ', 'recipes' ),
		'id'                    => '_rcps_meta_video_url',
		'type'                  => 'oembed',
		'column'                => true,
		'rcps_form_display'     => true,
		'rcps_form_extra_class' => 'rcps-wide',
	) );
}
add_action( 'cmb2_init', 'mytheme_cmb2_box_video' );

/**
 * Sets meta boxes for ingredient lists.
 */
function mytheme_custom_meta_single_recipe_ingredients() {

	$list_numbers = array( '', '2', '3', '4', '5' );

	foreach ( $list_numbers as $list_number ) {

		$ingredient_group = new_cmb2_box( array(
			'id'           => PREFIX . 'single_recipe_ingredients' . $list_number,
			'title'        => __( 'Ingredients', 'recipes' ) . ' ' . $list_number,
			'object_types' => array( 'recipe' ),
			'context'      => 'normal',
			'priority'     => 'high',
			'show_names'   => true,
			'closed'       => ( $list_number >= 1 ? true : false ),
		) );

		$ingredient_group->add_field( array(
			'name' => __( 'Title', 'recipes' ),
			'id'   => PREFIX . 'ingredient_group' . $list_number . '_title',
			'type' => 'text_medium',
		) );

		$group_field_id = $ingredient_group->add_field( array(
			'id'      => PREFIX . 'ingredient_group' . $list_number,
			'type'    => 'group',
			'options' => array(
				'group_title'   => __( 'Ingredient {#}', 'recipes' ),
				'add_button'    => __( 'Add Another Ingredient', 'recipes' ),
				'remove_button' => __( 'Remove Ingredient', 'recipes' ),
				'sortable'      => true,
			),
		) );

		$ingredient_group->add_group_field( $group_field_id, array(
			'name' => __( 'Ingredient', 'recipes' ),
			'id'   => 'ingredient',
			'type' => 'text',
		) );

		$ingredient_group->add_group_field( $group_field_id, array(
			'name' => __( 'Amount', 'recipes' ),
			'id'   => 'amount',
			'type' => 'text',
		) );
	}
}
add_action( 'cmb2_admin_init', 'mytheme_custom_meta_single_recipe_ingredients' );

/**
 * Sets meta boxes for nutrition facts.
 */
function rcps_cmb2_box_recipe_nutrition() {

	$cmb = new_cmb2_box( array(
		'id'           => 'rcps_cmb2_box_recipe_nutrition',
		'title'        => __( 'Nutrition Facts', 'recipes' ),
		'object_types' => array( 'recipe' ),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'closed'       => false,
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Servings', 'recipes' ),
		'id'                        => 'custom_meta_servings',
		'type'                      => 'text_small',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'servingSize',
		'attributes'                => array(
			'type' => 'number',
			'step' => '1',
			'min'  => '1',
			'size' => '3',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Calories in each serving', 'recipes' ),
		'id'                        => 'custom_meta_calories_in_serving',
		'type'                      => 'text_small',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'calories',
		'attributes'                => array(
			'type' => 'number',
			'step' => '1',
			'min'  => '1',
			'size' => '4',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Total Carbohydrate', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_carbohydrate',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'carbohydrateContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 275,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Cholesterol', 'recipes' ),
		'desc'                      => __( 'mg', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_cholesterol',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'cholesterolContent',
		'rcps_unit'                 => __( 'mg', 'recipes' ),
		'rcps_daily_value'          => 300,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Total Fat', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_total_fat',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'fatContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 78,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Saturated Fat', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_saturated_fat',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'saturatedFatContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 20,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Unsaturated Fat', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_unsaturated_fat',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'unsaturatedFatContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Trans Fat', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_trans_fat',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'transFatContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Dietary Fiber', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_fiber',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'fiberContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 28,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Protein', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_protein',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'proteinContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 50,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Sodium', 'recipes' ),
		'desc'                      => __( 'mg', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_sodium',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'sodiumContent',
		'rcps_unit'                 => __( 'mg', 'recipes' ),
		'rcps_daily_value'          => 2300,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	$cmb->add_field( array(
		'name'                      => __( 'Sugars', 'recipes' ),
		'desc'                      => __( 'g', 'recipes' ),
		'id'                        => '_rcps_meta_nutrient_sugars',
		'type'                      => 'text_small',
		'sanitization_cb'           => 'rcps_cmb2_sanitize_float_value',
		'rcps_form_display'         => true,
		'rcps_structured_data_name' => 'sugarContent',
		'rcps_unit'                 => __( 'g', 'recipes' ),
		'rcps_daily_value'          => 50,
		'attributes'                => array(
			'type' => 'number',
		),
	) );

	do_action( 'rcps_action_cmb2_box_recipe_nutrition', $cmb );
}
add_action( 'cmb2_init', 'rcps_cmb2_box_recipe_nutrition' );

/**
 * Sets meta boxes for user profile screen.
 */
function mytheme_custom_meta_user() {

	$cmb_user = new_cmb2_box( array(
		'id'               => 'user_edit',
		'title'            => __( 'User Profile Metabox', 'recipes' ),
		'object_types'     => array( 'user' ), // Tells CMB2 to use user_meta vs post_meta.
		'show_names'       => true,
		'new_user_section' => 'add-new-user', // Where form will show on new user page. 'add-existing-user' is only other valid option.
		'show_on'          => array(
			'key'   => 'user-type',
			'value' => 'publish_posts',
		),
	) );

	$cmb_user->add_field( array(
		'name'     => __( 'Social Links', 'recipes' ),
		'id'       => PREFIX . 'extra_info',
		'type'     => 'title',
		'on_front' => false,
	) );

	$cmb_user->add_field( array(
		'name' => __( 'Facebook URL', 'recipes' ),
		'id'   => PREFIX . 'facebookurl',
		'type' => 'text_url',
	) );

	$cmb_user->add_field( array(
		'name' => __( 'Twitter URL', 'recipes' ),
		'id'   => PREFIX . 'twitterurl',
		'type' => 'text_url',
	) );

	$cmb_user->add_field( array(
		'name' => __( 'Instagram URL', 'recipes' ),
		'id'   => PREFIX . 'instagramurl',
		'type' => 'text_url',
	) );

	$cmb_user->add_field( array(
		'name' => __( 'Pinterest URL', 'recipes' ),
		'id'   => PREFIX . 'pinteresturl',
		'type' => 'text_url',
	) );

	$cmb_user->add_field( array(
		'name' => __( 'YouTube URL', 'recipes' ),
		'id'   => PREFIX . 'youtubeurl',
		'type' => 'text_url',
	) );

	$cmb_user->add_field( array(
		'name' => __( 'Avatar', 'recipes' ),
		'desc' => __( 'Required size: 96px by 96px or larger', 'recipes' ),
		'id'   => PREFIX . 'avatar',
		'type' => 'file',
	) );

	$cmb_user->add_field( array(
		'name'   => __( 'Featured User', 'recipes' ),
		'id'     => '_rcps_meta_user_is_featured',
		'type'   => 'checkbox',
		'column' => true,
	) );
}
add_action( 'cmb2_admin_init', 'mytheme_custom_meta_user' );

/**
 * Registers meta fields for terms.
 */
function mytheme_cmb2_box_terms() {

	$cmb2 = new_cmb2_box( array(
		'id'           => 'cmb2_box_terms',
		'title'        => __( 'Term Meta', 'recipes' ),
		'object_types' => array( 'term' ),
		'taxonomies'   => get_object_taxonomies( 'recipe', 'names' ),
		'show_names'   => true,
	) );

	$cmb2->add_field( array(
		'name'   => __( 'Image', 'recipes' ),
		'desc'   => __( 'Required size: 1140px by 500px or larger.', 'recipes' ),
		'id'     => '_rcps_meta_term_image',
		'type'   => 'file',
		'column' => true,
	) );

	$cmb2->add_field( array(
		'name'   => __( 'Featured Term', 'recipes' ),
		'id'     => '_rcps_meta_term_is_featured',
		'type'   => 'checkbox',
		'column' => true,
	) );
}
add_action( 'cmb2_admin_init', 'mytheme_cmb2_box_terms' );

/**
 * Handles sanitization for float values. Allows using comma as a decimal separator.
 *
 * @param  mixed      $value      The unsanitized value from the form.
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 *
 * @return mixed                  Sanitized value to be stored.
 */
function rcps_cmb2_sanitize_float_value( $value, $field_args, $field ) {

	if ( '0' !== $value && empty( $value ) ) {
		return;
	}

	$sanitized_value = str_replace( ',', '.', $value );

	return floatval( $sanitized_value );
}

/**
 * Shows metabox only to users with specified capabilities.
 *
 * @param boolean $show     Show.
 * @param array   $meta_box Meta box.
 */
function rcps_show_metabox_to_chosen_user_types( $show, $meta_box ) {

	if ( ! isset( $meta_box['show_on']['key'], $meta_box['show_on']['value'] ) ) {
		return $show;
	}

	if ( 'user-type' !== $meta_box['show_on']['key'] ) {
		return $show;
	}

	// If the current user can publish posts show metaboxes (can be adjusted by capability).
	return current_user_can( $meta_box['show_on']['value'] );
}
add_filter( 'cmb2_show_on', 'rcps_show_metabox_to_chosen_user_types', 10, 2 );
