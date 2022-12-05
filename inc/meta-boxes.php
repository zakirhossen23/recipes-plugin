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
define('PREFIX', 'custom_meta_');



/**
 * Sets meta boxes for ingredient lists.
 */
function mytheme_custom_meta_single_recipe_ingredients()
{

	$list_numbers = array('', '2', '3', '4', '5', '6', '7', '8', '9', '10');

	foreach ($list_numbers as $list_number) {

		$ingredient_group = new_cmb2_box(array(
			'id'           => PREFIX . 'single_recipe_ingredients' . $list_number,
			'title'        => __('Shop it', 'recipes') . ' ' . $list_number,
			'object_types' => array('recipe'),
			'context'      => 'normal',
			'priority'     => 'default',
			'show_names'   => true,
			'closed'       => ($list_number >= 1 ? true : false),
		));

		$ingredient_group->add_field(array(
			'name' => __('Title', 'recipes'),
			'id'   => PREFIX . 'ingredient_group' . $list_number . '_title',
			'type' => 'text_medium',
		));

		$group_field_id = $ingredient_group->add_field(array(
			'id'      => PREFIX . 'ingredient_group' . $list_number,
			'type'    => 'group',
			'options' => array(
				'group_title'   => __('Ingredient {#}', 'recipes'),
				'add_button'    => __('Add Another Ingredient', 'recipes'),
				'remove_button' => __('Remove Ingredient', 'recipes'),
				'sortable'      => true,
			),
		));

		$ingredient_group->add_group_field($group_field_id, array(
			'name' => __('Ingredient', 'recipes'),
			'id'   => 'ingredient',
			'type' => 'text',
		));

		$ingredient_group->add_group_field($group_field_id, array(
			'name' => __('Cost', 'recipes'),
			'id'   => 'amount',
			'type' => 'text_money',
		));
		$ingredient_group->add_group_field($group_field_id, array(
			'name' => __('Overbuy', 'recipes'),
			'id'   => 'overbuy',
			'type' => 'checkbox',
		));
	}
}
add_action('cmb2_admin_init', 'mytheme_custom_meta_single_recipe_ingredients');



/**
 * Sets meta boxes for Mise it lists.
 */
function mytheme_custom_meta_single_recipe_miseit()
{

	$list_numbers = array('', '2', '3', '4', '5', '6', '7', '8', '9', '10','11','12','13','14','15','16','17','18','19','20');

	foreach ($list_numbers as $list_number) {

		$miseit_group = new_cmb2_box(array(
			'id'           => PREFIX . 'single_recipe_miseits' . $list_number,
			'title'        => __('Mise it', 'recipes') . ' ' . $list_number,
			'object_types' => array('recipe'),
			'context'      => 'normal',
			'priority'     => 'default',
			'show_names'   => true,
			'closed'       => ($list_number >= 1 ? true : false),
		));

		$miseit_group->add_field(array(
			'name' => __('Mise It Name', 'recipes'),
			'id'   => PREFIX . 'miseit_group' . $list_number . '_title',
			'type' => 'text_medium',
		));

		$group_field_id = $miseit_group->add_field(array(
			'id'      => PREFIX . 'miseit_group' . $list_number,
			'type'    => 'group',
			'options' => array(
				'group_title'   => __('Mise It Item {#}', 'recipes'),
				'add_button'    => __('Add Another Mise It Item', 'recipes'),
				'remove_button' => __('Remove Mise It Item', 'recipes'),
				'sortable'      => true,
			),
		));

		$miseit_group->add_group_field($group_field_id, array(
			'name' => __('Ingredient and Amount', 'recipes'),
			'id'   => 'ingredient',
			'type' => 'text',
		));

		$miseit_group->add_group_field($group_field_id, array(
			'name' => __('Instructions', 'recipes'),
			'id'   => 'instruction',
			'type' => 'textarea_small',
		));

	}
}
add_action('cmb2_admin_init', 'mytheme_custom_meta_single_recipe_miseit');


/**
 * Sets meta boxes for Make it lists.
 */
function mytheme_custom_meta_single_recipe_makeit()
{
	
	$makeit_tools_group = new_cmb2_box(array(
		'id'           => PREFIX . 'single_recipe_makeits',
		'title'        => __('Make it Tools', 'recipes') ,
		'object_types' => array('recipe'),
		'context'      => 'normal',
		'priority'     => 'default',
		'show_names'   => true,
		'closed'       => true ,
	));


	$makeit_tools_group->add_field(array(
		'name' => __('Make It Tools', 'recipes'),
		'id'   => PREFIX . 'makeit_tools_group'. '_tools',
		'type' => 'textarea_small',
	));


	$list_numbers = array('', '2', '3', '4', '5');

	// $list_numbers = array('', '2', '3', '4', '5', '6', '7', '8', '9', '10','11','12','13','14','15','16','17','18','19','20','21','22','23','24','25','26','27','28','29','30','31','32','33','34','35','36','37','38','39','40','41','42','43','44','45','46','47','48','49','50');

	foreach ($list_numbers as $list_number) {
		$makeit_group = new_cmb2_box(array(
			'id'           => PREFIX . 'single_recipe__group_makeits' . $list_number,
			'title'        => __('Make it Group', 'recipes') . ' '. $list_number ,
			'object_types' => array('recipe'),
			'context'      => 'normal',
			'priority'     => 'default',
			'show_names'   => true,
			'closed'       => true ,
		));
	
		
		
		$makeit_group->add_field(array(
			'name' => __('Group Name', 'recipes'),
			'id'   => PREFIX . 'makeit_group'. $list_number. '_name',
			'type' => 'text',
		));
		$group_item_field_id = $makeit_group->add_field(array(
		'id'      => PREFIX . 'makeit_group_item' . $list_number,
		'type'    => 'group',
		'options' => array(
			'group_title'   => __('Item {#}', 'recipes'),
			'add_button'    => __('Add Another Item', 'recipes'),
			'remove_button' => __('Remove Item', 'recipes'),
			
		)
		));
		$makeit_group->add_group_field($group_item_field_id, array(
			'name' => __('Do', 'recipes'),
			'id'   => 'do',
			'type' => 'text',
		));
		$makeit_group->add_group_field($group_item_field_id, array(
			'name' => __('With', 'recipes'),
			'id'   =>'with',
			'type' => 'textarea_small',
		));
		$makeit_group->add_group_field($group_item_field_id, array(
		'name' => __('How', 'recipes'),
		'id'   =>'how',
		'type' => 'text',
		));
		$makeit_group->add_group_field($group_item_field_id, array(
			'name' => __('Important', 'recipes'),
			'id'   =>'important',
			'type' => 'text',
		));
	}

}
add_action('cmb2_admin_init', 'mytheme_custom_meta_single_recipe_makeit');



/**
 * Registers video meta for posts and recipes.
 */
function mytheme_cmb2_box_video()
{

	$cmb = new_cmb2_box(array(
		'id'           => 'cmb2_box_video',
		'title'        => __('Video', 'recipes'),
		'object_types' => array('post', 'recipe'),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
		'closed'       => false, // True to keep the metabox closed by default.
	));

	$cmb->add_field(array(
		'name'                  => __('Video URL', 'recipes'),
		'desc'                  => __('For example: https://www.youtube.com/watch?v=2kl3Liy5jcQ', 'recipes'),
		'id'                    => '_rcps_meta_video_url',
		'type'                  => 'oembed',
		'column'                => true,
		'rcps_form_display'     => true,
		'rcps_form_extra_class' => 'rcps-wide',
	));
}
add_action('cmb2_init', 'mytheme_cmb2_box_video');



/**
 * Sets meta boxes for nutrition facts.
 */
function rcps_cmb2_box_recipe_nutrition()
{

	$cmb = new_cmb2_box(array(
		'id'           => 'rcps_cmb2_box_recipe_nutrition',
		'title'        => __('Nutrition Facts', 'recipes'),
		'object_types' => array('recipe'),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true,
		'closed'       => false,
	));

	$cmb->add_field(array(
		'name'                      => __('Servings', 'recipes'),
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
	));

	do_action('rcps_action_cmb2_box_recipe_nutrition', $cmb);
}
add_action('cmb2_init', 'rcps_cmb2_box_recipe_nutrition');

/**
 * Sets meta boxes for user profile screen.
 */
function mytheme_custom_meta_user()
{

	$cmb_user = new_cmb2_box(array(
		'id'               => 'user_edit',
		'title'            => __('User Profile Metabox', 'recipes'),
		'object_types'     => array('user'), // Tells CMB2 to use user_meta vs post_meta.
		'show_names'       => true,
		'new_user_section' => 'add-new-user', // Where form will show on new user page. 'add-existing-user' is only other valid option.
		'show_on'          => array(
			'key'   => 'user-type',
			'value' => 'publish_posts',
		),
	));

	$cmb_user->add_field(array(
		'name'     => __('Social Links', 'recipes'),
		'id'       => PREFIX . 'extra_info',
		'type'     => 'title',
		'on_front' => false,
	));

	$cmb_user->add_field(array(
		'name' => __('Facebook URL', 'recipes'),
		'id'   => PREFIX . 'facebookurl',
		'type' => 'text_url',
	));

	$cmb_user->add_field(array(
		'name' => __('Twitter URL', 'recipes'),
		'id'   => PREFIX . 'twitterurl',
		'type' => 'text_url',
	));

	$cmb_user->add_field(array(
		'name' => __('Instagram URL', 'recipes'),
		'id'   => PREFIX . 'instagramurl',
		'type' => 'text_url',
	));

	$cmb_user->add_field(array(
		'name' => __('Pinterest URL', 'recipes'),
		'id'   => PREFIX . 'pinteresturl',
		'type' => 'text_url',
	));

	$cmb_user->add_field(array(
		'name' => __('YouTube URL', 'recipes'),
		'id'   => PREFIX . 'youtubeurl',
		'type' => 'text_url',
	));

	$cmb_user->add_field(array(
		'name' => __('Avatar', 'recipes'),
		'desc' => __('Required size: 96px by 96px or larger', 'recipes'),
		'id'   => PREFIX . 'avatar',
		'type' => 'file',
	));

	$cmb_user->add_field(array(
		'name'   => __('Featured User', 'recipes'),
		'id'     => '_rcps_meta_user_is_featured',
		'type'   => 'checkbox',
		'column' => true,
	));
}
add_action('cmb2_admin_init', 'mytheme_custom_meta_user');

/**
 * Registers meta fields for terms.
 */
function mytheme_cmb2_box_terms()
{

	$cmb2 = new_cmb2_box(array(
		'id'           => 'cmb2_box_terms',
		'title'        => __('Term Meta', 'recipes'),
		'object_types' => array('term'),
		'taxonomies'   => get_object_taxonomies('recipe', 'names'),
		'show_names'   => true,
	));

	$cmb2->add_field(array(
		'name'   => __('Image', 'recipes'),
		'desc'   => __('Required size: 1140px by 500px or larger.', 'recipes'),
		'id'     => '_rcps_meta_term_image',
		'type'   => 'file',
		'column' => true,
	));

	$cmb2->add_field(array(
		'name'   => __('Featured Term', 'recipes'),
		'id'     => '_rcps_meta_term_is_featured',
		'type'   => 'checkbox',
		'column' => true,
	));
}
add_action('cmb2_admin_init', 'mytheme_cmb2_box_terms');

/**
 * Handles sanitization for float values. Aldefaults using comma as a decimal separator.
 *
 * @param  mixed      $value      The unsanitized value from the form.
 * @param  array      $field_args Array of field arguments.
 * @param  CMB2_Field $field      The field object.
 *
 * @return mixed                  Sanitized value to be stored.
 */
function rcps_cmb2_sanitize_float_value($value, $field_args, $field)
{

	if ('0' !== $value && empty($value)) {
		return;
	}

	$sanitized_value = str_replace(',', '.', $value);

	return floatval($sanitized_value);
}

/**
 * Shows metabox only to users with specified capabilities.
 *
 * @param boolean $show     Show.
 * @param array   $meta_box Meta box.
 */
function rcps_show_metabox_to_chosen_user_types($show, $meta_box)
{

	if (!isset($meta_box['show_on']['key'], $meta_box['show_on']['value'])) {
		return $show;
	}

	if ('user-type' !== $meta_box['show_on']['key']) {
		return $show;
	}

	// If the current user can publish posts show metaboxes (can be adjusted by capability).
	return current_user_can($meta_box['show_on']['value']);
}
add_filter('cmb2_show_on', 'rcps_show_metabox_to_chosen_user_types', 10, 2);

/**
 * Sets meta boxes for single recipes.
 */
function mytheme_custom_meta_single_recipe()
{

	$cmb = new_cmb2_box(array(
		'id'           => PREFIX . 'single_recipe',
		'title'        => __('Recipe Details', 'recipes'),
		'object_types' => array('recipe'),
		'context'      => 'normal',
		'priority'     => 'high',
		'show_names'   => true, // Show field names on the left.
		'closed'       => false, // True to keep the metabox closed by default.
	));



	$cmb->add_field(array(
		'name'                  => __('Recipe Source', 'recipes'),
		'desc'                  => __('For example: https://www.cookscountry.com/recipes/......', 'recipes'),
		'id'                    => 'source_url',
		'type'                  => 'textarea_small',
		'attributes'            => array(
			'rows' => '2',
		),
		'rcps_form_display'     => true,
		'rcps_form_label'       => __('Recipe Source Link', 'recipes'),
		'rcps_form_extra_class' => 'rcps-wide',
	));

	$cmb->add_field(array(
		'name'              => __('Mise It', 'recipes'),
		'desc'              => __('number', 'recipes'),
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
	));

	$cmb->add_field(array(
		'name'              => __('Make it', 'recipes'),
		'desc'              => __('number', 'recipes'),
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
	));

	$cmb->add_field(array(
		'name' => __('Likes', 'recipes'),
		'id'   => PREFIX . 'votes_up',
		'type' => 'text_small',
	));

	$cmb->add_field(array(
		'name' => __('Dislikes', 'recipes'),
		'id'   => PREFIX . 'votes_down',
		'type' => 'text_small',
	));

	$cmb->add_field(array(
		'name'                  => __('External Recipe URL', 'recipes'),
		'desc'                  => __('For example: http://www.recipe.com/delicious-cookies', 'recipes'),
		'id'                    => PREFIX . 'external_url',
		'type'                  => 'textarea_small',
		'attributes'            => array(
			'rows' => '2',
		),
		'rcps_form_display'     => true,
		'rcps_form_label'       => __('Recipe Link', 'recipes'),
		'rcps_form_extra_class' => 'rcps-wide',
	));

	$cmb->add_field(array(
		'name'                  => __('External Recipe Site Title', 'recipes'),
		'desc'                  => __('For example: Allrecipes', 'recipes'),
		'id'                    => PREFIX . 'external_site',
		'type'                  => 'text_medium',
		'rcps_form_display'     => true,
		'rcps_form_label'       => __('Site Title', 'recipes'),
		'rcps_form_extra_class' => 'rcps-wide',
	));

	do_action('rcps_action_cmb2_box_single_recipe', $cmb);
}
add_action('cmb2_init', 'mytheme_custom_meta_single_recipe');
