<?php
/**
 * Class-rcps-widget-list-terms.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Rcps_Widget_List_Terms extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'rcps-widget-list-terms',
			'description'                 => esc_html__( 'Widget to display terms.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Rcps_Widget_List_Terms', __( 'Recipes: Homepage > List Terms', 'recipes' ), $widget_ops );

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
		$taxonomies       = ( ! empty( $instance['taxonomies'] ) ) ? $instance['taxonomies'] : get_object_taxonomies( 'recipe', 'names' );
		$number           = ( ! empty( $instance['number'] ) ) ? $instance['number'] : 4;
		$orderby          = ( ! empty( $instance['orderby'] ) ) ? $instance['orderby'] : 'count';
		$order            = ( ! empty( $instance['order'] ) ) ? $instance['order'] : 'DESC';
		$only_featured    = ( ! empty( $instance['only_featured'] ) ) ? $instance['only_featured'] : 'off';
		$background_color = ( ! empty( $instance['background_color'] ) ) ? $instance['background_color'] : '';

		$title = apply_filters( 'widget_title', $title );

		echo wp_kses_post( $args['before_widget'] );

		$transient_key = 'rcps_widget_' . $args['widget_id'];

		$ids = get_transient( $transient_key );

		if ( false === $ids ) {

			$term_ids_args = [
				'taxonomy'   => $taxonomies,
				'number'     => $number,
				'hide_empty' => true,
				'orderby'    => $orderby,
				'order'      => $order,
				'fields'     => 'ids',
			];

			if ( 'on' === $only_featured ) {
				$term_ids_args['meta_key']   = '_rcps_meta_term_is_featured';
				$term_ids_args['meta_value'] = 'on';
			}

			$term_ids = new WP_Term_Query( $term_ids_args );

			$ids = $term_ids->terms;
			set_transient( $transient_key, $ids, HOUR_IN_SECONDS );
		}

		$terms_args = array(
			'include' => $ids,
			'number'  => $number,
			'orderby' => 'include',
		);

		$terms = new WP_Term_Query( $terms_args );

		if ( empty( $terms ) ) {
			return;
		}
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

					<?php foreach ( $terms->get_terms() as $term ) : ?>
						<?php
						$get_taxonomy  = get_taxonomy( $term->taxonomy );
						$term_image_id = mytheme_get_term_image_id( $term );
						?>
						<div class="rcps-item-wrap rcps-item-wrap-big rcps-item-wrap-collection">
							<article class="rcps-item rcps-item-big">
								<div class="rcps-item-featured-img">
									<a href="<?php echo esc_url( get_term_link( $term ) ); ?>">
										<?php
										if ( false === $term_image_id ) {
											$image = mytheme_get_term_fallback_image();
										} else {
											$image = wp_get_attachment_image(
												$term_image_id,
												'img-560',
												false,
												array(
													'class' => 'lazyload',
													'alt' => $get_taxonomy->labels->singular_name . ': ' . $term->name,
												)
											);
											$image = str_replace( 'width="560"', 'width="280"', $image );
											$image = str_replace( 'height="400"', 'height="200"', $image );
										}

										// NOTE: wp_kses_post removes 'sizes' attribute, so use wp_kses with a list of allowed attributes.
										echo wp_kses( $image, array(
											'img' => array(
												'data-src'    => true,
												'src'         => true,
												'srcset'      => true,
												'data-srcset' => true,
												'sizes'       => true,
												'class'       => true,
												'id'          => true,
												'width'       => true,
												'height'      => true,
												'alt'         => true,
												'align'       => true,
												'loading'     => true,
											),
										) );
										?>
									</a>

									<div class="rcps-item-top">
										<div class="rcps-item-top-left">
											<svg class="rcps-icon rcps-icon-white"><use xlink:href="<?php echo esc_url( get_template_directory_uri() ); ?>/images/icons.svg#icon-collection"/></svg>
											<?php // Translators: %d is the number of recipes. ?>
											<?php printf( esc_html( _n( '%d recipe', '%d recipes', $term->count, 'recipes' ) ), absint( $term->count ) ); ?>
										</div>
									</div><!-- .rcps-item-top -->
								</div>

								<div class="rcps-item-tax"><span><?php echo esc_html( $get_taxonomy->labels->singular_name ); ?></span></div>

								<h3 class="rcps-item-title"><a href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a></h3>
							</article>
						</div><!-- .rcps-item-recipe-wrap -->
					<?php endforeach; ?>

				</div><!-- .rcps-recipe-grid -->
			</div><!-- .rcps-inner -->
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
		$instance['taxonomies']       = ( ! empty( $new_instance['taxonomies'] ) ) ? $new_instance['taxonomies'] : '';
		$instance['number']           = absint( $new_instance['number'] );
		$instance['orderby']          = esc_attr( $new_instance['orderby'] );
		$instance['order']            = esc_attr( $new_instance['order'] );
		$instance['only_featured']    = ( ! empty( $new_instance['only_featured'] ) ) ? $new_instance['only_featured'] : '';
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

		$defaults = array(
			'title'            => __( 'Featured terms', 'recipes' ),
			'content'          => '',
			'taxonomies'       => $taxonomies,
			'number'           => 4,
			'orderby'          => 'count',
			'order'            => 'DESC',
			'only_featured'    => 'off',
			'background_color' => '',
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
			<label><?php esc_html_e( 'Content:', 'recipes' ); ?></label>
			<textarea name="<?php echo esc_attr( $this->get_field_name( 'content' ) ); ?>" class="widefat"><?php echo esc_html( wp_strip_all_tags( $instance['content'] ) ); ?></textarea>
		</p>

		<p>
			<label><?php esc_html_e( 'Taxonomies', 'recipes' ); ?>:</label>
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
			<label><?php esc_html_e( 'Max. number of terms to show', 'recipes' ); ?>:</label>
			<input name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" size="3" value="<?php echo absint( $instance['number'] ); ?>" class="tiny-text">
		</p>

		<p>
			<label><?php esc_html_e( 'Order by', 'recipes' ); ?>:</label>
			<select name="<?php echo esc_attr( $this->get_field_name( 'orderby' ) ); ?>" class="widefat">
				<option value="name" <?php selected( $instance['orderby'], 'name' ); ?>><?php esc_html_e( 'Name', 'recipes' ); ?></option>
				<option value="count" <?php selected( $instance['orderby'], 'count' ); ?>><?php esc_html_e( 'Count', 'recipes' ); ?></option>
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
			<input class="checkbox" type="checkbox" <?php checked( $instance['only_featured'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'only_featured' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'only_featured' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'only_featured' ) ); ?>"><?php esc_html_e( 'Show only featured terms', 'recipes' ); ?></label>
		</p>

		<p>
			<label><?php esc_html_e( 'Background color:', 'recipes' ); ?></label>
			<input class="rcps-colorpicker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $instance['background_color'] ); ?>">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Rcps_Widget_List_Terms' );
});
