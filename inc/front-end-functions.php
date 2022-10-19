<?php

/**
 * Plugin: Front-end Functions
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!function_exists('mytheme_remove_admin_bar')) {
	/**
	 * Disables toolbar for users.
	 */
	function mytheme_remove_admin_bar()
	{

		if (!current_user_can('administrator') && !is_admin()) {
			show_admin_bar(false);
		}
	}
}
add_action('after_setup_theme', 'mytheme_remove_admin_bar');

if (!function_exists('mytheme_author_slug')) {
	/**
	 * Renames /author slug to /profile.
	 */
	function mytheme_author_slug()
	{

		global $wp_rewrite;
		$author_slug = 'profile';
		$wp_rewrite->author_base = $author_slug;
	}
}
add_action('init', 'mytheme_author_slug');

if (!function_exists('mytheme_is_external_recipe')) {
	/**
	 * Checks if the recipe is from external site.
	 */
	function mytheme_is_external_recipe()
	{

		if (get_post_meta(get_the_ID(), 'custom_meta_external_url', true) && get_post_meta(get_the_ID(), 'custom_meta_external_site', true)) {
			return true;
		}
	}
}

if (function_exists('mytheme_get_option')) {
	if (mytheme_get_option('analytics') && !function_exists('mytheme_inline_script')) {
		/**
		 * Enqueues analytics script to the footer.
		 */
		function mytheme_inline_script()
		{

			echo mytheme_get_option('analytics');
		}
		add_action('wp_footer', 'mytheme_inline_script');
	}
}

if (!function_exists('mytheme_the_content_filter')) {
	/**
	 * Filters the content of recipes.
	 * If shortcodes are not used to add ingredient lists on the post, they are automatically added before the post content.
	 *
	 * @param  string $content Post content.
	 * @return string          Post content.
	 */
	function mytheme_the_content_filter($content)
	{

		if (is_singular('recipe')) {
			//Ingredeints
			ob_start();?>
			<div id="ShopIt">
				<div class="has-text-align-center has-large-font-size" >
					<div style="text-align: center;height: 73px;" >
						<button id="shopitprintbtn" onclick="shopitprint()" style="float: right; height: 45px; font-size: 26px; width: 90px;" class="">Print</button>
							<div>
								<img loading="lazy" id="shopitimage" style="width: 52px;height: 56px; vertical-align: middle;" alt="Mise It!" srcset="https://secureservercdn.net/198.71.233.213/ogy.39b.myftpupload.com/wp-content/uploads/2021/12/Mini-Cart.jpg" src="undefined"/>
								 Shop It!
							</div>				
						<style>.numbers {background: black;color: white;border-radius: 50%;width: 30px;align-items: center;text-align: center;font-size: 20px;font-family: calibri;vertical-align: middle;height: 30px;display: inline-block;}</style>
					</div>
				</div>
			<?php
			$html = ob_get_clean();
			$content = $content . $html;
			$ingredient_lists = '';
			for ($i = 1; $i <= 10; $i++) {
				$list_number = (1 === $i) ? '' : $i;

				if (!has_shortcode($content, 'ingredients' . $list_number) && !empty(get_post_meta(get_the_ID(), 'custom_meta_ingredient_group' . $list_number))) {
					$ingredient_lists .= do_shortcode('[ingredients' . $list_number . ']');
				}
			}

			$content =$content. $ingredient_lists ;

			//Mise it		
			$miseit_count = 0;
			$miseit_lists = '';
			for ($i = 1; $i <= 20; $i++) {
				$list_number = (1 === $i) ? '' : $i;
				
				if (!has_shortcode($content, 'miseits' . $list_number) && !empty(get_post_meta(get_the_ID(), 'custom_meta_miseit_group' . $list_number))) {
					$miseit_lists .= do_shortcode('[miseits' . $list_number . ']');
					$miseit_count++;
				}
			}
			ob_start();?>
			</div>
			</div>
			<?php
			$html = ob_get_clean();
			$content = $content . $html;
			if ($miseit_count !== 0){
				ob_start();?>
				<div style="text-align: center;font-size: 2.25em;height: 87px;" id="MiseIt"><button id="miseitprintbtn" onclick="miseitprint()" style="float: right;height: 45px;font-size: 26px;width: 90px;" class="">Print</button><div><img loading="lazy" id="miseitimage" style="width: 52px;height: 56px; vertical-align: middle;" alt="Mise It!" srcset="https://shopitmiseitmakeit.ca/wp-content/uploads/2021/12/Mini-Bowl-2.jpg " src="undefined"> <span>Mise It!</span></div></div>
				<?php
				$html = ob_get_clean();
				$content = $content . $html;
			}
			
			$content = $content . $miseit_lists;


			if (mytheme_is_external_recipe()) {
				ob_start(); ?>
				<p><a href="<?php echo esc_url(get_post_meta(get_the_ID(), 'custom_meta_external_url', true)); ?>" class="rcps-btn" target="_blank" rel="noopener"><?php esc_html_e('View the recipe at', 'recipes'); ?> <b><?php echo esc_html(get_post_meta(get_the_ID(), 'custom_meta_external_site', true)); ?></b></a></p>

<?php
				$html = ob_get_clean();

				$content = $content . $html;
			}
		}

		// Embed video if available and the shortcode is not used to insert the video.
		if ((is_singular('post') || is_singular('recipe')) && !has_shortcode($content, 'rcps_video')) {
			$video_url = get_post_meta(get_the_ID(), '_rcps_meta_video_url', true);
			if (!empty($video_url)) {
				$content = wp_oembed_get($video_url) . $content;
			}
		}

		return $content;
	}
}
add_filter('the_content', 'mytheme_the_content_filter');

if (!function_exists('mytheme_the_content_filter_nutrition_facts')) {
	/**
	 * Filters the content of recipes.
	 * Adds nutrition facts after the post content.
	 *
	 * @param  string $content Post content.
	 * @return string          Post content.
	 */
	function mytheme_the_content_filter_nutrition_facts($content)
	{

		if (is_singular('recipe')) {
			global $post;

			$nutrition_facts = rcps_get_nutrition_facts($post, $exclude = array('custom_meta_servings', 'custom_meta_calories_in_serving'));

			if (!empty($nutrition_facts)) {
				ob_start();
				get_template_part('templates/template', 'nutrition-facts');
				$nutrition_table = ob_get_clean();

				$content = $content . $nutrition_table;
			}

			if (!empty($post->custom_meta_prep_time) || !empty($post->custom_meta_cook_time)) {
				ob_start();
				get_template_part('templates/template', 'recipe-meta');
				$recipe_meta = ob_get_clean();

				$content = $content . $recipe_meta;
			}
		}

		return $content;
	}
}
add_filter('the_content', 'mytheme_the_content_filter_nutrition_facts');

/**
 * Handles the data submitted from the user settings form.
 *
 * @return array
 */
function rcps_update_user_data()
{

	$current_user_id = get_current_user_id();
	$form_post       = array();
	$error           = array();

	// Continue if profile was saved and nonce validates.
	$nonce = filter_input(INPUT_POST, 'nonce_update_user');

	if (!empty($nonce) && wp_verify_nonce($nonce, 'update_user_action')) {

		$form_post = $_POST;

		// Update user email.
		if (get_the_author_meta('user_email', $current_user_id) !== $form_post['email']) {
			if (!empty($form_post['email'])) {
				if (!is_email(esc_attr($form_post['email']))) {
					$error[] = __('The email you entered is not valid. Please try again.', 'recipes');
				} elseif (email_exists(esc_attr($form_post['email']))) {
					$error[] = __('The email you entered is already used by another user. Please try a different one.', 'recipes');
				} else {
					wp_update_user(array(
						'ID'         => $current_user_id,
						'user_email' => esc_attr($form_post['email']),
					));
				}
			}
		}

		// Profile image validation.
		if (count($_FILES['rcps_avatar']) && ($_FILES['rcps_avatar']['size'] > 0)) {
			$file = $_FILES['rcps_avatar'];

			list($width, $height, $type, $attr) = getimagesize($file['tmp_name']);
			if ($width < 96 || $height < 96) {
				$error[] = __('Image is too small (required size: 96px by 96px or larger)', 'recipes');
			}

			if (is_numeric(RCPS_MAX_IMAGE_FILESIZE) && $file['size'] > (RCPS_MAX_IMAGE_FILESIZE * 1024 * 1024)) {
				// Translators: %s is a file size in megabytes.
				$error[] = sprintf(__('Image file size is too big (max file size: %s megabytes)', 'recipes'), absint(RCPS_MAX_IMAGE_FILESIZE));
				unlink($file['tmp_name']);
			}

			$arr_file_type      = wp_check_filetype(basename($file['name']));
			$uploaded_file_type = $arr_file_type['type'];

			if (!in_array($uploaded_file_type, array('image/jpg', 'image/jpeg', 'image/png'), true)) {
				$error[] = __('Please upload a JPG or PNG file', 'recipes');
			}
		}

		// Update user's display name.
		if (!empty($form_post['display_name'])) {
			wp_update_user(array(
				'ID'           => $current_user_id,
				'display_name' => esc_attr($form_post['display_name']),
			));
		}

		// Update user's url.
		if (!empty($form_post['url']) && filter_var($form_post['url'], FILTER_VALIDATE_URL) !== false) {
			wp_update_user(array(
				'ID'       => $current_user_id,
				'user_url' => esc_attr($form_post['url']),
			));
		} else {
			wp_update_user(array(
				'ID'       => $current_user_id,
				'user_url' => '',
			));
		}

		// Update user's social profiles.
		$social_profiles = array('twitterurl', 'facebookurl', 'instagramurl', 'pinteresturl', 'youtubeurl');

		foreach ($social_profiles as $social_profile) {
			if (!empty($form_post[$social_profile]) && filter_var($form_post[$social_profile], FILTER_VALIDATE_URL) !== false) {
				update_user_meta($current_user_id, 'custom_meta_' . $social_profile, esc_attr($form_post[$social_profile]));
			} else {
				delete_user_meta($current_user_id, 'custom_meta_' . $social_profile);
			}
		}

		// Update user's biographical info.
		if (!empty($form_post['description'])) {
			update_user_meta($current_user_id, 'description', wp_kses_post($form_post['description']));
		} else {
			delete_user_meta($current_user_id, 'description');
		}

		// Insert profile picture.
		if (count($error) === 0 && !empty($_FILES) && $_FILES['rcps_avatar']) {
			$attachment_id = mytheme_insert_attachment('rcps_avatar', 0);
			if (!is_wp_error($attachment_id)) {
				update_user_meta($current_user_id, 'custom_meta_avatar', wp_get_attachment_image_src($attachment_id, 'img-280')[0]);
				update_user_meta($current_user_id, 'custom_meta_avatar_id', $attachment_id);
			}
		}
	} elseif (!empty($_POST['nonce_update_user'])) {
		$error[] = __('Nonce did not validate.', 'recipes');
	}

	return array(
		'form_post' => $form_post,
		'error'     => $error,
	);
}
