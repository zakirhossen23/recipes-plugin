<?php

/**
 * Plugin: Front-end Recipe Actions
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!function_exists('mytheme_transform_post_to_form')) {
	/**
	 * Transforms WP Post object to an array compatible with the front-end form.
	 *
	 * @param  int $edit_recipe_id Recipe post id.
	 * @return array       [description]
	 */
	function mytheme_transform_post_to_form($edit_recipe_id)
	{

		$recipe = get_post($edit_recipe_id);

		if (empty($recipe)) {
			return false;
		}

		// Check if the recipe has been already tried to edit.
		// If so, use the data from the transient, not from the original recipe.
		$transient = get_transient('rcps_form_edit_' . $edit_recipe_id);

		if (empty($transient)) {
			$form['recipe_title']  = $recipe->post_title;
			$form['description']   = $recipe->post_content;
			$form['attachment_id'] = get_post_thumbnail_id($edit_recipe_id);
		} elseif (!empty($transient)) {
			$form['recipe_title']  = $transient['recipe_title'];
			$form['description']   = $transient['description'];
			$form['attachment_id'] = $transient['attachment_id'];
		}

		$form['form_type'] = 'submit_form_recipe';

		// Meta fields.
		$form_fields_data = rcps_get_form_fields_data();

		foreach ($form_fields_data as $key => $values) {
			if (in_array($key, array('recipe_title', 'recipe-tag'), true)) {
				continue;
			}

			if (empty($transient)) {
				$form['meta'][$key] = get_post_meta($edit_recipe_id, $key, true);
			} elseif (!empty($transient) && !empty($transient['meta']) && !empty($transient['meta'][$key])) {
				$form['meta'][$key] = $transient['meta'][$key];
			}
		}

		// Check if an external recipe.
		if (!empty($form['meta']['custom_meta_external_url']) || !empty($form['meta']['custom_meta_external_site'])) {
			$form['form_type'] = 'submit_form_url';
		}

		// Taxonomies.
		$taxonomies = get_object_taxonomies('recipe', 'names');
		foreach ($taxonomies as $taxonomy) {

			if (empty($transient)) {
				$terms = get_the_terms($edit_recipe_id, $taxonomy);

				if (!empty($terms)) {
					if ('recipe-tag' === $taxonomy) {
						$form['tax']['recipe-tag'] = implode(', ', array_column($terms, 'name'));
						continue;
					} else {
						$form['tax'][$taxonomy] = wp_list_pluck($terms, 'slug');
					}
				}
			} elseif (!empty($transient) && !empty($transient['tax']) && !empty($transient['tax'][$taxonomy])) {
				$terms = $transient['tax'][$taxonomy];

				if (!empty($terms)) {
					if ('recipe-tag' === $taxonomy) {
						$form['tax']['recipe-tag'] = implode(', ', $terms);
						continue;
					} else {
						$form['tax'][$taxonomy] = $terms;
					}
				}
			}
		}

		for ($i = 1; $i <= 10; $i++) {
			$list_number = (1 === $i) ? '' : $i;

			$ingredients       = null;
			$ingredients_title = null;

			if (empty($transient)) {
				$ingredients       = get_post_meta($edit_recipe_id, 'custom_meta_ingredient_group' . $list_number, true);
				$ingredients_title = get_post_meta($edit_recipe_id, 'custom_meta_ingredient_group' . $list_number . '_title', true);
			} elseif (!empty($transient)) {
				if (!empty($transient['ingredient_lists'][$i]['ingredients'])) {
					$ingredients = $transient['ingredient_lists'][$i]['ingredients'];
				}

				if (!empty($transient['ingredient_lists'][$i]['ingredients_title'])) {
					$ingredients_title = $transient['ingredient_lists'][$i]['ingredients_title'];
				}
			}

			if (!empty($ingredients)) {
				$form['ingredient_lists'][$i]['ingredients'] = $ingredients;
			}

			if (!empty($ingredients_title)) {
				$form['ingredient_lists'][$i]['ingredients_title'] = $ingredients_title;
			}
		}


		for ($i = 1; $i <= 20; $i++) {
			$list_number = (1 === $i) ? '' : $i;

			$items       = null;
			$miseits_title = null;

			if (empty($items)) {
				$items       = get_post_meta($edit_recipe_id, 'custom_meta_miseit_group' . $list_number, true);
				$miseits_title = get_post_meta($edit_recipe_id, 'custom_meta_miseit_group' . $list_number . '_title', true);
			} elseif (!empty($transient)) {
				if (!empty($transient['miseit_lists'][$i]['ingredient'])) {
					$items = $transient['miseit_lists'][$i]['ingredient'];
				}

				if (!empty($transient['miseit_lists'][$i]['miseit_title'])) {
					$miseit_title = $transient['miseit_lists'][$i]['miseits_title'];
				}
			}

			if (!empty($items)) {
				$form['miseit_lists'][$i]['ingredient'] = $items;
			}

			if (!empty($miseit_title)) {
				$form['miseit_lists'][$i]['miseit_title'] = $miseits_title;
			}
		}

		return $form;
	}
}

if (!function_exists('mytheme_delete_recipe')) {
	/**
	 * Handles recipe deleting redirections.
	 *
	 * @param  string $template The path of the template to include.
	 * @return string           Template.
	 */
	function mytheme_delete_recipe($template)
	{

		// Delete recipe.
		if (is_user_logged_in() && is_singular('recipe') && get_query_var('rcps_delete_recipe_id') && (int) get_query_var('rcps_delete_recipe_id') === get_the_ID() && (int) get_query_var('rcps_delete_recipe_confirm') === get_the_ID()) {
			global $post;

			if (get_current_user_id() === (int) $post->post_author) {

				$post_to_delete = get_post(get_query_var('rcps_delete_recipe_id'));
				if (!empty($post_to_delete)) {

					// Move to trash.
					$deleted_post = wp_trash_post($post_to_delete->ID);

					// If the recipe was successfully moved to trash, redirect to user profile.
					if (false !== $deleted_post) {
						wp_safe_redirect(get_author_posts_url(get_current_user_id()));
						exit;
					}
				}
			}
		}

		return $template;
	}
}
add_filter('template_include', 'mytheme_delete_recipe');

if (!function_exists('mytheme_get_front_end_forms')) {
	/**
	 * Returns object for the front-end edit and submit forms.
	 *
	 * @return [type] [description]
	 */
	function mytheme_get_front_end_forms()
	{

		$return = new stdClass();

		$edit_recipe_id = get_query_var('rcps_edit_recipe_id');

		if (!empty($edit_recipe_id)) {
			$return->form           = mytheme_transform_post_to_form($edit_recipe_id);
			$return->form_type      = $return->form['form_type'];
			$return->errors         = get_transient('rcps_errors_edit_recipe_' . $edit_recipe_id);
			$return->edit_recipe_id = $edit_recipe_id;
		} else {
			$return->form      = get_transient('rcps_form_' . mytheme_get_user_key());
			$return->form_type = $return->form['form_type'];
			$return->errors    = get_transient('rcps_errors_' . mytheme_get_user_key());

			$return->tabs = array(
				'1' => array(
					'name'    => 'submit_form_recipe',
					'title'   => __('Your own recipe', 'recipes'),
					'show_on' => array('0', '1'),
				),
				'2' => array(
					'name'    => 'submit_form_url',
					'title'   => __('Recipe from another site', 'recipes'),
					'show_on' => array('1', '2'),
				),
			);
		}

		return $return;
	}
}
