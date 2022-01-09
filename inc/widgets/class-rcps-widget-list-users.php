<?php
/**
 * Class-rcps-widget-list-users.php
 *
 * @package Recipes WordPress Theme
 */

/**
 * Core class used to implement the widget.
 */
class Rcps_Widget_List_Users extends WP_Widget {

	/**
	 * Sets up the widget's name etc.
	 */
	public function __construct() {

		$widget_ops = array(
			'classname'                   => 'rcps-widget-list-users',
			'description'                 => esc_html__( 'Widget to display users.', 'recipes' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'Rcps_Widget_List_Users', __( 'Recipes: Homepage > List Users', 'recipes' ), $widget_ops );

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

		$title                    = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$content                  = ( ! empty( $instance['content'] ) ) ? $instance['content'] : '';
		$number                   = ( ! empty( $instance['number'] ) ) ? $instance['number'] : 4;
		$only_featured_users      = ( ! empty( $instance['only_featured_users'] ) ) ? $instance['only_featured_users'] : 'off';
		$link_to_member_directory = ( ! empty( $instance['link_to_member_directory'] ) ) ? $instance['link_to_member_directory'] : 'off';
		$background_color         = ( ! empty( $instance['background_color'] ) ) ? $instance['background_color'] : '';

		// Get the page ID for the member directory page.
		$member_directory_page_id = rcps_get_page_with_shortcode( 'rcps_member_directory' );

		$title = apply_filters( 'widget_title', $title );

		$transient_key = 'rcps_widget_' . $args['widget_id'];

		$user_ids = get_transient( $transient_key );

		if ( false === $user_ids ) {

			$wp_query_args = array(
				'number'      => $number,
				'fields'      => 'ID',
				'count_total' => false,
				'orderby'     => 'rand',
			);

			if ( 'on' === $only_featured_users ) {
				$wp_query_args['meta_key']   = '_rcps_meta_user_is_featured';
				$wp_query_args['meta_value'] = 'on';
				$wp_query_args['orderby']    = 'display_name';
			}

			$wp_query_user_ids = new WP_User_Query( $wp_query_args );

			$user_ids = $wp_query_user_ids->get_results();
			set_transient( $transient_key, $user_ids, HOUR_IN_SECONDS );
		}

		$wp_query_users = new WP_User_Query( array(
			'include' => $user_ids,
			'orderby' => 'include',
		) );

		$wp_query_users_results = $wp_query_users->get_results();
		?>

		<?php if ( ! empty( $wp_query_users_results ) ) : ?>
			<?php echo wp_kses_post( $args['before_widget'] ); ?>

			<div class="rcps-section-content" <?php echo ( ! empty( $background_color ) ? 'style="background-color:' . esc_attr( $background_color ) . ';"' : '' ); ?>>
				<div class="rcps-inner">
					<?php if ( ! empty( $title ) || ! empty( $content ) || ( 'on' === $link_to_member_directory && ! empty( $member_directory_page_id ) ) ) : ?>
						<div class="rcps-title-header rcps-title-header-sec">
							<?php if ( ! empty( $title ) ) : ?>
								<?php echo wp_kses_post( $args['before_title'] . wp_kses_post( wp_specialchars_decode( $title ) ) . $args['after_title'] ); ?>
							<?php endif; ?>

							<?php if ( 'on' === $link_to_member_directory && ! empty( $member_directory_page_id ) ) : ?>
								<a href="<?php echo esc_url( get_permalink( $member_directory_page_id ) ); ?>" class="rcps-btn rcps-btn-small"><?php esc_html_e( 'View all', 'recipes' ); ?></a>
							<?php endif; ?>

							<?php if ( ! empty( $content ) ) : ?>
								<div class="rcps-widget-content">
									<?php echo wp_kses_post( wp_specialchars_decode( wpautop( $content ) ) ); ?>
								</div><!-- .rcps-widget-content -->
							<?php endif; ?>
						</div><!-- .rcps-title-header -->
					<?php endif; ?>

					<div class="rcps-recipe-grid">
						<?php foreach ( $wp_query_users_results as $user ) : ?>
							<?php global $list_user; ?>
							<?php $list_user = get_userdata( $user->ID ); ?>
							<?php get_template_part( 'templates/template', 'user' ); ?>
						<?php endforeach; ?>
					</div><!-- .rcps-recipe-grid -->
				</div><!-- .rcps-inner -->
			</div><!-- .rcps-section-content -->
			<?php echo wp_kses_post( $args['after_widget'] ); ?>
		<?php endif; ?>
		<?php
		wp_reset_postdata();
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

		$instance['title']                    = esc_html( $new_instance['title'] );
		$instance['content']                  = esc_html( $new_instance['content'] );
		$instance['number']                   = absint( $new_instance['number'] );
		$instance['only_featured_users']      = ( ! empty( $new_instance['only_featured_users'] ) ) ? $new_instance['only_featured_users'] : '';
		$instance['link_to_member_directory'] = ( ! empty( $new_instance['link_to_member_directory'] ) ) ? $new_instance['link_to_member_directory'] : '';
		$instance['background_color']         = $new_instance['background_color'];

		return $instance;
	}

	/**
	 * Outputs the options form on admin.
	 *
	 * @param  array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$defaults = array(
			'title'                    => __( 'Featured members', 'recipes' ),
			'content'                  => '',
			'number'                   => 4,
			'only_featured_users'      => 'off',
			'link_to_member_directory' => 'off',
			'background_color'         => '',
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
			<input class="checkbox" type="checkbox" <?php checked( $instance['only_featured_users'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'only_featured_users' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'only_featured_users' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'only_featured_users' ) ); ?>"><?php esc_html_e( 'Show only featured users', 'recipes' ); ?></label>
		</p>

		<p>
			<input class="checkbox" type="checkbox" <?php checked( $instance['link_to_member_directory'], 'on' ); ?> id="<?php echo esc_attr( $this->get_field_id( 'link_to_member_directory' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link_to_member_directory' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'link_to_member_directory' ) ); ?>"><?php esc_html_e( 'Show a link to the member directory', 'recipes' ); ?></label>
		</p>

		<p>
			<label><?php esc_html_e( 'Number of users to show:', 'recipes' ); ?></label>
			<input name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" size="3" value="<?php echo absint( $instance['number'] ); ?>" class="tiny-text">
		</p>

		<p>
			<label><?php esc_html_e( 'Background color:', 'recipes' ); ?></label>
			<input class="rcps-colorpicker" type="text" id="<?php echo esc_attr( $this->get_field_id( 'background_color' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'background_color' ) ); ?>" value="<?php echo esc_attr( $instance['background_color'] ); ?>">
		</p>

		<?php
	}
}
add_action( 'widgets_init', function() {
	register_widget( 'Rcps_Widget_List_Users' );
});
