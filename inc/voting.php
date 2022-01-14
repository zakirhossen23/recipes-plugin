<?php

/**
 * Voting.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Vote post.
 */
function rcps_ajax_post_vote()
{

	// Verify security nonce.
	$nonce = filter_input(INPUT_POST, 'nonce');
	if (empty($nonce) || false === wp_verify_nonce($nonce, 'ajax-nonce')) {
		wp_die(esc_html__('Nonce did not validate.', 'recipes'));
	}

	if (!empty($_POST['post_id']) && !empty($_POST['vote_type'])) {

		$post_id   = filter_input(INPUT_POST, 'post_id');
		$vote_type = filter_input(INPUT_POST, 'vote_type');
		$meta_key  = 'custom_meta_votes_' . $vote_type;

		// Retrieve user's IP address.
		$user_ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');

		// Get votes count for the current post.
		$meta_count = get_post_meta($post_id, $meta_key, true);

		// Returns up or down. Or false if has not voted before.
		$current_vote = mytheme_what_voted($post_id);

		// User has already voted.
		if (false !== $current_vote) {

			// Trying to vote for the same as before.
			if ($current_vote === $vote_type) {
				wp_die('-1');
			} elseif ($current_vote !== $vote_type) {
				$meta_count_up   = get_post_meta($post_id, 'custom_meta_votes_up', true);
				$meta_count_down = get_post_meta($post_id, 'custom_meta_votes_down', true);

				// Check the vote type, and update meta values.
				if ('up' === $current_vote) {
					update_post_meta($post_id, 'custom_meta_votes_up', --$meta_count_up);
					update_post_meta($post_id, 'custom_meta_votes_down', ++$meta_count_down);
					$meta_count = $meta_count_down;
				} elseif ('down' === $current_vote) {
					update_post_meta($post_id, 'custom_meta_votes_up', ++$meta_count_up);
					update_post_meta($post_id, 'custom_meta_votes_down', --$meta_count_down);
					$meta_count = $meta_count_up;
				}
			}
		} elseif (false === $current_vote) {

			// Save vote count meta for the vote type.
			update_post_meta($post_id, $meta_key, ++$meta_count);
		}

		$vote = array();

		$vote[$user_ip] = array(
			'time'  => time(),
			'voted' => $vote_type,
		);

		// Save vote meta, and vote counts.
		update_post_meta($post_id, 'custom_meta_votes', $vote);

		// Update percent.
		mytheme_update_percent($post_id);

		// Echo count (ie JavaScript return value).
		echo absint($meta_count);
	}

	wp_die();
}
add_action('wp_ajax_nopriv_rcps_post_vote', 'rcps_ajax_post_vote');
add_action('wp_ajax_rcps_post_vote', 'rcps_ajax_post_vote');

/**
 * Returns AJAX response to set up voting buttons, rating etc.
 */
function rcps_ajax_voting_setup()
{

	// Verify security nonce.
	$nonce = filter_input(INPUT_POST, 'nonce');
	if (empty($nonce) || false === wp_verify_nonce($nonce, 'ajax-nonce')) {
		wp_die(esc_html__('Nonce did not validate.', 'recipes'));
	}

	if (!empty($_POST['post_id'])) {
		$post_id = filter_input(INPUT_POST, 'post_id');
	}

	$up_votes = 0;
	$meta_count_up = get_post_meta($post_id, 'custom_meta_votes_up', true);
	if ($meta_count_up > 0) {
		$up_votes = $meta_count_up;
	}

	$down_votes = 0;
	$meta_count_down = get_post_meta($post_id, 'custom_meta_votes_down', true);
	if ($meta_count_down > 0) {
		$down_votes = $meta_count_down;
	}

	$votes_percent   = get_post_meta($post_id, 'custom_meta_votes_percent', true);
	$votes_percent   = round($votes_percent, 0);

	$width = rcps_calculate_star_width($votes_percent);

	$return = array(
		'what_voted'         => mytheme_what_voted($post_id),
		'up_votes'           => $up_votes,
		'down_votes'         => $down_votes,
		'percent'            => $votes_percent,
		'rating_stars_width' => $width,
	);

	echo wp_json_encode($return);

	wp_die();
}
add_action('wp_ajax_nopriv_rcps_voting_setup', 'rcps_ajax_voting_setup');
add_action('wp_ajax_rcps_voting_setup', 'rcps_ajax_voting_setup');

/**
 * Updates vote percent meta data.
 *
 * @param  int $post_id Post ID.
 */
function mytheme_update_percent($post_id)
{

	$votes_up = get_post_meta($post_id, 'custom_meta_votes_up', true);
	if (empty($votes_up)) {
		$votes_up = 0;
	}

	$votes_down = get_post_meta($post_id, 'custom_meta_votes_down', true);
	if (empty($votes_down)) {
		$votes_down = 0;
	}

	$newpercent = (100 * ($votes_up / ($votes_up + $votes_down)));
	update_post_meta($post_id, 'custom_meta_votes_percent', $newpercent);
}

/**
 * Saves post metadata when a post is saved.
 *
 * @param  int $post_id Post ID.
 */
function mytheme_save_post_meta($post_id)
{

	if (isset($_REQUEST['custom_meta_votes_up'])) {
		$votes_up = absint($_REQUEST['custom_meta_votes_up']);
	} else {
		$votes_up = get_post_meta($post_id, 'custom_meta_votes_up', true);
	}

	if (isset($_REQUEST['custom_meta_votes_down'])) {
		$votes_down = absint($_REQUEST['custom_meta_votes_down']);
	} else {
		$votes_down = get_post_meta($post_id, 'custom_meta_votes_down', true);
	}

	if ($votes_up || $votes_down) {
		$newpercent = (100 * ($votes_up / ($votes_up + $votes_down)));
		update_post_meta($post_id, 'custom_meta_votes_percent', $newpercent);
	} else {
		update_post_meta($post_id, 'custom_meta_votes_percent', '0');
	}

	if (isset($_REQUEST['custom_meta_prep_time'])) {
		$prep_time = absint($_REQUEST['custom_meta_prep_time']);
	} else {
		$prep_time = absint(get_post_meta($post_id, 'custom_meta_prep_time', true));
	}

	if (isset($_REQUEST['custom_meta_cook_time'])) {
		$cook_time = absint($_REQUEST['custom_meta_cook_time']);
	} else {
		$cook_time = absint(get_post_meta($post_id, 'custom_meta_cook_time', true));
	}

	$total_time = ($prep_time + $cook_time);
	if ($total_time > 0) {
		update_post_meta($post_id, 'custom_meta_total_time', $total_time);
	}

	$ingredient_lists = array('custom_meta_ingredient_group', 'custom_meta_ingredient_group2', 'custom_meta_ingredient_group3', 'custom_meta_ingredient_group4', 'custom_meta_ingredient_group5', 'custom_meta_ingredient_group6', 'custom_meta_ingredient_group7', 'custom_meta_ingredient_group8', 'custom_meta_ingredient_group9', 'custom_meta_ingredient_group10');

	$ingredient_count = 0;

	foreach ($ingredient_lists as $ingredient_list) {

		$list = null;

		if (!empty($_REQUEST[$ingredient_list])) {
			$list = $_REQUEST[$ingredient_list];
		} elseif (!empty(get_post_meta($post_id, $ingredient_list, true))) {
			$list = get_post_meta($post_id, $ingredient_list, true);
		}

		if (!empty($list) && is_array($list)) {
			$ingredient_list = array_filter($list);

			foreach ($ingredient_list as $ingredient) {
				if (!empty($ingredient['ingredient'])) {
					$ingredient_count++;
				}
			}
		}
	}

	update_post_meta($post_id, 'custom_meta_ingredients_number', $ingredient_count);
}
add_action('save_post_recipe', 'mytheme_save_post_meta');

/**
 * Checks what the user has voted.
 *
 * @param  int $post_id Post ID.
 */
function mytheme_what_voted($post_id)
{

	// Get current user's IP.
	$ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');

	// Get all votes for the post.
	$votes = get_post_meta($post_id, 'custom_meta_votes', true);

	// Check that post has votes.
	if (is_array($votes)) {
		// Check that the voted value for the user's IP is not empty.
		if (!empty($votes[$ip]['voted'])) {
			$voted = $votes[$ip]['voted'];
			return $voted;
		}
	}

	return false;
}

if (!function_exists('mytheme_the_post_vote_buttons')) {
	/**
	 * Displays the post voting links on single posts.
	 *
	 * @param  int $post_id Post ID.
	 */
	function mytheme_the_post_vote_buttons($post_id)
	{

		$args = array(
			'up'   => __('Like', 'recipes'),
			'down' => __('Dislike', 'recipes'),
		);
?>

		<div class="rcps-vote-box" data-post-id="<?php echo absint($post_id); ?>" data-has-voted="false">
			<?php foreach ($args as $key => $title) : ?>
				<button type="button" class="rcps-btn rcps-mr05" data-vote-type="<?php echo esc_attr($key); ?>" data-js-action="rcps-vote" disabled>
					<?php echo esc_html($title); ?>
					<b class="rcps-text-smaller" data-vote-count="rcps-vote-count" data-vote-count-type="rcps-vote-count-<?php echo esc_attr($key); ?>">0</b>
				</button>
			<?php endforeach; ?>
		</div><!-- .rcps-vote-box -->
	<?php
	}
}

if (!function_exists('mytheme_votes_percent')) {
	/**
	 * Displays rating value.
	 *
	 * NOTE: On single recipes, the rating is hidden initially, and then revealed with JavaScript.
	 *
	 * @param  array $args Arguments.
	 */
	function mytheme_votes_percent($args)
	{

		if (empty($args) || empty($args['location'])) {
			return;
		}

		$location = $args['location'];

		$votes_percent = get_post_meta(get_the_ID(), 'custom_meta_votes_percent', true);

		// Hide on recipe cards if the rating is 0 or empty.
		if ('card' === $location && (0 === absint($votes_percent) || empty($votes_percent))) {
			return;
		}

		$rating = round($votes_percent, 0);
	?>

		<div class="rcps-rating-stars rcps-rating-stars-<?php echo sanitize_html_class($location); ?> <?php echo ('single' === $location ? ' rcps-rating-stars-hidden' : ''); ?>">
			<?php rcps_display_stars($rating); ?>

			<?php if ('single' === $location) : ?>
				<div class="rcps-rating-value"><?php echo esc_html($rating) . '%'; ?></div>
			<?php endif; ?>
		</div>

	<?php
	}
}

if (!function_exists('rcps_display_stars')) {
	/**
	 * Displays rating in 5 stars SVG icon.
	 *
	 * @param  string $rating Rating in 0-100 scale.
	 */
	function rcps_display_stars($rating)
	{

		// Check that the rating value is numeric.
		if (!is_numeric($rating)) {
			return;
		}

		$width = rcps_calculate_star_width($rating);
	?>

		<div class="rcps-5-stars-rating" style="width:<?php echo absint($width); ?>px;" title="<?php printf(esc_html_x('Rating: %s%%', 'Title attribute of star rating. %s%%: rating percentage.', 'recipes'), round($rating, 0)); ?>">
			<svg class="rcps-icon rcps-icon-5-stars">
				<use xlink:href="<?php echo esc_url(get_template_directory_uri()); ?>/images/icons.svg#icon-5-stars" />
			</svg>
		</div>

<?php
	}
}

if (!function_exists('rcps_calculate_star_width')) {
	/**
	 * Returns width for the star rating.
	 *
	 * @param  string $rating Rating in 0-100 scale.
	 */
	function rcps_calculate_star_width($rating)
	{

		// Calculate icon width from the rating percent. 70 is the SVGs full width in pixels.
		$width = $rating / 100 * 70;

		// Round to the nearest multiple of 7 to display only full and half stars.
		$width = ceil($width / 7) * 7;

		if ($width > 1) {
			$width = $width - 1;
		}

		return $width;
	}
}
