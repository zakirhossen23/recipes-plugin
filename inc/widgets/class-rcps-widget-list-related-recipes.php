<?php
/**
 * Class-rcps-widget-list-related-recipes.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Rcps_Widget_List_Related_Recipes extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'rcps-widget-list-related-recipes',
			'description'                 => esc_html__( 'Widget to display related recipes.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Rcps_Widget_List_Related_Recipes', __( 'Recipes: List Related Recipes', 'recipes' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'widgets-scripts', get_template_directory_uri() . '/js/widgets.js', array( 'jquery' ), RCPS_PLUGIN_VERSION, false );
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		// Allow using the widget only on single recipes.
		if ( ! is_singular( 'recipe' ) ) {
			return;
		}

		$title            = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$content          = ( ! empty( $instance['content'] ) ) ? $instance['content'] : '';
		$taxonomies       = ( ! empty( $instance['taxonomies'] ) ) ? $instance['taxonomies'] : get_object_taxonomies( 'recipe', 'names' );
		$number           = ( ! empty( $instance['number'] ) ) ? $instance['number'] : 6;
		$orderby          = ( ! empty( $instance['orderby'] ) ) ? $instance['orderby'] : 'date';
		$order            = ( ! empty( $instance['order'] ) ) ? $instance['order'] : 'DESC';
		$background_color = ( ! empty( $instance['background_color'] ) ) ? $instance['background_color'] : '';

		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );

		global $post;
		$current_post_id = $post->ID;

		$transient_key = 'rcps_widget_related_recipes_to_' . $current_post_id;

		$ids = get_transient( $transient_key );

		if ( false === $ids ) {

			$wp_query_args = array(
				'post_type'      => 'recipe',
				'posts_per_page' => $number,
				'order'          => $order,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'post__not_in'   => array( $current_post_id ),
				'meta_query'     => array(
					array(
						'key' => '_thumbnail_id',
					),
				),
			);

			if ( ! empty( $taxonomies ) ) {
				foreach ( $taxonomies as $taxonomy ) {
					$terms = get_the_terms( $current_post_id, $taxonomy );

					if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
						// Get term IDs.
						$term_ids = wp_list_pluck( $terms, 'term_id' );

						$wp_query_args['tax_query']['relation'] = 'OR';
						$wp_query_args['tax_query'][] = array(
							'taxonomy' => $taxonomy,
							'terms'    => $term_ids,
						);
					}
				}
			}

			if ( 'rating' === $orderby ) {
				$wp_query_args['meta_key'] = 'custom_meta_votes_percent';
				$wp_query_args['orderby']  = 'meta_value_num';
			}

			if ( 'favorites' === $orderby ) {
				$wp_query_args['meta_key'] = 'simplefavorites_count';
				$wp_query_args['orderby']  = 'meta_value_num';
			}

			if ( 'views' === $orderby ) {
				$wp_query_args['meta_key'] = 'views';
				$wp_query_args['orderby']  = 'meta_value_num';
			}

			$wp_query_recipes_ids = new WP_Query( $wp_query_args );

			$ids = $wp_query_recipes_ids->posts;

			set_transient( $transient_key, $ids, DAY_IN_SECONDS );
		}

		$wp_query_recipes = new WP_Query( array(
			'post_type'      => 'recipe',
			'post__in'       => $ids,
			'posts_per_page' => count( $ids ),
			'orderby'        => 'post__in',
		) );

		if ( $wp_query_recipes->have_posts() ) {
			?>

			<div class="rcps-section-content" <?php echo ( ! empty( $background_color ) ? 'style="background-color:' . esc_attr( $background_color ) . ';"' : '' ); ?>>
				<div class="rcps-inner">
					<?php if ( ! empty( $title ) || ! empty( $content ) ) : ?>
						<div class="rcps-title-header rcps-title-header-sec">
							<?php if ( ! empty( $title ) ) : ?>
								<?php echo wp_kses_post( $args['before_title'] . wp_kses_post( wp_specialchars_decode( $title ) ) . $args['after_title'] ); ?>
							<?php endif; ?>

							<?php if ( ! empty( $content ) ) : ?>
								<div class="rcps-widget-content">
									<?php echo wp_kses_post( wp_specialchars_decode( wpautop( $content ) ) ); ?>
								</div><!-- .rcps-widget-content -->
							<?php endif; ?>
						</div><!-- .rcps-title-header -->
					<?php endif; ?>

					<div class="rcps-recipe-grid">
						<?php while ( $wp_query_recipes->have_posts() ) : ?>
							<?php $wp_query_recipes->the_post(); ?>
							<?php get_template_part( 'templates/template', 'recipe' ); ?>
						<?php endwhile; ?>
					</div><!-- .rcps-recipe-grid -->
				</div><!-- .rcps-inner -->
			</div><!-- .rcps-section-content -->
			<?php
		}

		wp_reset_postdata();

		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Processing widget options on save.
	 *
	 * @param  array $new_instance Values just sent to be saved.
	 * @param  array $old_instance Previously saved values from database.
	 * @return array               Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = array();

		$instance['title']            = esc_html( $new_instance['title'] );
		$instance['content']          = esc_html( $new_instance['content'] );
		$instance['taxonomies']       = ( ! empty( $new_instance['taxonomies'] ) ) ? $new_instance['taxonomies'] : '';
		$instance['number']           = absint( $new_instance['number'] );
		$instance['orderby']          = $new_instance['orderby'];
		$instance['order']            = $new_instance['order'];
		$instance['background_color'] = $new_instance['background_color'];

		return $instance;
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @param  array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$taxonomies = get_object_taxonomies( 'recipe', 'names' );

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'            => __( 'Recommended For You', 'recipes' ),
				'content'          => '',
				'taxonomies'       => $taxonomies,
				'number'           => 6,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'background_color' => '',
			)
		);
		?>

		<p>
			<label><?php esc_html_e( 'Title:', 'recipes' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $instance['title'] ); ?>">
		</p>

		<p>
			<label><?php esc_html_e( 'Content', 'recipes' ); ?>:</label>
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>" class="widefat"><?php echo esc_html( wp_strip_all_tags( $instance['content'] ) ); ?></textarea>
		</p>

		<p>
			<label><?php esc_html_e( 'Taxonomies used to find related recipes', 'recipes' ); ?>:</label>
			<?php foreach ( $taxonomies as $taxonomy ) : ?>
				<?php $get_taxonomy = get_taxonomy( $taxonomy ); ?>
				<?php
				$checked = false;
				if ( ! empty( $instance['taxonomies'] ) && in_array( $taxonomy, $instance['taxonomies'], true ) ) {
					$checked = true;
				}
				?>

				<br><input type="checkbox" class="checkbox" id="<?php echo esc_attr( $this->get_field_id( 'taxonomies' ) ) . esc_attr( $taxonomy ); ?>" <?php checked( $checked ); ?> name="<?php echo esc_attr( $this->get_field_name( 'taxonomies' ) ); ?>[]" value="<?php echo esc_attr( $taxonomy ); ?>">

				<label for="<?php echo esc_attr( $this->get_field_id( 'taxonomies' ) ) . esc_attr( $taxonomy ); ?>"><?php echo esc_html( $get_taxonomy->labels->singular_name ); ?></label>
			<?php endforeach; ?>
		</p>

		<p>
			<label><?php esc_html_e( 'Max. number of recipes to show', 'recipes' ); ?>:</label>
			<input name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" size="3" value="<?php echo absint( $instance['number'] ); ?>" class="tiny-text">
		</p>

		<p>
			<label><?php esc_html_e( 'Order by', 'recipes' ); ?>:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" class="widefat">
				<option value="date" <?php selected( $instance['orderby'], 'date' ); ?>><?php esc_html_e( 'Date', 'recipes' ); ?></option>
				<option value="rating" <?php selected( $instance['orderby'], 'rating' ); ?>><?php esc_html_e( 'Rating', 'recipes' ); ?></option>
				<option value="favorites" <?php selected( $instance['orderby'], 'favorites' ); ?>><?php esc_html_e( 'Favorite count', 'recipes' ); ?></option>
				<?php if ( class_exists( 'WP_Widget_PostViews' ) ) : ?>
					<option value="views" <?php selected( $instance['orderby'], 'views' ); ?>><?php esc_html_e( 'Views', 'recipes' ); ?></option>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label><?php esc_html_e( 'Order', 'recipes' ); ?>:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'order' ) ); ?>" class="widefat">
				<option value="ASC" <?php selected( $instance['order'], 'ASC' ); ?>><?php esc_html_e( 'Ascending', 'recipes' ); ?></option>
				<option value="DESC" <?php selected( $instance['order'], 'DESC' ); ?>><?php esc_html_e( 'Descending', 'recipes' ); ?></option>
			</select>
		</p>

		<p>
			<label><?php esc_html_e( 'Background color:', 'recipes' ); ?></label>
			<input class="rcps-colorpicker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $instance['background_color'] ); ?>">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Rcps_Widget_List_Related_Recipes' );
});
