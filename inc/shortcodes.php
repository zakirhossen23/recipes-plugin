<?php

/**
 * Plugin: Shortcodes
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Adds hooks for shortcode tags.
 */
add_shortcode('ingredients', 'mytheme_ingredient_list_shortcode');
add_shortcode('ingredients2', 'mytheme_ingredient_list_shortcode');
add_shortcode('ingredients3', 'mytheme_ingredient_list_shortcode');
add_shortcode('ingredients4', 'mytheme_ingredient_list_shortcode');
add_shortcode('ingredients5', 'mytheme_ingredient_list_shortcode');

if (!function_exists('mytheme_ingredient_list_shortcode')) {
	/**
	 * Runs the callback function to build the ingredient list based on the shortcode number.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            Ingredient list HTML.
	 */
	function mytheme_ingredient_list_shortcode($atts, $content = null, $shortcode)
	{

		if ($shortcode) {

			// Gets the ingredient list number from the shortcode.
			$list_number = str_replace('ingredients', '', $shortcode);

			// Returns the ingredient list HTML.
			return mytheme_ingredient_list($list_number);
		}
	}
}

if (!function_exists('mytheme_ingredient_list')) {
	/**
	 * Outputs the ingredient list HTML from the shortcode.
	 *
	 * @param  int $list_number Number of the ingredient list shortcode.
	 */
	function mytheme_ingredient_list($list_number = null)
	{

		$ingredients = get_post_meta(get_the_ID(), 'custom_meta_ingredient_group' . $list_number, true);
		$title       = get_post_meta(get_the_ID(), 'custom_meta_ingredient_group' . $list_number . '_title', true);

		if (is_array($ingredients) && !empty($ingredients[0])) {

			if (empty($title)) {
				$title = __('Shop it', 'recipes');
			}

			// Turn on output buffering.
			ob_start();
?>

			<h2><?php echo esc_html($title); ?></h2>

			<table class="rcps-table-ingredients">
				<?php
				foreach ($ingredients as $key => $entry) {
					$ingredient = '';
					$amount     = '';

					if (!empty($entry['ingredient'])) {
						$ingredient = $entry['ingredient'];
						if (is_rtl()) {
							$ingredient = '&#8207;' . $ingredient;
						}
					}

					if (!empty($entry['amount'])) {
						$amount = $entry['amount'];
					}

					if (!empty($entry['ingredient'])) {
				?>
						<tr class="rcps-ingredient-checkable">
							<td><?php echo wp_kses_post($ingredient); ?>
							<td>
							<td><?php echo wp_kses_post($amount); ?></td>
						</tr>
				<?php
					}
				}
				?>
			</table>
		<?php

			// Gets current buffer contents and deletes current output buffer.
			return ob_get_clean();
		}
	}
}

if (!function_exists('mytheme_shortcode_rcps_recipe_listing')) {
	/**
	 * Returns HTML for the recipe list shortcode.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_recipe_listing($atts, $content = null, $shortcode)
	{

		$wp_query_args = array(
			'post_type'              => 'recipe',
			'posts_per_page'         => 99,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
		);

		if (!empty($atts['recipe_ids'])) {
			$wp_query_args['post__in'] = explode(',', $atts['recipe_ids']);
		}

		if (!empty($atts['collection_id'])) {
			$wp_query_args['tax_query'] = array( // phpcs:ignore slow query ok.
				array(
					'taxonomy' => 'collection',
					'field'    => 'term_id',
					'terms'    => $atts['collection_id'],
				),
			);
		}

		$wp_query_collection = new WP_Query($wp_query_args);

		// Turn on output buffering.
		ob_start();
		?>

		<?php if ($wp_query_collection->have_posts()) : ?>
			<div class="rcps-recipe-grid">
				<?php while ($wp_query_collection->have_posts()) : ?>
					<?php $wp_query_collection->the_post(); ?>
					<?php get_template_part('templates/template', 'recipe'); ?>
				<?php endwhile; ?>
			</div><!-- .rcps-recipe-grid -->
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>

	<?php
		// Gets current buffer contents and deletes current output buffer.
		return ob_get_clean();
	}
}
add_shortcode('rcps_recipe_list', 'mytheme_shortcode_rcps_recipe_listing');

if (!function_exists('mytheme_shortcode_rcps_video')) {
	/**
	 * Embeds video.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_video($atts, $content = null, $shortcode)
	{

		// Return video embed if available.
		$video_url = get_post_meta(get_the_ID(), '_rcps_meta_video_url', true);
		if (!empty($video_url)) {
			$video = wp_oembed_get($video_url);
			return '<div class="rcps-video-embed">' . $video . '</div>';
		}
	}
}
add_shortcode('rcps_video', 'mytheme_shortcode_rcps_video');

if (!function_exists('mytheme_shortcode_rcps_member_directory')) {
	/**
	 * Member directory shortcode.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_member_directory($atts, $content = null, $shortcode)
	{

		$users = mytheme_get_members();

		if (empty($users)) {
			return;
		}

		// Turn on output buffering.
		ob_start();
	?>
		<div class="rcps-recipe-grid">
			<?php
			foreach ($users as $user) {
				global $list_user;
				$list_user = get_userdata($user['ID']);
				get_template_part('templates/template', 'user');
			}
			?>
		</div><!-- .rcps-recipe-grid -->

		<?php
		// Gets current buffer contents and deletes current output buffer.
		return ob_get_clean();
	}
}
add_shortcode('rcps_member_directory', 'mytheme_shortcode_rcps_member_directory');

if (!function_exists('mytheme_shortcode_rcps_submit_recipe')) {
	/**
	 * Recipe submit form shortcode.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_submit_recipe($atts, $content = null, $shortcode)
	{

		$forms = mytheme_get_front_end_forms();
		if ($forms) {
			// Turn on output buffering.
			ob_start();
		?>

			<?php if (!is_user_logged_in() && mytheme_get_option('submissions_only_for_registered')) : ?>
				<p class="rcps-alert rcps-alert-yellow"><?php esc_html_e('You must be logged in to submit a recipe.', 'recipes'); ?></p>
			<?php else : ?>

				<?php if (empty($forms->errors) && empty($forms->form_type) && !empty(get_query_var('success')) && 'true' === get_query_var('success')) : ?>
					<p class="rcps-alert rcps-alert-green"><?php esc_html_e('Thank you! Your recipe is awaiting moderation.', 'recipes'); ?></p>
				<?php endif; ?>

				<?php if ('1' === mytheme_get_option('allowed_submission_types') && (!mytheme_get_option('submissions_only_for_registered') || is_user_logged_in())) : ?>
					<ul class="rcps-tabs-nav rcps-tabs-nav-submit">
						<?php foreach ($forms->tabs as $key => $value) : ?>
							<li class="<?php echo (!empty($forms->form_type) && $value['name'] === $forms->form_type ? 'rcps-tabs-nav-active' : ''); ?>"><a href="#rcps-tab-submit-<?php echo absint($key); ?>"><?php echo esc_html($value['title']); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if (!empty($forms->errors)) : ?>
					<?php foreach ($forms->errors as $error) : ?>
						<p class="rcps-alert rcps-alert-red"><?php echo esc_html($error); ?></p>
					<?php endforeach; ?>
				<?php endif; ?>

				<div class="rcps-tabs-container">
					<?php foreach ($forms->tabs as $key => $value) : ?>

						<?php if (in_array(mytheme_get_option('allowed_submission_types', '1'), $value['show_on'], true)) : ?>
							<div id="rcps-tab-submit-<?php echo esc_attr($key); ?>" class="rcps-tab-submit" <?php echo (!empty($forms->form_type) && $value['name'] !== $forms->form_type ? 'style="display:none;"' : ''); ?>>
								<form name="new_post" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="rcps-form" enctype="multipart/form-data">
									<?php mytheme_build_form($value['name']); ?>

									<input type="hidden" name="form_type" value="<?php echo esc_attr($value['name']); ?>">
									<input type="hidden" name="action" value="rcps_submit_form">
									<?php wp_nonce_field('new_post_action', 'nonce_new_post'); ?>

									<input class="rcps-form-submit" type="submit" value="<?php esc_html_e('Submit Recipe', 'recipes'); ?>" name="submit" id="submit">
								</form>
							</div>
						<?php endif; ?>

					<?php endforeach; ?>
				</div><!-- .rcps-tabs-container -->
			<?php endif; ?>

		<?php
			// Gets current buffer contents and deletes current output buffer.
			return ob_get_clean();
		}
	}
}
add_shortcode('rcps_submit_recipe', 'mytheme_shortcode_rcps_submit_recipe');

if (!function_exists('mytheme_shortcode_rcps_edit_recipe')) {
	/**
	 * Recipe edit form shortcode.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_edit_recipe($atts, $content = null, $shortcode)
	{

		$forms = mytheme_get_front_end_forms();
		if ($forms) {
			// Turn on output buffering.
			ob_start();
		?>

			<?php if (!is_user_logged_in()) : ?>
				<p class="rcps-alert rcps-alert-yellow"><?php esc_html_e('You must be logged in to edit your recipes.', 'recipes'); ?></p>

			<?php elseif (empty($forms->edit_recipe_id) || (is_user_logged_in() && get_current_user_id() !== (int) get_post_field('post_author', $forms->edit_recipe_id))) : ?>
				<p class="rcps-alert rcps-alert-yellow"><?php esc_html_e('You are not the author of this recipe.', 'recipes'); ?></p>

				<?php
				$args = array(
					'post_type'      => 'recipe',
					'posts_per_page' => -1,
					'posts_status'   => 'publish',
					'author'         => get_current_user_id(),
					'orderby'        => 'title',
					'order'          => 'ASC',
				);

				$wp_query_user_recipes = new WP_Query($args);
				?>

				<?php if ($wp_query_user_recipes->have_posts()) : ?>
					<h3><?php esc_html_e('Choose a recipe to edit', 'recipes'); ?></h3>

					<ul>
						<?php while ($wp_query_user_recipes->have_posts()) : ?>
							<?php $wp_query_user_recipes->the_post(); ?>
							<li><a href="<?php echo esc_url(add_query_arg('rcps_edit_recipe_id', get_the_ID())); ?>"><?php the_title(); ?></a></li>
						<?php endwhile; ?>
					</ul>

				<?php endif; ?>
				<?php wp_reset_postdata(); ?>

			<?php else : ?>

				<?php if (!empty($forms->errors)) : ?>
					<?php foreach ($forms->errors as $error) : ?>
						<p class="rcps-alert rcps-alert-red"><?php echo esc_html($error); ?></p>
					<?php endforeach; ?>
				<?php endif; ?>

				<?php if (!empty(get_query_var('success')) && 'true' === get_query_var('success')) : ?>
					<p class="rcps-alert rcps-alert-green"><?php esc_html_e('Recipe updated.', 'recipes'); ?> <a href="<?php the_permalink(get_query_var('rcps_edit_recipe_id')); ?>">See recipe</a></p>
				<?php endif; ?>

				<form name="new_post" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="rcps-form" enctype="multipart/form-data">
					<?php mytheme_build_form($forms->form['form_type']); ?>

					<input type="hidden" name="form_type" value="<?php echo esc_attr($forms->form['form_type']); ?>">
					<input type="hidden" name="action" value="rcps_submit_form">
					<?php wp_nonce_field('new_post_action', 'nonce_new_post'); ?>

					<input type="hidden" name="edit_recipe_id" value="<?php echo absint($forms->edit_recipe_id); ?>">
					<input class="rcps-form-submit" type="submit" value="<?php esc_html_e('Update Recipe', 'recipes'); ?>" name="submit" id="submit">
				</form>

			<?php endif; ?>

		<?php
			// Gets current buffer contents and deletes current output buffer.
			return ob_get_clean();
		}
	}
}
add_shortcode('rcps_edit_recipe', 'mytheme_shortcode_rcps_edit_recipe');

if (!function_exists('mytheme_shortcode_rcps_user_settings')) {
	/**
	 * User settings shortcode.
	 *
	 * @param  array  $atts      Shortcode attributes.
	 * @param  string $content   Shortcode content.
	 * @param  string $shortcode Name of the shortcode.
	 * @return string            HTML.
	 */
	function mytheme_shortcode_rcps_user_settings($atts, $content = null, $shortcode)
	{

		$current_user_id = get_current_user_id();

		$update_user_data = rcps_update_user_data();

		$form_post = $update_user_data['form_post'];
		$error     = $update_user_data['error'];

		// Turn on output buffering.
		ob_start();
		?>

		<?php if (!is_user_logged_in()) : ?>
			<p class="rcps-alert rcps-alert-yellow"><?php esc_html_e('You must be logged in to edit your profile.', 'recipes'); ?></p>

		<?php elseif (is_user_logged_in()) : ?>
			<?php if (!empty($error) && is_array($error)) : ?>
				<?php foreach ($error as $error_message) : ?>
					<p class="rcps-alert rcps-alert-red"><?php echo esc_html($error_message); ?></p>
				<?php endforeach; ?>
			<?php endif; ?>

			<?php if (!empty($form_post['nonce_update_user']) && count($error) === 0) : ?>
				<p class="rcps-alert rcps-alert-green"><?php esc_html_e('Thanks, your settings have been saved.', 'recipes'); ?></p>
			<?php endif; ?>

			<form method="post" class="rcps-form" action="<?php the_permalink(); ?>" enctype="multipart/form-data">
				<fieldset class="rcps-fieldset">
					<label class="rcps-label"><?php esc_html_e('Upload profile picture', 'recipes'); ?></label>

					<input name="rcps_avatar" type="file" accept="image/*" class="rcps-text-input rcps-input-wide">
					<p class="rcps-form-description">
						<?php esc_html_e('Required size: 96px by 96px or larger.', 'recipes'); ?>
						<?php // Translators: %s is file size in megabytes. 
						?>
						<?php (is_numeric(RCPS_MAX_IMAGE_FILESIZE) ? printf(esc_html__('Max file size: %s megabytes', 'recipes'), absint(RCPS_MAX_IMAGE_FILESIZE)) : ''); ?>
					</p>
				</fieldset>

				<?php
				$form_fields = array(
					'display_name' => array(__('Display Name', 'recipes'), 'display_name'),
					'email'        => array(__('Email', 'recipes'), 'user_email'),
					'url'          => array(__('Website', 'recipes'), 'user_url'),
					'twitterurl'   => array(__('Twitter URL', 'recipes'), 'custom_meta_twitterurl'),
					'facebookurl'  => array(__('Facebook URL', 'recipes'), 'custom_meta_facebookurl'),
					'instagramurl' => array(__('Instagram URL', 'recipes'), 'custom_meta_instagramurl'),
					'pinteresturl' => array(__('Pinterest URL', 'recipes'), 'custom_meta_pinteresturl'),
					'youtubeurl'   => array(__('YouTube URL', 'recipes'), 'custom_meta_youtubeurl'),
				);
				?>

				<?php foreach ($form_fields as $key => $value) : ?>
					<fieldset class="rcps-fieldset">
						<label class="rcps-label"><?php echo esc_html($value[0]); ?></label>
						<input class="rcps-text-input" name="<?php echo esc_attr($key); ?>" type="text" value="<?php the_author_meta($value[1], $current_user_id); ?>">
					</fieldset>
				<?php endforeach; ?>

				<fieldset class="rcps-fieldset">
					<label class="rcps-label"><?php esc_html_e('Biographical Info', 'recipes'); ?></label>
					<textarea class="rcps-textarea rcps-wide" name="description" rows="4" cols="50"><?php the_author_meta('description', $current_user_id); ?></textarea>
				</fieldset>

				<?php wp_nonce_field('update_user_action', 'nonce_update_user'); ?>

				<input class="rcps-form-submit" name="updateuser" type="submit" id="submit" value="<?php esc_html_e('Update Profile', 'recipes'); ?>">

				<p class="alignright"><a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="rcps-btn"><?php esc_html_e('Change Password', 'recipes'); ?></a></p>
			</form>
		<?php endif; ?>

<?php
		// Gets current buffer contents and deletes current output buffer.
		return ob_get_clean();
	}
}
add_shortcode('rcps_user_settings', 'mytheme_shortcode_rcps_user_settings');
