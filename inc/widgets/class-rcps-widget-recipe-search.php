<?php
/**
 * Class-rcps-widget-recipe-search.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Rcps_Widget_Recipe_Search extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'rcps-widget-recipe-search',
			'description'                 => esc_html__( 'Widget to display recipe search.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Rcps_Widget_Recipe_Search', __( 'Recipes: Homepage > Recipe Search', 'recipes' ), $widget_ops );

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

		$title            = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$content          = ( ! empty( $instance['content'] ) ) ? $instance['content'] : '';
		$background_color = ( ! empty( $instance['background_color'] ) ) ? $instance['background_color'] : '';

		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );
		?>

		<div class="rcps-section-content" <?php echo ( ! empty( $background_color ) ? 'style="background-color:' . esc_attr( $background_color ) . ';"' : '' ); ?>>
			<?php if ( ! empty( $title ) || ! empty( $content ) ) : ?>
				<div class="rcps-inner">
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
				</div><!-- .rcps-inner -->
			<?php endif; ?>

			<?php get_template_part( 'templates/template', 'filters' ); ?>
		</div><!-- .rcps-section-content -->

		<?php
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
		$instance['background_color'] = $new_instance['background_color'];

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
				'title'            => __( 'Search recipes', 'recipes' ),
				'content'          => '',
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
			<label><?php esc_html_e( 'Background color:', 'recipes' ); ?></label>
			<input class="rcps-colorpicker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $instance['background_color'] ); ?>">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Rcps_Widget_Recipe_Search' );
});
