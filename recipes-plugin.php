<?php
/**
 * Plugin Name: Recipes Plugin
 * Plugin URI: https://themeforest.net/item/recipes-wordpress-theme/9258994?ref=mytheme
 * Description: Plugin required by <a href="https://themeforest.net/item/recipes-wordpress-theme/9258994?ref=mytheme">Recipes WordPress Theme</a>.
 * Version: 3.16.0
 * Author: myTheme
 * Author URI: https://themeforest.net/user/mytheme/portfolio?ref=mytheme
 * Text Domain: recipes
 * License: GNU General Public License
 * License URI: license.txt
 *
 * @package Recipes WordPress Theme
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Defines plugin version.
 */
define( 'RCPS_PLUGIN_VERSION', '3.16.0' );

define( 'RCPS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Sets a constant for the plugin URL.
 */
add_action( 'init', function() {
	define( 'RCPS_PLUGIN_BASE_PLUGIN_URL', plugins_url( '', __FILE__ ) );
} );

/**
 * Requires files from /inc folder.
 */
require plugin_dir_path( __FILE__ ) . '/inc/admin-functions.php';
require plugin_dir_path( __FILE__ ) . '/inc/front-end-functions.php';
require plugin_dir_path( __FILE__ ) . '/inc/front-end-recipe-actions.php';
require plugin_dir_path( __FILE__ ) . '/inc/meta-boxes.php';
require plugin_dir_path( __FILE__ ) . '/inc/misc.php';
require plugin_dir_path( __FILE__ ) . '/inc/permalinks.php';
require plugin_dir_path( __FILE__ ) . '/inc/post-types.php';
require plugin_dir_path( __FILE__ ) . '/inc/shortcodes.php';
require plugin_dir_path( __FILE__ ) . '/inc/structured-data.php';
require plugin_dir_path( __FILE__ ) . '/inc/transients.php';
require plugin_dir_path( __FILE__ ) . '/inc/voting.php';
require plugin_dir_path( __FILE__ ) . '/inc/forms/form-fields.php';
require plugin_dir_path( __FILE__ ) . '/inc/forms/form-processing.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-widget-custom-recipes.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-hero-image.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-list-blog-posts.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-list-recipes.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-list-related-recipes.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-list-terms.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-list-users.php';
require plugin_dir_path( __FILE__ ) . '/inc/widgets/class-rcps-widget-recipe-search.php';

/**
 * Registers a plugin function to be run when the plugin is activated.
 */
register_activation_hook( __FILE__, 'recipes_plugin_activate' );
