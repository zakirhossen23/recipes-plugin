<?php
/**
 * Plugin: Admin Functions
 *
 * @package Recipes Plugin
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! function_exists( 'mytheme_recipe_posts_columns' ) ) {
	/**
	 * Adds columns to the custom post type listing.
	 *
	 * @param  string $column  The name of the column to display.
	 * @param  int    $post_id The ID of the current post.
	 */
	function mytheme_recipe_posts_columns( $column, $post_id ) {

		switch ( $column ) {
			case 'thumbnail':
				echo get_the_post_thumbnail( $post_id );
				break;

			case 'rating':
				echo absint( round( get_post_meta( $post_id, 'custom_meta_votes_percent', true ), 0 ) ) . ' %';
				break;
		}
	}
}
add_action( 'manage_recipe_posts_custom_column', 'mytheme_recipe_posts_columns', 10, 2 );

if ( ! function_exists( 'mytheme_add_recipe_columns' ) ) {
	/**
	 * Adds columns to the custom post type listing.
	 *
	 * @param  array $columns An array of column name and label.
	 */
	function mytheme_add_recipe_columns( $columns ) {

		unset( $columns['post_type'] );
		return array_merge(
			$columns,
			array(
				'rating'    => __( 'Rating', 'recipes' ),
				'thumbnail' => __( 'Thumbnail', 'recipes' ),
			)
		);
	}
}
add_filter( 'manage_recipe_posts_columns', 'mytheme_add_recipe_columns' );

if ( ! function_exists( 'mytheme_sortable_recipe_columns' ) ) {
	/**
	 * Makes custom columns sortable.
	 *
	 * @param  array $columns Columns.
	 * @return array          Columns.
	 */
	function mytheme_sortable_recipe_columns( $columns ) {

		$columns['rating'] = 'rating';
		$columns['views']  = 'views';

		return $columns;
	}
}
add_filter( 'manage_edit-recipe_sortable_columns', 'mytheme_sortable_recipe_columns' );

if ( ! function_exists( 'mytheme_posts_columnsorderby' ) ) {
	/**
	 * Defines sorting arguments for custom columns.
	 *
	 * @param  object $query WP_Query.
	 */
	function mytheme_posts_columnsorderby( $query ) {

		if ( ! is_admin() ) {
			return;
		}

		$orderby = $query->get( 'orderby' );
		if ( 'rating' === $orderby ) {
			$query->set( 'meta_key', 'custom_meta_votes_percent' );
			$query->set( 'orderby', 'meta_value_num' );
		}
	}
}
add_action( 'pre_get_posts', 'mytheme_posts_columnsorderby' );

if ( ! function_exists( 'recipes_plugin_activate' ) ) {
	/**
	 * Runs functions on plugin activation.
	 */
	function recipes_plugin_activate() {
		mytheme_set_options_for_favorites();
	}
}

if ( ! function_exists( 'mytheme_set_options_for_favorites' ) ) {
	/**
	 * Sets the options for Favorites plugin.
	 */
	function mytheme_set_options_for_favorites() {

		$favorites_options_display      = get_option( 'simplefavorites_display' );
		$favorites_options_dependencies = get_option( 'simplefavorites_dependencies' );
		$favorites_options_users        = get_option( 'simplefavorites_users' );

		$favorites_options_display['buttontext'] = '<svg class="rcps-icon rcps-icon-heart"><use xlink:href="' . esc_url( get_template_directory_uri() ) . '/images/icons.svg#icon-heart"/></svg>';

		$favorites_options_display['buttontextfavorited'] = '<svg class="rcps-icon rcps-icon-heart rcps-icon-heart-favorited"><use xlink:href="' . esc_url( get_template_directory_uri() ) . '/images/icons.svg#icon-heart"/></svg>';

		$favorites_options_display['posttypes']       = array(
			'recipe' => array(
				'display' => 'true',
			),
		);
		$favorites_options_display['buttoncount']     = 'true';
		$favorites_options_dependencies['css']        = 'false';
		$favorites_options_dependencies['js']         = 'true';
		$favorites_options_users['anonymous']['save'] = 'false';

		update_option( 'simplefavorites_display', $favorites_options_display );
		update_option( 'simplefavorites_dependencies', $favorites_options_dependencies );
		update_option( 'simplefavorites_users', $favorites_options_users );
	}
}

if ( ! function_exists( 'mytheme_admin_notice_save_new_options' ) ) {
	/**
	 * Displays admin notice if options are not saved.
	 * Unsaved default options may cause errors.
	 */
	function mytheme_admin_notice_save_new_options() {

		// Get the name of the current active theme.
		$theme = wp_get_theme();

		$theme_name = $theme->get( 'Name' );

		if ( ! mytheme_get_option( 'options_saved' ) && ( 'Recipes' === $theme_name || 'Recipes Child' === $theme_name ) ) {
			?>
			<div class="notice notice-error">
				<p>Please save the options to finish the installation of Recipes. Go to <a href="<?php echo esc_url( admin_url( 'admin.php?page=rcps_options' ) ); ?>">Recipes Options</a> and click <strong>Save Changes</strong>.</p>
			</div>
			<?php
		}
	}
}
add_action( 'admin_notices', 'mytheme_admin_notice_save_new_options' );

if ( ! function_exists( 'mytheme_get_option' ) ) {
	/**
	 * Wrapper function around cmb2_get_option.
	 *
	 * @param  string $key     Options array key.
	 * @param  mixed  $default Optional default value.
	 * @return mixed           Option value.
	 */
	function mytheme_get_option( $key = '', $default = false ) {

		if ( function_exists( 'cmb2_get_option' ) ) {
			return cmb2_get_option( 'rcps_options', $key, $default );
		}

		// Fallback to get_option if CMB2 is not loaded yet.
		$opts = get_option( 'rcps_options', $default );
		$val  = $default;
		if ( 'all' === $key ) {
			$val = $opts;
		} elseif ( is_array( $opts ) && array_key_exists( $key, $opts ) && false !== $opts[ $key ] ) {
			$val = $opts[ $key ];
		}
		return $val;
	}
}

/**
 * Ajax action to refresh selected image on widget options.
 */
function rcps_get_widget_image() {

	$id               = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
	$image_element_id = filter_input( INPUT_GET, 'image_element_id', FILTER_VALIDATE_INT );

	if ( isset( $id ) && isset( $image_element_id ) ) {
		$image = wp_get_attachment_image(
			$id,
			'medium',
			false,
			array(
				'id'    => esc_attr( $image_element_id ),
				'class' => 'preview_image',
			)
		);

		$data = array(
			'image' => $image,
		);
		wp_send_json_success( $data );
	} else {
		wp_send_json_error();
	}
}
add_action( 'wp_ajax_rcps_get_widget_image', 'rcps_get_widget_image' );

/**
 * Adds recipe count column to dashboard's Users page.
 *
 * @param array $cols Columns.
 *
 * @return array
 */
function rcps_manage_users_columns( $cols ) {
	$cols['rcps_recipes'] = esc_html__( 'Recipes', 'recipes' );
	return $cols;
}
add_filter( 'manage_users_columns', 'rcps_manage_users_columns' );

/**
 * Outputs the recipe count for dashboard's Users page.
 *
 * @param string $output      Custom column output. Default empty.
 * @param string $column_name Column name.
 * @param int    $id          ID of the currently-listed user.
 *
 * @return string
 */
function rcps_manage_users_custom_column( $output, $column_name, $id ) {

	if ( 'rcps_recipes' === $column_name ) {
		$count = count_user_posts( $id, 'recipe' );

		$link = '<a href="' . admin_url() . 'edit.php?post_type=recipe&author=' . $id . '">' . $count . '</a>';

		$return = ( $count > 0 ) ? $link : $count;

		return $return;
	}
}
add_filter( 'manage_users_custom_column', 'rcps_manage_users_custom_column', 10, 3 );
