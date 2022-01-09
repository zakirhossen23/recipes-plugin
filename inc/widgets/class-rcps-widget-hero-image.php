<?php
/**
 * Class-rcps-widget-hero-image.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Rcps_Widget_Hero_Image extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'rcps-widget-hero-image',
			'description'                 => esc_html__( 'Widget to display hero image.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Rcps_Widget_Hero_Image', __( 'Recipes: Homepage > Hero Image', 'recipes' ), $widget_ops );

		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
	}

	/**
	 * Enqueue scripts.
	 */
	public function scripts() {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'widgets-scripts', get_template_directory_uri() . '/js/widgets.js', array( 'jquery' ), RCPS_PLUGIN_VERSION, false );
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param  array $args     Widget arguments.
	 * @param  array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		$title              = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$search_title       = ( ! empty( $instance['search_title'] ) ) ? $instance['search_title'] : '';
		$search_suggestions = ( ! empty( $instance['search_suggestions'] ) ) ? $instance['search_suggestions'] : '';
		$image_id           = ( ! empty( $instance['image_id'] ) ) ? $instance['image_id'] : '';
		$background_color   = ( ! empty( $instance['background_color'] ) ) ? $instance['background_color'] : '';

		// Convert comma separated list of search suggestions into array.
		if ( ! empty( $search_suggestions ) ) {
			$search_suggestions = str_replace( ', ', ',', $search_suggestions );
			$keywords           = explode( ',', $search_suggestions );
		}

		$overlay_class = '';
		if ( ! empty( $background_color ) ) {
			$overlay_class = mytheme_get_contrast( $background_color, 'rcps-hero-overlay-dark', 'rcps-hero-overlay-light' );
		}

		echo wp_kses_post( $args['before_widget'] );
		?>

		<div class="rcps-hero">
			<div class="rcps-hero-image">
				<?php if ( ! empty( $image_id ) ) : ?>
					<?php echo wp_get_attachment_image( $image_id, 'img-1140', false, array( 'class' => 'rcps-hero-img lazyload' ) ); ?>
				<?php endif; ?>
			</div><!-- .rcps-hero-image -->

			<?php if ( ! empty( $title ) || ! empty( $keywords ) || ! empty( $search_title ) ) : ?>
				<div class="rcps-hero-content">
					<div class="rcps-hero-content-inner">
						<?php if ( ! empty( $title ) ) : ?>
							<h1 class="rcps-title-hero"><span class="rcps-hero-overlay <?php echo sanitize_html_class( $overlay_class ); ?>" <?php echo ( ! empty( $background_color ) ? 'style="background-color:' . esc_attr( $background_color ) . '"' : '' ); ?>><?php echo wp_kses_post( wp_specialchars_decode( $title ) ); ?></span></h1>
						<?php endif; ?>

						<?php if ( ! empty( $keywords ) || ! empty( $search_title ) ) : ?>
							<span class="rcps-hero-overlay rcps-hero-overlay-sec <?php echo sanitize_html_class( $overlay_class ); ?>" <?php echo ( ! empty( $background_color ) ? 'style="background-color:' . esc_attr( $background_color ) . '"' : '' ); ?>>
								<svg class="rcps-icon rcps-icon-on-hero"><use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/images/icons.svg#icon-search"/></svg>

								<?php if ( ! empty( $search_title ) ) : ?>
									<span><?php echo esc_html( $search_title ); ?></span>
								<?php endif; ?>

								<?php if ( ! empty( $keywords ) ) : ?>
									<?php foreach ( $keywords as $keyword ) : ?>
										<a href="<?php echo esc_url( add_query_arg( 's', $keyword, get_post_type_archive_link( 'recipe' ) ) ); ?>"><?php echo esc_html( $keyword ); ?></a>
									<?php endforeach; ?>
								<?php endif; ?>
							</span>
						<?php endif; ?>
					</div><!-- .rcps-hero-content-inner -->
				</div><!-- .rcps-hero-content -->
			<?php endif; ?>
		</div><!-- .rcps-hero -->

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

		$instance = $old_instance;

		$instance['title']              = esc_html( $new_instance['title'] );
		$instance['search_title']       = sanitize_text_field( $new_instance['search_title'] );
		$instance['search_suggestions'] = sanitize_text_field( $new_instance['search_suggestions'] );
		$instance['image_id']           = absint( $new_instance['image_id'] );
		$instance['background_color']   = $new_instance['background_color'];

		return $instance;
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @param  array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'              => get_bloginfo( 'name' ),
			'search_title'       => __( 'Popular searches', 'recipes' ),
			'search_suggestions' => 'pizza, tomato, easy',
			'image_id'           => '',
			'image'              => '',
			'background_color'   => '',
		);

		$instance = wp_parse_args(
			(array) $instance,
			$defaults
		);
		?>

		<p>
			<label><?php esc_html_e( 'Title:', 'recipes' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_html( $instance['title'] ); ?>">
		</p>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>"><?php esc_html_e( 'Image:', 'recipes' ); ?></label>
			<div>
				<?php if ( intval( $instance['image_id'] ) > 0 ) : ?>
					<?php
					echo wp_get_attachment_image( $instance['image_id'], 'medium', false, array(
						'id'    => $this->get_field_id( 'image' ),
						'class' => 'preview_image',
					) );
					?>
				<?php else : ?>
					<?php echo '<img src="" id="' . esc_attr( $this->get_field_id( 'image' ) ) . '" class="preview_image">'; ?>
				<?php endif; ?>
			</div>
			<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'image_id' ) ); ?>" class="rcps_image_id" value="<?php echo esc_attr( $instance['image_id'] ); ?>">
			<input class="button not-selected upload_image_button" type="button" value="<?php esc_html_e( 'Select Image', 'recipes' ); ?>" data-widget-id="<?php echo esc_attr( $this->get_field_name( 'image_id' ) ); ?>">
		</p>

		<p>
			<label><?php esc_html_e( 'Search title:', 'recipes' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'search_title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['search_title'] ); ?>">
		</p>

		<p>
			<label><?php esc_html_e( 'Search suggestions as a comma separated list. For example: pizza, tomato, easy', 'recipes' ); ?></label>
			<input class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'search_suggestions' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['search_suggestions'] ); ?>">
		</p>

		<p>
			<label><?php esc_html_e( 'Title background color:', 'recipes' ); ?></label>
			<input class="rcps-colorpicker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $instance['background_color'] ); ?>">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Rcps_Widget_Hero_Image' );
});
