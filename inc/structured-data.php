<?php

/**
 * Plugin: Structured Data
 *
 * @package Recipes Plugin
 */

/**
 * Builds the structured data.
 */
function mytheme_get_structured_data()
{

	$structured_data = array();

	global $post;

	// Add only to single recipes.
	if (is_singular('recipe')) {

		$structured_data = array(
			'@context'      => 'http://schema.org/',
			'@type'         => 'Recipe',
			'name'          => get_the_title(),
			'url'           => get_permalink(),
			'datePublished' => get_the_date('c'),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta('display_name', $post->post_author),
			),
		);

		if (has_post_thumbnail()) {
			$structured_data['image'][] = get_the_post_thumbnail_url($post->ID, 'rcps-img-480x480'); // 1x1
			$structured_data['image'][] = get_the_post_thumbnail_url($post->ID, 'rcps-img-640x480'); // 4x3
			$structured_data['image'][] = get_the_post_thumbnail_url($post->ID, 'rcps-img-640x360'); // 16x9
		}

		// Servings.
		$servings = get_post_meta($post->ID, 'custom_meta_servings', true);
		if ($servings) {
			$structured_data['recipeYield'] = absint($servings);
		}

		// Mise It.
		$prep_time = absint(get_post_meta($post->ID, 'custom_meta_prep_time', true));
		if ($prep_time) {
			$structured_data['prepTime'] = 'PT' . absint($prep_time) . 'M';
		}

		// Cook time.
		$cook_time = absint(get_post_meta($post->ID, 'custom_meta_cook_time', true));
		if ($cook_time) {
			$structured_data['cookTime'] = 'PT' . absint($cook_time) . 'M';
		}

		// Total time.
		if ($prep_time + $cook_time > 0) {
			$structured_data['totalTime'] = 'PT' . absint($prep_time + $cook_time) . 'M';
		}

		// Course.
		$courses = get_the_terms($post->ID, 'course');
		if ($courses) {
			foreach ($courses as $course) {
				$structured_data['recipeCategory'][] = $course->name;
			}
		}

		// Cuisine.
		$cuisines = get_the_terms($post->ID, 'cuisine');
		if ($cuisines) {
			foreach ($cuisines as $cuisine) {
				$structured_data['recipeCuisine'][] = $cuisine->name;
			}
		}

		// Recipe tags.
		$recipe_tags = get_the_terms($post->ID, 'recipe-tag');
		if ($recipe_tags) {
			$structured_data['keywords'] = implode(', ', array_column($recipe_tags, 'name'));
		}

		// Shop it.
		$ingredient_lists = array('custom_meta_ingredient_group', 'custom_meta_ingredient_group2', 'custom_meta_ingredient_group3', 'custom_meta_ingredient_group4', 'custom_meta_ingredient_group5');

		foreach ($ingredient_lists as $ingredient_list) {
			$ingredients = get_post_meta($post->ID, $ingredient_list, true);

			if ($ingredients) {
				foreach ($ingredients as $ingredient) {
					$amount = '';
					if (!empty($ingredient['amount'])) {
						$amount = $ingredient['amount'] . ' ';
					}
					$structured_data['recipeIngredient'][] = $amount . $ingredient['ingredient'];
				}
			}
		}

		// Nutrition Facts.
		$nutrition_facts = rcps_get_nutrition_facts($post);

		if (!empty($nutrition_facts)) {
			$structured_data['nutrition']['@type'] = 'NutritionInformation';

			foreach ($nutrition_facts as $meta_key => $values) {
				if (!empty($values->name) && !empty($values->amount) && !empty($values->structured_data_name)) {
					if (!empty($values->unit)) {
						$value = esc_html($values->amount) . ' ' . esc_html($values->unit);
					} else {
						$value = absint($values->amount);
					}
					$structured_data['nutrition'][$values->structured_data_name] = $value;
				}
			}
		}

		// Rating.
		if (mytheme_get_option('enable_ratings')) {
			$votes_up      = $post->custom_meta_votes_up;
			$votes_down    = $post->custom_meta_votes_down;
			$votes_percent = $post->custom_meta_votes_percent;

			$votes_total = absint($votes_up) + absint($votes_down);

			if ($votes_total > 0 && $votes_percent > 0) {
				// Calculate 5 star rating from the percent value.
				$rating = $votes_percent / 20;
				$rating = round($rating, 1);

				$structured_data['AggregateRating'] = array(
					'@type'       => 'AggregateRating',
					'ratingValue' => esc_html($rating),
					'ratingCount' => absint($votes_total),
				);
			}
		}

		// Find ordered lists from the post content to be used as recipe instructions.
		if (!empty($post->post_content)) {
			if (strpos($post->post_content, '<ol>') !== false) {
				$doc = new DOMDocument();
				$doc->loadHTML(mb_convert_encoding($post->post_content, 'HTML-ENTITIES', 'UTF-8'));
				$ol_elements = $doc->getElementsByTagName('ol');

				if (!empty($ol_elements)) {
					$structured_data['recipeInstructions'] = array();

					foreach ($ol_elements as $ol) {
						$li_elements = $ol->getElementsByTagName('li');

						if (!empty($li_elements)) {
							foreach ($li_elements as $li) {
								$text = strip_shortcodes(wp_strip_all_tags($li->nodeValue)); // phpcs:ignore

								$structured_data['recipeInstructions'][] = [
									'@type' => 'HowToStep',
									'text'  => $text,
								];
							}
						}
					}
				}
			}

			$description = wp_kses_post(strip_shortcodes(wp_strip_all_tags($post->post_content)));

			// Shorten the post content to be used as description.
			if (strlen($description) >= 160) {
				$description = substr($description, 0, 150) . '...' . substr($description, -3);
			}
			$structured_data['description'] = $description;
		}
	}

	// Add ItemList if the post or page has a list of recipes.
	// https://developers.google.com/search/docs/data-types/recipe#carousel-example.
	if ((is_singular('post') || is_singular('page')) && has_shortcode($post->post_content, 'rcps_recipe_list')) {
		$post_ids = rcps_get_shortcode_attributes_from_string($post->post_content, 'rcps_recipe_list', 'recipe_ids');
		$term_ids = rcps_get_shortcode_attributes_from_string($post->post_content, 'rcps_recipe_list', 'collection_id');

		$posts = rcps_add_recipe_list_structured_data($post_ids, $term_ids);

		if (!empty($posts)) {
			$structured_data = array(
				'@context' => 'https://schema.org/',
				'@type'    => 'ItemList',
			);

			$structured_data['itemListElement'] = $posts;
		}
	}

	// Filter to allow adding structured data from elsewhere.
	$structured_data = apply_filters('mytheme_filter_structured_data', $structured_data, $post);

	return $structured_data;
}

/**
 * Adds structured data as JSON to the head.
 */
function mytheme_inline_structured_data_json()
{

	$structured_data = mytheme_get_structured_data();

	if (!empty($structured_data)) {
?>
		<script type="application/ld+json">
			<?php echo wp_json_encode($structured_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>
		</script>
<?php
	}
}
add_action('wp_head', 'mytheme_inline_structured_data_json', 1000);

/**
 * Removes hentry class from recipes, as they are already using schema.org for the structured data.
 *
 * @param  array $class Current classes.
 * @return array        Returned classes.
 */
function mytheme_remove_hentry_class($class)
{

	if (array_search('type-recipe', $class, true)) {
		$class = array_diff($class, array('hentry'));
	}
	return $class;
}
add_filter('post_class', 'mytheme_remove_hentry_class');

/**
 * Returns an array of posts to be used in the structured data.
 *
 * @param array $post_ids Post IDs.
 * @param array $term_ids Term IDs.
 *
 * @return array
 */
function rcps_add_recipe_list_structured_data($post_ids, $term_ids)
{

	if (empty($post_ids) && empty($term_ids)) {
		return false;
	}

	$return = false;

	$wp_query_args = array(
		'post_type'              => 'recipe',
		'posts_per_page'         => -1,
		'post_status'            => 'publish',
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'fields'                 => 'ids',
	);

	// Get the posts from the collections, and add to the array of post ids.
	if (!empty($term_ids)) {
		$wp_query_args['tax_query'] = array( // phpcs:ignore slow query ok.
			array(
				'taxonomy' => 'collection',
				'field'    => 'term_id',
				'terms'    => $term_ids,
			),
		);

		$wp_query_collection_post_ids = new WP_Query($wp_query_args);

		if ($wp_query_collection_post_ids->have_posts()) {
			$post_ids = array_merge($post_ids, $wp_query_collection_post_ids->posts);
		}
		wp_reset_postdata();

		// Remove the tax query, as we have the post ids now.
		unset($wp_query_args['tax_query']);
	}

	if (!empty($post_ids)) {
		$wp_query_args['post__in']               = $post_ids;
		$wp_query_args['posts_per_page']         = count($post_ids);
		$wp_query_args['fields']                 = 'all';
		$wp_query_args['update_post_term_cache'] = false;

		$wp_query_recipes = new WP_Query($wp_query_args);

		if ($wp_query_recipes->have_posts()) {
			$return = array();
			$x      = 1;

			while ($wp_query_recipes->have_posts()) {
				$wp_query_recipes->the_post();
				$return[] = array(
					'@type'    => 'ListItem',
					'position' => $x,
					'url'      => get_permalink(),
				);

				$x++;
			}
		}
		wp_reset_postdata();
	}

	return $return;
}

/**
 * Finds the shortcode attributes from string.
 *
 * @param  string $string       String to search from.
 * @param  string $shortcode    Shortcode name.
 * @param  string $attribute    Attribute name.
 *
 * @return array
 */
function rcps_get_shortcode_attributes_from_string($string, $shortcode, $attribute)
{

	$shortcode_regex = get_shortcode_regex();

	// Find the shortcodes from the post content.
	preg_match_all('/' . $shortcode_regex . '/s', $string, $shortcode_matches);

	if (empty($shortcode_matches)) {
		return array();
	}

	$return = array();

	foreach ($shortcode_matches[0] as $match => $value) {
		if (false !== strpos($value, '[' . $shortcode) && false !== strpos($value, $attribute)) {

			// Check if shortcode has another attribute.
			$pattern = '/\[' . $shortcode . '.*' . $attribute . '=(.*?)[a-z\]]/';

			preg_match($pattern, $value, $matches);

			if (!empty($matches)) {
				$values = str_replace('"', '', $matches[1]);
				$values = str_replace(' ', '', $values);
				$values = explode(',', $values);

				foreach ($values as $key => $value) {
					if (!is_numeric($value)) {
						unset($values[$key]);
					}
				}

				$return = array_merge($return, $values);
			}
		}
	}

	// Remove duplicates.
	$return = array_unique($return);

	// Convert values to integers.
	$return = array_map('intval', $return);

	return $return;
}
