<?php
/**
 * Class-widget-custom-recipes.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Widget_Custom_Recipes extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'widget-custom-recipes',
			'description'                 => esc_html__( 'Widget to display recent or top rated recipes.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Widget_Custom_Recipes', __( 'Recipes: Posts or recipes with thumbnails', 'recipes' ), $widget_ops );
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$title     = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$sortby    = ( ! empty( $instance['sortby'] ) ) ? $instance['sortby'] : 'date';
		$post_type = ( ! empty( $instance['post_type'] ) ) ? $instance['post_type'] : 'recipe';
		$number    = ( ! empty( $instance['number'] ) ) ? $instance['number'] : 5;

		$transient_key = 'rcps_widget_' . $args['widget_id'];

		$ids = get_transient( $transient_key );

		if ( false === $ids ) {

			$wp_query_args = array(
				'post_type'              => $post_type,
				'posts_per_page'         => $number,
				'order'                  => 'DESC',
				'update_post_term_cache' => false,
				'no_found_rows'          => true,
				'fields'                 => 'ids',
			);

			if ( 'rating' === $sortby ) {
				$wp_query_args['meta_key'] = 'custom_meta_votes_percent';
				$wp_query_args['orderby']  = 'meta_value_num';
			} elseif ( 'favorites' === $sortby ) {
				$wp_query_args['meta_key'] = 'simplefavorites_count';
				$wp_query_args['orderby']  = 'meta_value_num';
			} elseif ( 'views' === $sortby ) {
				$wp_query_args['meta_key'] = 'views';
				$wp_query_args['orderby']  = 'meta_value_num';
			}

			$wp_query_ids = new WP_Query( $wp_query_args );

			$ids = $wp_query_ids->posts;
			set_transient( $transient_key, $ids, HOUR_IN_SECONDS );
		}

		$wp_query = new WP_Query( array(
			'post_type' => $post_type,
			'post__in'  => $ids,
			'orderby'   => 'post__in',
		) );

		echo wp_kses_post( $args['before_widget'] );
		if ( ! empty( $title ) ) {
			echo wp_kses_post( $args['before_title'] . esc_html( apply_filters( 'widget_title', $title ) ) . $args['after_title'] );
		}

		while ( $wp_query->have_posts() ) :
			$wp_query->the_post();
			get_template_part( 'templates/template', 'recipe-small' );
		endwhile;

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

		$instance = $old_instance;

		$instance['title']     = wp_strip_all_tags( $new_instance['title'] );
		$instance['sortby']    = $new_instance['sortby'];
		$instance['post_type'] = $new_instance['post_type'];
		$instance['number']    = absint( $new_instance['number'] );

		return $instance;
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @param  array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'     => __( 'Recent Recipes', 'recipes' ),
				'sortby'    => 'date',
				'post_type' => 'recipe',
				'number'    => 5,
			)
		);
		?>

		<p>
			<label><?php esc_html_e( 'Title:', 'recipes' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>">
		</p>

		<p>
			<label><?php esc_html_e( 'Post type', 'recipes' ); ?>:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'post_type' ) ); ?>" class="widefat">
				<option value="recipe" <?php selected( $instance['post_type'], 'recipe' ); ?>><?php esc_html_e( 'Recipe', 'recipes' ); ?></option>
				<option value="post" <?php selected( $instance['post_type'], 'post' ); ?>><?php esc_html_e( 'Post', 'recipes' ); ?></option>
			</select>
		</p>

		<p>
			<label><?php esc_html_e( 'Sort by', 'recipes' ); ?>:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'sortby' ) ); ?>" class="widefat">
				<option value="date" <?php selected( $instance['sortby'], 'date' ); ?>><?php esc_html_e( 'Date', 'recipes' ); ?></option>
				<option value="rating" <?php selected( $instance['sortby'], 'rating' ); ?>><?php esc_html_e( 'Rating', 'recipes' ); ?></option>
				<option value="favorites" <?php selected( $instance['sortby'], 'favorites' ); ?>><?php esc_html_e( 'Most favorited', 'recipes' ); ?></option>
				<?php if ( class_exists( 'WP_Widget_PostViews' ) ) : ?>
					<option value="views" <?php selected( $instance['sortby'], 'views' ); ?>><?php esc_html_e( 'Views', 'recipes' ); ?></option>
				<?php endif; ?>
			</select>
		</p>

		<p>
			<label><?php esc_html_e( 'Number of posts to show', 'recipes' ); ?>:</label>
			<input name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" size="3" value="<?php echo absint( $instance['number'] ); ?>" class="tiny-text">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Widget_Custom_Recipes' );
});
